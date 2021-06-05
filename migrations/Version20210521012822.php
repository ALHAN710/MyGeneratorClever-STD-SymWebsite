<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210521012822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE data_mod');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE data_mod (id INT AUTO_INCREMENT NOT NULL, smart_mod_id INT NOT NULL, INDEX IDX_5378B2FD2CFA4C13 (smart_mod_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE data_mod ADD CONSTRAINT FK_5378B2FD2CFA4C13 FOREIGN KEY (smart_mod_id) REFERENCES smart_mod (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
