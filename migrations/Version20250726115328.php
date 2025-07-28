<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250726115328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE participant (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, prenom VARCHAR(70) NOT NULL, courrier_professionnel VARCHAR(150) NOT NULL, pays VARCHAR(20) NOT NULL, ville VARCHAR(20) NOT NULL, cin VARCHAR(255) NOT NULL, profession VARCHAR(255) NOT NULL, niveau_etude VARCHAR(100) DEFAULT NULL, etablissement VARCHAR(50) DEFAULT NULL, carte_etudiant_attestation_travail VARCHAR(255) DEFAULT NULL, specialite VARCHAR(255) DEFAULT NULL, statu VARCHAR(255) DEFAULT NULL, fonction VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE participant');
    }
}
