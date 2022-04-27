<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20220425112021 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE pia_processings_redactors (processing_id INT NOT NULL, redactor_id INT NOT NULL, PRIMARY KEY(processing_id, redactor_id))');
        $this->addSql('CREATE INDEX IDX_8E18DB2E5BAE24E8 ON pia_processings_redactors (processing_id)');
        $this->addSql('CREATE INDEX IDX_8E18DB2E8E706861 ON pia_processings_redactors (redactor_id)');
        $this->addSql('ALTER TABLE pia_processings_redactors ADD CONSTRAINT FK_8E18DB2E5BAE24E8 FOREIGN KEY (processing_id) REFERENCES pia_processing (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pia_processings_redactors ADD CONSTRAINT FK_8E18DB2E8E706861 FOREIGN KEY (redactor_id) REFERENCES pia_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function postUp(Schema $schema) : void
    {
        // do not use this, otherwise it crashes on saving histories (-> no user connected)!
        // $this->container->get('doctrine.orm.entity_manager');

        # get all processings
        $processings = $this->connection->executeQuery('SELECT id, redactor_id FROM pia_processing')->fetchAll();
        $total = count($processings);
        $this->write("<info>number of processings: {$total}.</info>");
        foreach ($processings as $processing) {
            if (!empty($processing['redactor_id'])) {
                $this->connection->insert('pia_processings_redactors', [
                    'processing_id' => $processing['id'], 'redactor_id' => $processing['redactor_id']
                ]);
            }
        }
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE pia_processings_redactors');
    }
}