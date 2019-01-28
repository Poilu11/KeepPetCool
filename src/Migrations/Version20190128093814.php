<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190128093814 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE message ADD user_from_id INT DEFAULT NULL, ADD user_to_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F20C3C701 FOREIGN KEY (user_from_id) REFERENCES app_users (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FD2F7B13D FOREIGN KEY (user_to_id) REFERENCES app_users (id)');
        $this->addSql('CREATE INDEX IDX_B6BD307F20C3C701 ON message (user_from_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FD2F7B13D ON message (user_to_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F20C3C701');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FD2F7B13D');
        $this->addSql('DROP INDEX IDX_B6BD307F20C3C701 ON message');
        $this->addSql('DROP INDEX IDX_B6BD307FD2F7B13D ON message');
        $this->addSql('ALTER TABLE message DROP user_from_id, DROP user_to_id');
    }
}
