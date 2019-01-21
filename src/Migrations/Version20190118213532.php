<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190118213532 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(32) NOT NULL, name VARCHAR(32) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE presentation (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, title VARCHAR(128) NOT NULL, body LONGTEXT NOT NULL, is_active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_9B66E893A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE presentation_species (presentation_id INT NOT NULL, species_id INT NOT NULL, INDEX IDX_387CB74BAB627E8B (presentation_id), INDEX IDX_387CB74BB2A1D860 (species_id), PRIMARY KEY(presentation_id, species_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE presentation_service (presentation_id INT NOT NULL, service_id INT NOT NULL, INDEX IDX_7CEEDA8BAB627E8B (presentation_id), INDEX IDX_7CEEDA8BED5CA9E6 (service_id), PRIMARY KEY(presentation_id, service_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE species (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(64) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, petsitter_id INT DEFAULT NULL, body LONGTEXT NOT NULL, note SMALLINT NOT NULL, is_active TINYINT(1) NOT NULL, is_validated TINYINT(1) NOT NULL, INDEX IDX_9474526C7E3C61F9 (owner_id), INDEX IDX_9474526C6442B64 (petsitter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_users (id INT AUTO_INCREMENT NOT NULL, role_id INT DEFAULT NULL, username VARCHAR(64) NOT NULL, password VARCHAR(64) NOT NULL, email VARCHAR(254) NOT NULL, is_active TINYINT(1) NOT NULL, firstname VARCHAR(64) NOT NULL, lastname VARCHAR(64) NOT NULL, address VARCHAR(256) NOT NULL, zip_code VARCHAR(16) NOT NULL, city VARCHAR(64) NOT NULL, longitude DOUBLE PRECISION NOT NULL, latitude DOUBLE PRECISION NOT NULL, path_avatar VARCHAR(512) DEFAULT NULL, phone_number VARCHAR(16) DEFAULT NULL, cell_number VARCHAR(16) DEFAULT NULL, path_certificat VARCHAR(512) DEFAULT NULL, is_validated TINYINT(1) NOT NULL, slug VARCHAR(256) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, connected_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_C2502824F85E0677 (username), UNIQUE INDEX UNIQ_C2502824E7927C74 (email), INDEX IDX_C2502824D60322AC (role_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(64) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE presentation ADD CONSTRAINT FK_9B66E893A76ED395 FOREIGN KEY (user_id) REFERENCES app_users (id)');
        $this->addSql('ALTER TABLE presentation_species ADD CONSTRAINT FK_387CB74BAB627E8B FOREIGN KEY (presentation_id) REFERENCES presentation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE presentation_species ADD CONSTRAINT FK_387CB74BB2A1D860 FOREIGN KEY (species_id) REFERENCES species (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE presentation_service ADD CONSTRAINT FK_7CEEDA8BAB627E8B FOREIGN KEY (presentation_id) REFERENCES presentation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE presentation_service ADD CONSTRAINT FK_7CEEDA8BED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES app_users (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C6442B64 FOREIGN KEY (petsitter_id) REFERENCES app_users (id)');
        $this->addSql('ALTER TABLE app_users ADD CONSTRAINT FK_C2502824D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_users DROP FOREIGN KEY FK_C2502824D60322AC');
        $this->addSql('ALTER TABLE presentation_species DROP FOREIGN KEY FK_387CB74BAB627E8B');
        $this->addSql('ALTER TABLE presentation_service DROP FOREIGN KEY FK_7CEEDA8BAB627E8B');
        $this->addSql('ALTER TABLE presentation_species DROP FOREIGN KEY FK_387CB74BB2A1D860');
        $this->addSql('ALTER TABLE presentation DROP FOREIGN KEY FK_9B66E893A76ED395');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C7E3C61F9');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C6442B64');
        $this->addSql('ALTER TABLE presentation_service DROP FOREIGN KEY FK_7CEEDA8BED5CA9E6');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE presentation');
        $this->addSql('DROP TABLE presentation_species');
        $this->addSql('DROP TABLE presentation_service');
        $this->addSql('DROP TABLE species');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE app_users');
        $this->addSql('DROP TABLE service');
    }
}
