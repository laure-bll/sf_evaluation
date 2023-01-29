<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230129182049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE panier DROP INDEX UNIQ_24CC0DF2FB88E14F, ADD INDEX IDX_24CC0DF2FB88E14F (utilisateur_id)');
        $this->addSql('ALTER TABLE utilisateur ADD panier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3F77D927C FOREIGN KEY (panier_id) REFERENCES panier (id)');
        $this->addSql('CREATE INDEX IDX_1D1C63B3F77D927C ON utilisateur (panier_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE panier DROP INDEX IDX_24CC0DF2FB88E14F, ADD UNIQUE INDEX UNIQ_24CC0DF2FB88E14F (utilisateur_id)');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3F77D927C');
        $this->addSql('DROP INDEX IDX_1D1C63B3F77D927C ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur DROP panier_id');
    }
}
