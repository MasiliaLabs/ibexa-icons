<?php

declare(strict_types=1);

namespace Masilia\Contracts\IbexaIcons;

/**
 * The icons of the content types and groups of the repository, kept in the database so that they change without a deploy.
 * Read and write through this contract, never the table.
 */
interface IconRegistry
{
    public function find(IconKind $kind, string $identifier): ?IconEntry;

    /** @return list<IconEntry> */
    public function all(): array;

    /**
     * Gives an icon to a content type or a group.
     *
     * @return bool false when a choice of a higher rank is kept (an icon picked in the admin is not replaced by the assistant)
     *
     * @throws InvalidIcon When the icon is not a symbol of the Ibexa sprite
     */
    public function set(IconKind $kind, string $identifier, string $icon, IconSource $source): bool;

    public function remove(IconKind $kind, string $identifier): void;
}
