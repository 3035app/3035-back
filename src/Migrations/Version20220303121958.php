<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20220303121958 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE users_folders (user_profile_id INT NOT NULL, folder_id INT NOT NULL, PRIMARY KEY(user_profile_id, folder_id))');
        $this->addSql('CREATE INDEX IDX_E37A7C56B9DD454 ON users_folders (user_profile_id)');
        $this->addSql('CREATE INDEX IDX_E37A7C5162CB942 ON users_folders (folder_id)');
        $this->addSql('ALTER TABLE users_folders ADD CONSTRAINT FK_E37A7C56B9DD454 FOREIGN KEY (user_profile_id) REFERENCES user_profile (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users_folders ADD CONSTRAINT FK_E37A7C5162CB942 FOREIGN KEY (folder_id) REFERENCES pia_folder (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE users_folders');
    }
}