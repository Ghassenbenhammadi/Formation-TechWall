<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230719153005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE hobby_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE job_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE profile_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE hobby (id INT NOT NULL, designation VARCHAR(70) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE job (id INT NOT NULL, designation VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE personne_hobby (personne_id INT NOT NULL, hobby_id INT NOT NULL, PRIMARY KEY(personne_id, hobby_id))');
        $this->addSql('CREATE INDEX IDX_2D85E25EA21BD112 ON personne_hobby (personne_id)');
        $this->addSql('CREATE INDEX IDX_2D85E25E322B2123 ON personne_hobby (hobby_id)');
        $this->addSql('CREATE TABLE profile (id INT NOT NULL, url VARCHAR(255) NOT NULL, rs VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE personne_hobby ADD CONSTRAINT FK_2D85E25EA21BD112 FOREIGN KEY (personne_id) REFERENCES personne (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE personne_hobby ADD CONSTRAINT FK_2D85E25E322B2123 FOREIGN KEY (hobby_id) REFERENCES hobby (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE personne ADD profile_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personne ADD job_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personne DROP job');
        $this->addSql('ALTER TABLE personne ADD CONSTRAINT FK_FCEC9EFCCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE personne ADD CONSTRAINT FK_FCEC9EFBE04EA9 FOREIGN KEY (job_id) REFERENCES job (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FCEC9EFCCFA12B8 ON personne (profile_id)');
        $this->addSql('CREATE INDEX IDX_FCEC9EFBE04EA9 ON personne (job_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE personne DROP CONSTRAINT FK_FCEC9EFBE04EA9');
        $this->addSql('ALTER TABLE personne DROP CONSTRAINT FK_FCEC9EFCCFA12B8');
        $this->addSql('DROP SEQUENCE hobby_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE job_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE profile_id_seq CASCADE');
        $this->addSql('ALTER TABLE personne_hobby DROP CONSTRAINT FK_2D85E25EA21BD112');
        $this->addSql('ALTER TABLE personne_hobby DROP CONSTRAINT FK_2D85E25E322B2123');
        $this->addSql('DROP TABLE hobby');
        $this->addSql('DROP TABLE job');
        $this->addSql('DROP TABLE personne_hobby');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP INDEX UNIQ_FCEC9EFCCFA12B8');
        $this->addSql('DROP INDEX IDX_FCEC9EFBE04EA9');
        $this->addSql('ALTER TABLE personne ADD job VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE personne DROP profile_id');
        $this->addSql('ALTER TABLE personne DROP job_id');
    }
}
