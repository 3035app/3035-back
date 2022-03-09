<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20220309080236 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE pia_users__folders (folder_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(folder_id, user_id))');
        $this->addSql('CREATE INDEX IDX_D19DD300162CB942 ON pia_users__folders (folder_id)');
        $this->addSql('CREATE INDEX IDX_D19DD300A76ED395 ON pia_users__folders (user_id)');
        $this->addSql('ALTER TABLE pia_users__folders ADD CONSTRAINT FK_D19DD300162CB942 FOREIGN KEY (folder_id) REFERENCES pia_folder (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pia_users__folders ADD CONSTRAINT FK_D19DD300A76ED395 FOREIGN KEY (user_id) REFERENCES pia_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE pia_users__folders');
    }
}