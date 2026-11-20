<?php

declare(strict_types=1);

namespace Masilia\Contracts\IbexaIcons;

/** What an icon belongs to: a content type, or a content type group. */
enum IconKind: string
{
    case Type = 'type';
    case Group = 'group';

    /** The prefix of the Ibexa configuration parameter that holds the icon of this kind (content_type.<id>). */
    public function parameterPrefix(): string
    {
        return match ($this) {
            self::Type => 'content_type.',
            self::Group => 'content_type_group.',
        };
    }
}
