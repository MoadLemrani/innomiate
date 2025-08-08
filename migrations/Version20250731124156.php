<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250731124156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE competition (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, min_team_size INT NOT NULL, max_team_size INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, status VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invitation (id INT AUTO_INCREMENT NOT NULL, sender_participant_id INT NOT NULL, receiver_participant_id INT NOT NULL, team_id INT NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_F11D61A24CF0CDDA (sender_participant_id), INDEX IDX_F11D61A2B76A84E0 (receiver_participant_id), INDEX IDX_F11D61A2296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participant (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, competition_id INT NOT NULL, team_id INT DEFAULT NULL, is_team_leader TINYINT(1) NOT NULL, joined_team_date DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, participant_code VARCHAR(255) NOT NULL, nom VARCHAR(50) NOT NULL, prenom VARCHAR(70) NOT NULL, courrier_professionnel VARCHAR(150) NOT NULL, pays VARCHAR(20) NOT NULL, ville VARCHAR(20) NOT NULL, cin VARCHAR(255) NOT NULL, profession VARCHAR(255) NOT NULL, niveau_etude VARCHAR(100) DEFAULT NULL, etablissement VARCHAR(50) DEFAULT NULL, carte_attestation VARCHAR(255) DEFAULT NULL, specialite VARCHAR(255) DEFAULT NULL, statut VARCHAR(255) DEFAULT NULL, fonction VARCHAR(255) DEFAULT NULL, partage VARCHAR(10) NOT NULL, INDEX IDX_D79F6B11A76ED395 (user_id), INDEX IDX_D79F6B117B39D312 (competition_id), INDEX IDX_D79F6B11296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pitch (id INT AUTO_INCREMENT NOT NULL, participant_id INT NOT NULL, competition_id INT NOT NULL, content LONGTEXT NOT NULL, contact_info LONGTEXT NOT NULL, INDEX IDX_279FBED99D1C3019 (participant_id), INDEX IDX_279FBED97B39D312 (competition_id), UNIQUE INDEX UNIQ_PARTICIPANT_COMPETITION (participant_id, competition_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, competition_id INT NOT NULL, leader_participant_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_C4E0A61F7B39D312 (competition_id), INDEX IDX_C4E0A61F89EC6D6 (leader_participant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A24CF0CDDA FOREIGN KEY (sender_participant_id) REFERENCES participant (id)');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2B76A84E0 FOREIGN KEY (receiver_participant_id) REFERENCES participant (id)');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B117B39D312 FOREIGN KEY (competition_id) REFERENCES competition (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE pitch ADD CONSTRAINT FK_279FBED99D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id)');
        $this->addSql('ALTER TABLE pitch ADD CONSTRAINT FK_279FBED97B39D312 FOREIGN KEY (competition_id) REFERENCES competition (id)');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F7B39D312 FOREIGN KEY (competition_id) REFERENCES competition (id)');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F89EC6D6 FOREIGN KEY (leader_participant_id) REFERENCES participant (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A24CF0CDDA');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2B76A84E0');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2296CD8AE');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11A76ED395');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B117B39D312');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11296CD8AE');
        $this->addSql('ALTER TABLE pitch DROP FOREIGN KEY FK_279FBED99D1C3019');
        $this->addSql('ALTER TABLE pitch DROP FOREIGN KEY FK_279FBED97B39D312');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F7B39D312');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F89EC6D6');
        $this->addSql('DROP TABLE competition');
        $this->addSql('DROP TABLE invitation');
        $this->addSql('DROP TABLE participant');
        $this->addSql('DROP TABLE pitch');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
