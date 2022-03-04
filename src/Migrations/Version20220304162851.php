<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20220304162851 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE users_processings (user_id INT NOT NULL, processing_id INT NOT NULL, PRIMARY KEY(user_id, processing_id))');
        $this->addSql('CREATE INDEX IDX_408F6612A76ED395 ON users_processings (user_id)');
        $this->addSql('CREATE INDEX IDX_408F66125BAE24E8 ON users_processings (processing_id)');
        $this->addSql('ALTER TABLE users_processings ADD CONSTRAINT FK_408F6612A76ED395 FOREIGN KEY (user_id) REFERENCES pia_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users_processings ADD CONSTRAINT FK_408F66125BAE24E8 FOREIGN KEY (processing_id) REFERENCES pia_processing (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE users_processings');
    }
}