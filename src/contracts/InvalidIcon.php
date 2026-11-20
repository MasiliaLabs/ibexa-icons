<?php

declare(strict_types=1);

namespace Masilia\Contracts\IbexaIcons;

/** The icon is not a symbol of the Ibexa sprite. The message names the closest ones. */
final class InvalidIcon extends \InvalidArgumentException
{
    /** @param list<string> $closest */
    public static function unknown(string $icon, array $closest): self
    {
        return new self(sprintf(
            'The icon "%s" is not in the Ibexa icon set.%s',
            $icon,
            $closest === [] ? '' : ' Closest: ' . implode(', ', $closest) . '.',
        ));
    }
}
