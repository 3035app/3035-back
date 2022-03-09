<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20220309081816 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE pia_users__processings (processing_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(processing_id, user_id))');
        $this->addSql('CREATE INDEX IDX_1060238C5BAE24E8 ON pia_users__processings (processing_id)');
        $this->addSql('CREATE INDEX IDX_1060238CA76ED395 ON pia_users__processings (user_id)');
        $this->addSql('ALTER TABLE pia_users__processings ADD CONSTRAINT FK_1060238C5BAE24E8 FOREIGN KEY (processing_id) REFERENCES pia_processing (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pia_users__processings ADD CONSTRAINT FK_1060238CA76ED395 FOREIGN KEY (user_id) REFERENCES pia_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE pia_users__processings');
    }
}