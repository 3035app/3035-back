<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20220628085920 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql("UPDATE pia set hiss_processing_implemented_status = 0 WHERE hiss_processing_implemented_status IS NULL");
        $this->addSql('ALTER TABLE pia ALTER hiss_processing_implemented_status SET NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE pia ALTER hiss_processing_implemented_status DROP NOT NULL');
    }
}