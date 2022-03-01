<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20220301092532 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE pia_profile_id_seq CASCADE');
        $this->addSql('ALTER TABLE pia_processing ADD informed_concerned_people TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE pia_processing ADD consent_concerned_people TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE pia_processing ADD access_concerned_people TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE pia_processing ADD delete_concerned_people TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE pia_processing ADD limit_concerned_people TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE pia_processing ADD subcontractors_obligations TEXT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE pia_profile_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE pia_processing DROP informed_concerned_people');
        $this->addSql('ALTER TABLE pia_processing DROP consent_concerned_people');
        $this->addSql('ALTER TABLE pia_processing DROP access_concerned_people');
        $this->addSql('ALTER TABLE pia_processing DROP delete_concerned_people');
        $this->addSql('ALTER TABLE pia_processing DROP limit_concerned_people');
        $this->addSql('ALTER TABLE pia_processing DROP subcontractors_obligations');
    }
}