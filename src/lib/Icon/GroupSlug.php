<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Icon;

use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * How Ibexa writes the name of a group in its icon settings: it slugs it (ContentTypeGroupIconResolver), so the group "Block items" has
 * its icon under `content_type_group.block_items`. The registry keeps the real name and answers to this form.
 */
final class GroupSlug
{
    public static function of(string $groupName): string
    {
        return (string) (new AsciiSlugger())->slug($groupName, '_')->lower();
    }
}
