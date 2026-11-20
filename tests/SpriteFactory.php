<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Tests;

use Masilia\IbexaIcons\Icon\Sprite;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/** A sprite of six icons, for the tests. */
final class SpriteFactory
{
    public static function create(): Sprite
    {
        $project = sys_get_temp_dir() . '/masilia-icons-' . bin2hex(random_bytes(4));
        $dir = $project . '/public/bundles/ibexaadminuiassets/vendors/ids-assets/dist/img';
        mkdir($dir, 0777, true);
        copy(__DIR__ . '/fixtures/all-icons.svg', $dir . '/all-icons.svg');

        return new Sprite($project, new ArrayAdapter());
    }
}
