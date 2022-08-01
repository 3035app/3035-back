<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20220628080919 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        // $this->addSql('DROP SEQUENCE pia_trackinglog_id_seq CASCADE'); // Lors du déploiement sur staging la migration plante, la séquence n'existe pas
        $this->addSql("UPDATE pia set hiss_name = '' WHERE hiss_name IS NULL");
        $this->addSql("UPDATE pia set hiss_opinion = '' WHERE hiss_opinion IS NULL");
        $this->addSql('ALTER TABLE pia ALTER hiss_name SET NOT NULL');
        $this->addSql('ALTER TABLE pia ALTER hiss_opinion SET NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        // $this->addSql('CREATE SEQUENCE pia_trackinglog_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE pia ALTER hiss_name DROP NOT NULL');
        $this->addSql('ALTER TABLE pia ALTER hiss_opinion DROP NOT NULL');
    }
}