<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210525102153 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE zone (id INT AUTO_INCREMENT NOT NULL, site_id INT NOT NULL, name VARCHAR(150) NOT NULL, INDEX IDX_A0EBC007F6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE zone_smart_mod (zone_id INT NOT NULL, smart_mod_id INT NOT NULL, INDEX IDX_F6FD359B9F2C3FAB (zone_id), INDEX IDX_F6FD359B2CFA4C13 (smart_mod_id), PRIMARY KEY(zone_id, smart_mod_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE zone ADD CONSTRAINT FK_A0EBC007F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('ALTER TABLE zone_smart_mod ADD CONSTRAINT FK_F6FD359B9F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE zone_smart_mod ADD CONSTRAINT FK_F6FD359B2CFA4C13 FOREIGN KEY (smart_mod_id) REFERENCES smart_mod (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE site ADD latitude DOUBLE PRECISION DEFAULT NULL, ADD longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE smart_mod ADD level_zone INT DEFAULT NULL, ADD nb_phases INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE zone_smart_mod DROP FOREIGN KEY FK_F6FD359B9F2C3FAB');
        $this->addSql('DROP TABLE zone');
        $this->addSql('DROP TABLE zone_smart_mod');
        $this->addSql('ALTER TABLE site DROP latitude, DROP longitude');
        $this->addSql('ALTER TABLE smart_mod DROP level_zone, DROP nb_phases');
    }
}
