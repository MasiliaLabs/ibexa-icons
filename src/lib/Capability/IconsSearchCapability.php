<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Capability;

use Masilia\Contracts\IbexaAi\Os\AbstractCapability;
use Masilia\Contracts\IbexaAi\Os\CapabilityResult;
use Masilia\Contracts\IbexaAi\Os\Risk;
use Masilia\IbexaIcons\Icon\Sprite;

final class IconsSearchCapability extends AbstractCapability
{
    public function __construct(private readonly Sprite $sprite)
    {
    }

    public function name(): string
    {
        return 'icons.search';
    }

    public function summary(): string
    {
        return 'Search the icons of the Ibexa icon set by keywords (English), to choose one for a content type or group.';
    }

    public function description(): string
    {
        return 'The Ibexa icon set has hundreds of icons named in English (home, calendar, user-add, file-text, folder, banner...). Give a few '
            . 'words that describe what the content type is ("event calendar", "team member user") and get the best matching icon names, best '
            . 'first. Try several searches and pick one icon per type or group; only the names returned here are valid for icons.set.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['query'],
            'properties' => [
                'query' => ['type' => 'string', 'minLength' => 1, 'description' => 'Words that describe the type or group, in English'],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 40, 'description' => 'How many icon names to return (default 15)'],
            ],
        ];
    }

    public function risk(): Risk
    {
        return Risk::READ;
    }

    public function requires(): array
    {
        return [['content', 'read']];
    }

    public function examples(): array
    {
        return [['description' => 'An icon for events', 'arguments' => ['query' => 'event calendar']]];
    }

    public function validate(array $arguments): ?string
    {
        return trim((string) ($arguments['query'] ?? '')) === '' ? 'Give some words to search for.' : null;
    }

    public function execute(array $arguments): CapabilityResult
    {
        $icons = $this->sprite->search((string) $arguments['query'], (int) ($arguments['limit'] ?? 15));

        return CapabilityResult::ok(
            $icons === [] ? 'No icon matches: try other words.' : sprintf('%d matching icons, best first.', count($icons)),
            ['icons' => $icons],
        );
    }
}
