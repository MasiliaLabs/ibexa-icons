<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Capability;

use Masilia\Contracts\IbexaAi\Os\AbstractCapability;
use Masilia\Contracts\IbexaAi\Os\CapabilityResult;
use Masilia\Contracts\IbexaAi\Os\Preview;
use Masilia\Contracts\IbexaAi\Os\Risk;
use Masilia\Contracts\IbexaIcons\IconKind;
use Masilia\Contracts\IbexaIcons\IconRegistry;
use Masilia\Contracts\IbexaIcons\IconSource;
use Masilia\IbexaIcons\Icon\ContentModelScanner;
use Masilia\IbexaIcons\Icon\Sprite;

final class IconsSetCapability extends AbstractCapability
{
    public function __construct(
        private readonly IconRegistry $registry,
        private readonly ContentModelScanner $scanner,
        private readonly Sprite $sprite,
    ) {
    }

    public function name(): string
    {
        return 'icons.set';
    }

    public function summary(): string
    {
        return 'Give an icon to content types and content type groups, several at once.';
    }

    public function description(): string
    {
        return 'Sets the icon of each content type or group in icons: a list of {kind, identifier, icon, reason}. kind is "type" or "group"; '
            . 'identifier is the identifier of the type, or the name of the group, as icons.list gives it; icon must be a name returned by '
            . 'icons.search; reason is a few words that say why it fits (shown for approval). An icon that a person chose in the admin is kept: '
            . 'it is reported as skipped, not replaced. The icons show in the admin at once.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['icons'],
            'properties' => [
                'icons' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 100,
                    'items' => [
                        'type' => 'object',
                        'required' => ['kind', 'identifier', 'icon'],
                        'properties' => [
                            'kind' => ['type' => 'string', 'enum' => ['type', 'group']],
                            'identifier' => ['type' => 'string', 'description' => 'Identifier of the type, or name of the group'],
                            'icon' => ['type' => 'string', 'description' => 'An icon name from icons.search'],
                            'reason' => ['type' => 'string', 'description' => 'Why this icon fits, a few words'],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function risk(): Risk
    {
        return Risk::WRITE;
    }

    public function requires(): array
    {
        return [['class', 'update']];
    }

    public function examples(): array
    {
        return [['description' => 'An icon for the events type', 'arguments' => ['icons' => [['kind' => 'type', 'identifier' => 'event', 'icon' => 'calendar', 'reason' => 'an event has a date']]]]];
    }

    public function validate(array $arguments): ?string
    {
        $existing = [];
        foreach ($this->scanner->scan() as $item) {
            $existing[$item['kind']->value . ':' . $item['identifier']] = true;
        }
        foreach ((array) ($arguments['icons'] ?? []) as $i => $entry) {
            $kind = IconKind::tryFrom((string) ($entry['kind'] ?? ''));
            if ($kind === null) {
                return sprintf('icons[%d]: kind is "type" or "group".', $i);
            }
            $identifier = (string) ($entry['identifier'] ?? '');
            if (!isset($existing[$kind->value . ':' . $identifier])) {
                return sprintf('icons[%d]: there is no %s "%s" (icons.list gives the identifiers).', $i, $kind->value, $identifier);
            }
            $icon = (string) ($entry['icon'] ?? '');
            if (!$this->sprite->has($icon)) {
                return sprintf('icons[%d]: "%s" is not an icon of Ibexa. Closest: %s (icons.search finds more).', $i, $icon, implode(', ', $this->sprite->closest($icon)) ?: 'none');
            }
        }

        return null;
    }

    public function preview(array $arguments): Preview
    {
        $rows = [];
        foreach ((array) ($arguments['icons'] ?? []) as $entry) {
            $reason = trim((string) ($entry['reason'] ?? ''));
            $rows[] = [sprintf('%s %s', $entry['kind'] ?? '?', $entry['identifier'] ?? '?'), ($entry['icon'] ?? '?') . ($reason !== '' ? ' (' . $reason . ')' : '')];
        }

        return new Preview(sprintf('Set %d icon(s)', count($rows)), $rows, ['An icon chosen by a person in the admin is kept.']);
    }

    public function execute(array $arguments): CapabilityResult
    {
        $set = [];
        $skipped = [];
        foreach ((array) $arguments['icons'] as $entry) {
            $kind = IconKind::from((string) $entry['kind']);
            if ($this->registry->set($kind, (string) $entry['identifier'], (string) $entry['icon'], IconSource::Ai)) {
                $set[] = sprintf('%s %s: %s', $kind->value, $entry['identifier'], $entry['icon']);
            } else {
                $skipped[] = sprintf('%s %s (an icon chosen by a person is kept)', $kind->value, $entry['identifier']);
            }
        }

        return CapabilityResult::ok(
            sprintf('%d icon(s) set%s.', count($set), $skipped === [] ? '' : sprintf(', %d skipped', count($skipped))),
            ['set' => $set, 'skipped' => $skipped],
        );
    }
}
