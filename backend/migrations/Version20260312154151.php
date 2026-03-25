<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260312154151 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE band ADD description LONGTEXT DEFAULT NULL, ADD style_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE band ADD CONSTRAINT FK_48DFA2EBBACD6074 FOREIGN KEY (style_id) REFERENCES style (id)');
        $this->addSql('CREATE INDEX IDX_48DFA2EBBACD6074 ON band (style_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE band DROP FOREIGN KEY FK_48DFA2EBBACD6074');
        $this->addSql('DROP INDEX IDX_48DFA2EBBACD6074 ON band');
        $this->addSql('ALTER TABLE band DROP description, DROP style_id');
    }
}
