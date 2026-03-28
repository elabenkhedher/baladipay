<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325232648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE infraction (id INT AUTO_INCREMENT NOT NULL, type_infraction VARCHAR(255) NOT NULL, montant_amende DOUBLE PRECISION NOT NULL, lieu VARCHAR(255) NOT NULL, plaque_immat VARCHAR(20) NOT NULL, date_infraction DATETIME NOT NULL, statut VARCHAR(50) NOT NULL, notes LONGTEXT DEFAULT NULL, user_id INT NOT NULL, agent_id INT NOT NULL, INDEX IDX_C1A458F5A76ED395 (user_id), INDEX IDX_C1A458F53414710B (agent_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE paiement (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(255) NOT NULL, date_paiement DATETIME NOT NULL, montant DOUBLE PRECISION NOT NULL, statut VARCHAR(50) NOT NULL, user_id INT NOT NULL, taxe_id INT DEFAULT NULL, infraction_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_B1DC7A1EAEA34913 (reference), INDEX IDX_B1DC7A1EA76ED395 (user_id), INDEX IDX_B1DC7A1E1AB947A4 (taxe_id), INDEX IDX_B1DC7A1E7697C467 (infraction_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE reclamation (id INT AUTO_INCREMENT NOT NULL, sujet VARCHAR(200) NOT NULL, description LONGTEXT NOT NULL, date_soumission DATETIME NOT NULL, statut VARCHAR(50) NOT NULL, user_id INT NOT NULL, INDEX IDX_CE606404A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE taxe (id INT AUTO_INCREMENT NOT NULL, nom_taxe VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, montant DOUBLE PRECISION NOT NULL, actif TINYINT DEFAULT 1 NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, cin VARCHAR(8) NOT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649ABE530DA (cin), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE infraction ADD CONSTRAINT FK_C1A458F5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE infraction ADD CONSTRAINT FK_C1A458F53414710B FOREIGN KEY (agent_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1E1AB947A4 FOREIGN KEY (taxe_id) REFERENCES taxe (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1E7697C467 FOREIGN KEY (infraction_id) REFERENCES infraction (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE infraction DROP FOREIGN KEY FK_C1A458F5A76ED395');
        $this->addSql('ALTER TABLE infraction DROP FOREIGN KEY FK_C1A458F53414710B');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1EA76ED395');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1E1AB947A4');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1E7697C467');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404A76ED395');
        $this->addSql('DROP TABLE infraction');
        $this->addSql('DROP TABLE paiement');
        $this->addSql('DROP TABLE reclamation');
        $this->addSql('DROP TABLE taxe');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
