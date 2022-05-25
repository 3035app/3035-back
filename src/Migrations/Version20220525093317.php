<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20220525093317 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE pia ADD requested_hiss_opinion BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE pia ADD hiss_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE pia ADD hiss_processing_implemented_status SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE pia ADD hiss_opinion TEXT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE pia DROP requested_hiss_opinion');
        $this->addSql('ALTER TABLE pia DROP hiss_name');
        $this->addSql('ALTER TABLE pia DROP hiss_processing_implemented_status');
        $this->addSql('ALTER TABLE pia DROP hiss_opinion');
    }
}