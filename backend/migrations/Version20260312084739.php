<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260312084739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE group_invitation (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, responded_at DATETIME DEFAULT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, INDEX IDX_26D00010F624B39D (sender_id), INDEX IDX_26D00010CD53EDB6 (receiver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE group_invitation ADD CONSTRAINT FK_26D00010F624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE group_invitation ADD CONSTRAINT FK_26D00010CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE group_invitation DROP FOREIGN KEY FK_26D00010F624B39D');
        $this->addSql('ALTER TABLE group_invitation DROP FOREIGN KEY FK_26D00010CD53EDB6');
        $this->addSql('DROP TABLE group_invitation');
    }
}
