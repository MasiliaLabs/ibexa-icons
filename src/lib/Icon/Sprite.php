<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Icon;

use Psr\Cache\CacheItemPoolInterface;

/**
 * The icons of Ibexa: the symbols of its SVG sprite. Only the name of a symbol is kept anywhere; the path of the sprite is here,
 * in one place, so that a change of path in Ibexa is one line.
 */
final class Sprite
{
    /** Where the browser finds the sprite. */
    public const PATH = '/bundles/ibexaadminuiassets/vendors/ids-assets/dist/img/all-icons.svg';

    /** Where the file is, relative to the project: the published copy, then the one of the package. */
    private const FILES = [
        '/public/bundles/ibexaadminuiassets/vendors/ids-assets/dist/img/all-icons.svg',
        '/vendor/ibexa/admin-ui-assets/src/bundle/Resources/public/vendors/ids-assets/dist/img/all-icons.svg',
    ];

    /** @var list<string>|null */
    private ?array $ids = null;

    public function __construct(
        private readonly string $projectDir,
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    /** The value of a `thumbnail` setting of Ibexa for an icon. */
    public static function thumbnail(string $icon): string
    {
        return self::PATH . '#' . $icon;
    }

    /** The icon of a `thumbnail` setting (".../all-icons.svg#home" is "home"), or null when it is not a symbol reference. */
    public static function iconOf(string $thumbnail): ?string
    {
        $position = strrpos($thumbnail, '#');
        if ($position === false || $position === strlen($thumbnail) - 1) {
            return null;
        }

        return substr($thumbnail, $position + 1);
    }

    /** @return list<string> every symbol of the sprite, in alphabetical order */
    public function ids(): array
    {
        if ($this->ids !== null) {
            return $this->ids;
        }
        $file = $this->file();
        if ($file === null) {
            return $this->ids = [];
        }
        $item = $this->cache->getItem('masilia_icons_sprite_' . md5($file . '|' . (string) filemtime($file)));
        if ($item->isHit()) {
            return $this->ids = $item->get();
        }
        preg_match_all('/<symbol[^>]*?\sid="([^"]+)"/s', (string) file_get_contents($file), $found);
        $ids = array_values(array_unique($found[1]));
        sort($ids);
        $this->cache->save($item->set($ids));

        return $this->ids = $ids;
    }

    public function has(string $icon): bool
    {
        return in_array($icon, $this->ids(), true);
    }

    /**
     * The symbols that match words: "calendar" finds "calendar", "calendar-check"... The best matches come first.
     *
     * @return list<string>
     */
    public function search(string $query, int $limit = 20): array
    {
        // One-letter words ("a") are in every name: they say nothing.
        $words = array_values(array_filter(preg_split('/[^a-z0-9]+/', strtolower($query)) ?: [], static fn (string $word): bool => strlen($word) >= 2));
        if ($words === []) {
            return [];
        }
        $scores = [];
        foreach ($this->ids() as $id) {
            $parts = explode('-', $id);
            $score = 0;
            foreach ($words as $word) {
                if ($id === $word) {
                    $score += 10;
                } elseif (in_array($word, $parts, true)) {
                    $score += 5;
                } elseif (str_contains($id, $word)) {
                    $score += 2;
                }
            }
            if ($score > 0) {
                $scores[$id] = $score;
            }
        }
        uksort($scores, static fn (string $a, string $b): int => [$scores[$b], $a] <=> [$scores[$a], $b]);

        return array_slice(array_keys($scores), 0, max(1, $limit));
    }

    /**
     * The symbols that look like a name that is not one (a typo, a name from another set).
     *
     * @return list<string>
     */
    public function closest(string $icon, int $limit = 5): array
    {
        $found = $this->search($icon, $limit);
        if (count($found) >= $limit) {
            return $found;
        }
        $distances = [];
        foreach ($this->ids() as $id) {
            $distances[$id] = levenshtein(substr($icon, 0, 60), $id);
        }
        asort($distances);

        return array_values(array_unique([...$found, ...array_slice(array_keys($distances), 0, $limit)]));
    }

    private function file(): ?string
    {
        foreach (self::FILES as $relative) {
            if (is_file($this->projectDir . $relative)) {
                return $this->projectDir . $relative;
            }
        }

        return null;
    }
}
