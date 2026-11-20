<?php

declare(strict_types=1);

namespace Masilia\Contracts\IbexaIcons;

/**
 * Who chose an icon. A choice replaces one of the same rank or lower, never a higher one: what a person picked in the admin is
 * not undone by the assistant, an import or the defaults of the package.
 */
enum IconSource: string
{
    case Default = 'default';
    case Import = 'import';
    case Ai = 'ai';
    case Admin = 'admin';

    public function rank(): int
    {
        return match ($this) {
            self::Default => 0,
            self::Import => 1,
            self::Ai => 2,
            self::Admin => 3,
        };
    }

    /** Whether a choice by this source may replace a choice by $existing. */
    public function canReplace(self $existing): bool
    {
        return $this->rank() >= $existing->rank();
    }
}
