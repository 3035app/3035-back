<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20220318154046 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE pia ADD evaluator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pia ADD data_protection_officer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pia ALTER author_name DROP NOT NULL');
        $this->addSql('ALTER TABLE pia ALTER evaluator_name DROP NOT NULL');
        $this->addSql('ALTER TABLE pia ALTER validator_name DROP NOT NULL');
        $this->addSql('ALTER TABLE pia ADD CONSTRAINT FK_253A306243575BE2 FOREIGN KEY (evaluator_id) REFERENCES pia_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pia ADD CONSTRAINT FK_253A3062F081EB64 FOREIGN KEY (data_protection_officer_id) REFERENCES pia_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_253A306243575BE2 ON pia (evaluator_id)');
        $this->addSql('CREATE INDEX IDX_253A3062F081EB64 ON pia (data_protection_officer_id)');
        $this->addSql('ALTER TABLE pia_processing ADD redactor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pia_processing ADD data_controller_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pia_processing ADD evaluator_pending_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pia_processing ADD data_protection_officer_pending_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pia_processing ALTER author DROP NOT NULL');
        $this->addSql('ALTER TABLE pia_processing ALTER designated_controller DROP NOT NULL');
        $this->addSql('ALTER TABLE pia_processing ADD CONSTRAINT FK_81E5D0EC8E706861 FOREIGN KEY (redactor_id) REFERENCES pia_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pia_processing ADD CONSTRAINT FK_81E5D0EC793B361 FOREIGN KEY (data_controller_id) REFERENCES pia_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pia_processing ADD CONSTRAINT FK_81E5D0ECCB8D7AD6 FOREIGN KEY (evaluator_pending_id) REFERENCES pia_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pia_processing ADD CONSTRAINT FK_81E5D0ECEDAC34DD FOREIGN KEY (data_protection_officer_pending_id) REFERENCES pia_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_81E5D0EC8E706861 ON pia_processing (redactor_id)');
        $this->addSql('CREATE INDEX IDX_81E5D0EC793B361 ON pia_processing (data_controller_id)');
        $this->addSql('CREATE INDEX IDX_81E5D0ECCB8D7AD6 ON pia_processing (evaluator_pending_id)');
        $this->addSql('CREATE INDEX IDX_81E5D0ECEDAC34DD ON pia_processing (data_protection_officer_pending_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE pia DROP CONSTRAINT FK_253A306243575BE2');
        $this->addSql('ALTER TABLE pia DROP CONSTRAINT FK_253A3062F081EB64');
        $this->addSql('DROP INDEX IDX_253A306243575BE2');
        $this->addSql('DROP INDEX IDX_253A3062F081EB64');
        $this->addSql('ALTER TABLE pia DROP evaluator_id');
        $this->addSql('ALTER TABLE pia DROP data_protection_officer_id');
        $this->addSql('ALTER TABLE pia ALTER author_name SET NOT NULL');
        $this->addSql('ALTER TABLE pia ALTER evaluator_name SET NOT NULL');
        $this->addSql('ALTER TABLE pia ALTER validator_name SET NOT NULL');
        $this->addSql('ALTER TABLE pia_processing DROP CONSTRAINT FK_81E5D0EC8E706861');
        $this->addSql('ALTER TABLE pia_processing DROP CONSTRAINT FK_81E5D0EC793B361');
        $this->addSql('ALTER TABLE pia_processing DROP CONSTRAINT FK_81E5D0ECCB8D7AD6');
        $this->addSql('ALTER TABLE pia_processing DROP CONSTRAINT FK_81E5D0ECEDAC34DD');
        $this->addSql('DROP INDEX IDX_81E5D0EC8E706861');
        $this->addSql('DROP INDEX IDX_81E5D0EC793B361');
        $this->addSql('DROP INDEX IDX_81E5D0ECCB8D7AD6');
        $this->addSql('DROP INDEX IDX_81E5D0ECEDAC34DD');
        $this->addSql('ALTER TABLE pia_processing DROP redactor_id');
        $this->addSql('ALTER TABLE pia_processing DROP data_controller_id');
        $this->addSql('ALTER TABLE pia_processing DROP evaluator_pending_id');
        $this->addSql('ALTER TABLE pia_processing DROP data_protection_officer_pending_id');
        $this->addSql('ALTER TABLE pia_processing ALTER author SET NOT NULL');
        $this->addSql('ALTER TABLE pia_processing ALTER designated_controller SET NOT NULL');
    }
}