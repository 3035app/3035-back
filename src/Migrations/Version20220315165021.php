<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20220315165021 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE pia_processing ADD s_author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pia_processing ADD s_designated_controller_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pia_processing ADD s_data_protection_officer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pia_processing ADD s_data_controller_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pia_processing ADD CONSTRAINT FK_81E5D0EC5424BF6E FOREIGN KEY (s_author_id) REFERENCES pia_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pia_processing ADD CONSTRAINT FK_81E5D0ECB620DF89 FOREIGN KEY (s_designated_controller_id) REFERENCES pia_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pia_processing ADD CONSTRAINT FK_81E5D0EC71DC80E4 FOREIGN KEY (s_data_protection_officer_id) REFERENCES pia_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pia_processing ADD CONSTRAINT FK_81E5D0EC12E3B0BF FOREIGN KEY (s_data_controller_id) REFERENCES pia_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_81E5D0EC5424BF6E ON pia_processing (s_author_id)');
        $this->addSql('CREATE INDEX IDX_81E5D0ECB620DF89 ON pia_processing (s_designated_controller_id)');
        $this->addSql('CREATE INDEX IDX_81E5D0EC71DC80E4 ON pia_processing (s_data_protection_officer_id)');
        $this->addSql('CREATE INDEX IDX_81E5D0EC12E3B0BF ON pia_processing (s_data_controller_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE pia_processing DROP CONSTRAINT FK_81E5D0EC5424BF6E');
        $this->addSql('ALTER TABLE pia_processing DROP CONSTRAINT FK_81E5D0ECB620DF89');
        $this->addSql('ALTER TABLE pia_processing DROP CONSTRAINT FK_81E5D0EC71DC80E4');
        $this->addSql('ALTER TABLE pia_processing DROP CONSTRAINT FK_81E5D0EC12E3B0BF');
        $this->addSql('DROP INDEX IDX_81E5D0EC5424BF6E');
        $this->addSql('DROP INDEX IDX_81E5D0ECB620DF89');
        $this->addSql('DROP INDEX IDX_81E5D0EC71DC80E4');
        $this->addSql('DROP INDEX IDX_81E5D0EC12E3B0BF');
        $this->addSql('ALTER TABLE pia_processing DROP s_author_id');
        $this->addSql('ALTER TABLE pia_processing DROP s_designated_controller_id');
        $this->addSql('ALTER TABLE pia_processing DROP s_data_protection_officer_id');
        $this->addSql('ALTER TABLE pia_processing DROP s_data_controller_id');
    }
}