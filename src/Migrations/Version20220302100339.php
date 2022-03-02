<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20220302100339 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE pia_processing ALTER informed_concerned_people TYPE TEXT');
        $this->addSql('ALTER TABLE pia_processing ALTER informed_concerned_people DROP DEFAULT');
        $this->addSql('ALTER TABLE pia_processing ALTER consent_concerned_people TYPE TEXT');
        $this->addSql('ALTER TABLE pia_processing ALTER consent_concerned_people DROP DEFAULT');
        $this->addSql('ALTER TABLE pia_processing ALTER access_concerned_people TYPE TEXT');
        $this->addSql('ALTER TABLE pia_processing ALTER access_concerned_people DROP DEFAULT');
        $this->addSql('ALTER TABLE pia_processing ALTER delete_concerned_people TYPE TEXT');
        $this->addSql('ALTER TABLE pia_processing ALTER delete_concerned_people DROP DEFAULT');
        $this->addSql('ALTER TABLE pia_processing ALTER limit_concerned_people TYPE TEXT');
        $this->addSql('ALTER TABLE pia_processing ALTER limit_concerned_people DROP DEFAULT');
        $this->addSql('ALTER TABLE pia_processing ALTER subcontractors_obligations TYPE TEXT');
        $this->addSql('ALTER TABLE pia_processing ALTER subcontractors_obligations DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN pia_processing.informed_concerned_people IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN pia_processing.consent_concerned_people IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN pia_processing.access_concerned_people IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN pia_processing.delete_concerned_people IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN pia_processing.limit_concerned_people IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN pia_processing.subcontractors_obligations IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE pia_processing ALTER informed_concerned_people TYPE TEXT');
        $this->addSql('ALTER TABLE pia_processing ALTER informed_concerned_people DROP DEFAULT');
        $this->addSql('ALTER TABLE pia_processing ALTER consent_concerned_people TYPE TEXT');
        $this->addSql('ALTER TABLE pia_processing ALTER consent_concerned_people DROP DEFAULT');
        $this->addSql('ALTER TABLE pia_processing ALTER access_concerned_people TYPE TEXT');
        $this->addSql('ALTER TABLE pia_processing ALTER access_concerned_people DROP DEFAULT');
        $this->addSql('ALTER TABLE pia_processing ALTER delete_concerned_people TYPE TEXT');
        $this->addSql('ALTER TABLE pia_processing ALTER delete_concerned_people DROP DEFAULT');
        $this->addSql('ALTER TABLE pia_processing ALTER limit_concerned_people TYPE TEXT');
        $this->addSql('ALTER TABLE pia_processing ALTER limit_concerned_people DROP DEFAULT');
        $this->addSql('ALTER TABLE pia_processing ALTER subcontractors_obligations TYPE TEXT');
        $this->addSql('ALTER TABLE pia_processing ALTER subcontractors_obligations DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN pia_processing.informed_concerned_people IS NULL');
        $this->addSql('COMMENT ON COLUMN pia_processing.consent_concerned_people IS NULL');
        $this->addSql('COMMENT ON COLUMN pia_processing.access_concerned_people IS NULL');
        $this->addSql('COMMENT ON COLUMN pia_processing.delete_concerned_people IS NULL');
        $this->addSql('COMMENT ON COLUMN pia_processing.limit_concerned_people IS NULL');
        $this->addSql('COMMENT ON COLUMN pia_processing.subcontractors_obligations IS NULL');
    }
}