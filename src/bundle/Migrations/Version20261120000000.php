<?php

declare(strict_types=1);

namespace Masilia\Bundle\IbexaIcons\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/** The icons of the content types and groups (masilia_icon). A table that exists is left as it is. */
final class Version20261120000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create masilia_icon (the icon of each content type and group) when it does not exist yet.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'This table is created for MySQL / MariaDB.');

        $this->addSql(<<<'SQL'
            CREATE TABLE IF NOT EXISTS masilia_icon (
                kind VARCHAR(10) NOT NULL,
                identifier VARCHAR(190) NOT NULL,
                icon VARCHAR(80) NOT NULL,
                source VARCHAR(10) NOT NULL,
                updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                PRIMARY KEY(kind, identifier)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS masilia_icon');
    }
}
