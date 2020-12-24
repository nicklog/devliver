<?php

declare(strict_types=1);

namespace App\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201224124920 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Setup';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE author (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name LONGTEXT DEFAULT NULL, email LONGTEXT DEFAULT NULL, homepage LONGTEXT DEFAULT NULL, role LONGTEXT DEFAULT NULL, created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimeutc)\', updated DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimeutc)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE download (id INT UNSIGNED AUTO_INCREMENT NOT NULL, package_id INT UNSIGNED NOT NULL, version_id INT UNSIGNED DEFAULT NULL, version_name VARCHAR(255) DEFAULT NULL, created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimeutc)\', updated DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimeutc)\', INDEX IDX_781A8270F44CABFF (package_id), INDEX IDX_781A82704BBC2705 (version_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE package (id INT UNSIGNED AUTO_INCREMENT NOT NULL, creator_id INT UNSIGNED DEFAULT NULL, repo_id INT UNSIGNED DEFAULT NULL, type VARCHAR(255) DEFAULT \'vcs\' NOT NULL, url VARCHAR(255) NOT NULL, enable TINYINT(1) DEFAULT \'1\' NOT NULL, abandoned TINYINT(1) DEFAULT \'0\' NOT NULL, replacement_package VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, readme LONGTEXT DEFAULT NULL, auto_update TINYINT(1) DEFAULT \'0\' NOT NULL, last_update DATETIME NOT NULL COMMENT \'(DC2Type:datetimeutc)\', created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimeutc)\', updated DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimeutc)\', INDEX IDX_DE68679561220EA6 (creator_id), UNIQUE INDEX UNIQ_DE686795BD359B2D (repo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE repo (id INT UNSIGNED AUTO_INCREMENT NOT NULL, creator_id INT UNSIGNED DEFAULT NULL, type VARCHAR(255) DEFAULT \'vcs\' NOT NULL, url VARCHAR(255) NOT NULL, created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimeutc)\', updated DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimeutc)\', INDEX IDX_5C5CBBFF61220EA6 (creator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimeutc)\', updated DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimeutc)\', UNIQUE INDEX UNIQ_389B7835E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE update_queue (id INT UNSIGNED AUTO_INCREMENT NOT NULL, package_id INT UNSIGNED NOT NULL, locked_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimeutc)\', last_called_at DATETIME NOT NULL COMMENT \'(DC2Type:datetimeutc)\', created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimeutc)\', updated DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimeutc)\', INDEX IDX_1F999DE1F44CABFF (package_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT UNSIGNED AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) DEFAULT NULL, password_reset_token VARCHAR(255) DEFAULT NULL, password_reset_token_expire_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimeutc)\', api_token VARCHAR(255) DEFAULT NULL, repository_token VARCHAR(255) DEFAULT NULL, package_root_access TINYINT(1) DEFAULT \'1\' NOT NULL, auto_add_to_new_packages TINYINT(1) DEFAULT \'1\' NOT NULL, auto_add_to_new_versions TINYINT(1) DEFAULT \'1\' NOT NULL, created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimeutc)\', updated DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimeutc)\', UNIQUE INDEX UNIQ_8D93D6497BA2F5EB (api_token), UNIQUE INDEX UNIQ_8D93D649F155E556 (repository_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_package (user_id INT UNSIGNED NOT NULL, package_id INT UNSIGNED NOT NULL, INDEX IDX_8665799FA76ED395 (user_id), INDEX IDX_8665799FF44CABFF (package_id), PRIMARY KEY(user_id, package_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_version (user_id INT UNSIGNED NOT NULL, version_id INT UNSIGNED NOT NULL, INDEX IDX_E711CDC9A76ED395 (user_id), INDEX IDX_E711CDC94BBC2705 (version_id), PRIMARY KEY(user_id, version_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE version (id INT UNSIGNED AUTO_INCREMENT NOT NULL, package_id INT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimeutc)\', updated DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimeutc)\', INDEX IDX_BF1CD3C3F44CABFF (package_id), INDEX IDX_BF1CD3C3F44CABFF5E237E06 (package_id, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE version_author (version_id INT UNSIGNED NOT NULL, author_id INT UNSIGNED NOT NULL, INDEX IDX_CDC2F0934BBC2705 (version_id), INDEX IDX_CDC2F093F675F31B (author_id), PRIMARY KEY(version_id, author_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE version_tag (version_id INT UNSIGNED NOT NULL, tag_id INT UNSIGNED NOT NULL, INDEX IDX_187C97B84BBC2705 (version_id), INDEX IDX_187C97B8BAD26311 (tag_id), PRIMARY KEY(version_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetimeutc)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetimeutc)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimeutc)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE download ADD CONSTRAINT FK_781A8270F44CABFF FOREIGN KEY (package_id) REFERENCES package (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE download ADD CONSTRAINT FK_781A82704BBC2705 FOREIGN KEY (version_id) REFERENCES version (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE package ADD CONSTRAINT FK_DE68679561220EA6 FOREIGN KEY (creator_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE package ADD CONSTRAINT FK_DE686795BD359B2D FOREIGN KEY (repo_id) REFERENCES repo (id)');
        $this->addSql('ALTER TABLE repo ADD CONSTRAINT FK_5C5CBBFF61220EA6 FOREIGN KEY (creator_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE update_queue ADD CONSTRAINT FK_1F999DE1F44CABFF FOREIGN KEY (package_id) REFERENCES package (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_package ADD CONSTRAINT FK_8665799FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_package ADD CONSTRAINT FK_8665799FF44CABFF FOREIGN KEY (package_id) REFERENCES package (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_version ADD CONSTRAINT FK_E711CDC9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_version ADD CONSTRAINT FK_E711CDC94BBC2705 FOREIGN KEY (version_id) REFERENCES version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE version ADD CONSTRAINT FK_BF1CD3C3F44CABFF FOREIGN KEY (package_id) REFERENCES package (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE version_author ADD CONSTRAINT FK_CDC2F0934BBC2705 FOREIGN KEY (version_id) REFERENCES version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE version_author ADD CONSTRAINT FK_CDC2F093F675F31B FOREIGN KEY (author_id) REFERENCES author (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE version_tag ADD CONSTRAINT FK_187C97B84BBC2705 FOREIGN KEY (version_id) REFERENCES version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE version_tag ADD CONSTRAINT FK_187C97B8BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
    }
}
