<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260123213528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_style (id INT AUTO_INCREMENT NOT NULL, is_principal TINYINT(1) NOT NULL, user_id INT NOT NULL, style_id INT NOT NULL, INDEX IDX_D17F4332A76ED395 (user_id), INDEX IDX_D17F4332BACD6074 (style_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user_style ADD CONSTRAINT FK_D17F4332A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_style ADD CONSTRAINT FK_D17F4332BACD6074 FOREIGN KEY (style_id) REFERENCES style (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_style DROP FOREIGN KEY FK_D17F4332A76ED395');
        $this->addSql('ALTER TABLE user_style DROP FOREIGN KEY FK_D17F4332BACD6074');
        $this->addSql('DROP TABLE user_style');
    }
}
