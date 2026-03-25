<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260123215747 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE band_member (id INT AUTO_INCREMENT NOT NULL, is_admin TINYINT(1) NOT NULL, joined_at DATETIME NOT NULL, description LONGTEXT DEFAULT NULL, user_id INT NOT NULL, band_id INT NOT NULL, INDEX IDX_89A1C7A9A76ED395 (user_id), INDEX IDX_89A1C7A949ABEB17 (band_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE band_member ADD CONSTRAINT FK_89A1C7A9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE band_member ADD CONSTRAINT FK_89A1C7A949ABEB17 FOREIGN KEY (band_id) REFERENCES band (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE band_member DROP FOREIGN KEY FK_89A1C7A9A76ED395');
        $this->addSql('ALTER TABLE band_member DROP FOREIGN KEY FK_89A1C7A949ABEB17');
        $this->addSql('DROP TABLE band_member');
    }
}
