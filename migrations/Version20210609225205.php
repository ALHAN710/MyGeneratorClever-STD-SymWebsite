<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210609225205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contacts ADD site_id INT NOT NULL');
        //$this->addSql("UPDATE contacts SET site_id=2");
        $this->addSql('ALTER TABLE contacts ADD CONSTRAINT FK_33401573F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('CREATE INDEX IDX_33401573F6BD1646 ON contacts (site_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contacts DROP FOREIGN KEY FK_33401573F6BD1646');
        $this->addSql('DROP INDEX IDX_33401573F6BD1646 ON contacts');
        $this->addSql('ALTER TABLE contacts DROP site_id');
    }
}
