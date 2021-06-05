<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210528201556 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE load_data_energy ADD smart_mod_id INT NOT NULL');
        $this->addSql('ALTER TABLE load_data_energy ADD CONSTRAINT FK_697171EA2CFA4C13 FOREIGN KEY (smart_mod_id) REFERENCES smart_mod (id)');
        $this->addSql('CREATE INDEX IDX_697171EA2CFA4C13 ON load_data_energy (smart_mod_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE load_data_energy DROP FOREIGN KEY FK_697171EA2CFA4C13');
        $this->addSql('DROP INDEX IDX_697171EA2CFA4C13 ON load_data_energy');
        $this->addSql('ALTER TABLE load_data_energy DROP smart_mod_id');
    }
}
