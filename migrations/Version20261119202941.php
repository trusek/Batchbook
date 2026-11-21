<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261119202941 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tabela wpisów (entry)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, occurred_at DATETIME NOT NULL, note CLOB NOT NULL, created_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, batch_id INTEGER NOT NULL, CONSTRAINT FK_2B219D70F39EBE7A FOREIGN KEY (batch_id) REFERENCES batch (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_2B219D70F39EBE7A ON entry (batch_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE entry');
    }
}
