<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210608015831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE alarm (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(20) NOT NULL, label VARCHAR(150) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE alarm_reporting (id INT AUTO_INCREMENT NOT NULL, alarm_id INT NOT NULL, smart_mod_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_1900779325830571 (alarm_id), INDEX IDX_190077932CFA4C13 (smart_mod_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE alarm_reporting ADD CONSTRAINT FK_1900779325830571 FOREIGN KEY (alarm_id) REFERENCES alarm (id)');
        $this->addSql('ALTER TABLE alarm_reporting ADD CONSTRAINT FK_190077932CFA4C13 FOREIGN KEY (smart_mod_id) REFERENCES smart_mod (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE alarm_reporting DROP FOREIGN KEY FK_1900779325830571');
        $this->addSql('DROP TABLE alarm');
        $this->addSql('DROP TABLE alarm_reporting');
    }
}
