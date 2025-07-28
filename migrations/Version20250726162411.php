<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250726162411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participant ADD partage VARCHAR(10) NOT NULL, CHANGE courrierProfessionnel courrier_professionnel VARCHAR(150) NOT NULL, CHANGE NiveauEtude niveau_etude VARCHAR(100) DEFAULT NULL, CHANGE CarteAttestation carte_attestation VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participant DROP partage, CHANGE courrier_professionnel courrierProfessionnel VARCHAR(150) NOT NULL, CHANGE niveau_etude NiveauEtude VARCHAR(100) DEFAULT NULL, CHANGE carte_attestation CarteAttestation VARCHAR(255) DEFAULT NULL');
    }
}
