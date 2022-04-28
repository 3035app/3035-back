<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20220426143538 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
    }

    /**
     * @param Schema $schema
     */
    public function postUp(Schema $schema) : void
    {
        # get all processings
        $processings = $this->connection->executeQuery('SELECT id, informed_concerned_people FROM pia_processing')->fetchAll();
        $total = count($processings);
        $this->write("<info>number of processings: {$total}.</info>");
        foreach ($processings as $processing) {
            $unserialized = unserialize($processing['informed_concerned_people']);
            if (null != $unserialized) {
                foreach ($unserialized as $reference => $value) {
                    if (true === (bool) $value) {
                        $this->addSql(
                            sprintf(
                                'INSERT INTO pia_processing_data_type (id, processing_id, reference, sensitive)
                                 VALUES (nextval(\'pia_processing_data_type_id_seq\'), %d, \'%s\', \'%b\'
                                )',
                                $processing['id'],
                                $reference,
                                false
                            )
                        );
                    }
                }
            }
        }
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
    }
}