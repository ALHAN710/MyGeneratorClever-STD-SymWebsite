<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210617180440 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE smart_mod ADD enterprise_id INT NOT NULL');
        //$this->addSql("UPDATE smart_mod SET enterprise_id=1");
        $this->addSql('ALTER TABLE smart_mod ADD CONSTRAINT FK_786B66EEA97D1AC3 FOREIGN KEY (enterprise_id) REFERENCES enterprise (id)');
        $this->addSql('CREATE INDEX IDX_786B66EEA97D1AC3 ON smart_mod (enterprise_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE smart_mod DROP FOREIGN KEY FK_786B66EEA97D1AC3');
        $this->addSql('DROP INDEX IDX_786B66EEA97D1AC3 ON smart_mod');
        $this->addSql('ALTER TABLE smart_mod DROP enterprise_id');
    }
}
