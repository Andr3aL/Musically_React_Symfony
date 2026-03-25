<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260312150258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE band ADD needs_setup TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE group_invitation ADD created_band_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE group_invitation ADD CONSTRAINT FK_26D00010FC1F951 FOREIGN KEY (created_band_id) REFERENCES band (id)');
        $this->addSql('CREATE INDEX IDX_26D00010FC1F951 ON group_invitation (created_band_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE band DROP needs_setup');
        $this->addSql('ALTER TABLE group_invitation DROP FOREIGN KEY FK_26D00010FC1F951');
        $this->addSql('DROP INDEX IDX_26D00010FC1F951 ON group_invitation');
        $this->addSql('ALTER TABLE group_invitation DROP created_band_id');
    }
}
