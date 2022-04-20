<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20220420094537 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE pia_comment ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pia_comment ADD CONSTRAINT FK_7651353AA76ED395 FOREIGN KEY (user_id) REFERENCES pia_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7651353AA76ED395 ON pia_comment (user_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE pia_comment DROP CONSTRAINT FK_7651353AA76ED395');
        $this->addSql('DROP INDEX IDX_7651353AA76ED395');
        $this->addSql('ALTER TABLE pia_comment DROP user_id');
    }
}