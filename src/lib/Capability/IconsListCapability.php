<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Capability;

use Masilia\Contracts\IbexaAi\Os\AbstractCapability;
use Masilia\Contracts\IbexaAi\Os\CapabilityResult;
use Masilia\Contracts\IbexaAi\Os\Risk;
use Masilia\Contracts\IbexaIcons\IconKind;
use Masilia\IbexaIcons\Icon\ContentModelScanner;

final class IconsListCapability extends AbstractCapability
{
    public function __construct(private readonly ContentModelScanner $scanner)
    {
    }

    public function name(): string
    {
        return 'icons.list';
    }

    public function summary(): string
    {
        return 'List the content types and content type groups with their icon, or only the ones that have none.';
    }

    public function description(): string
    {
        return 'Lists every content type and every content type group of the repository: identifier, name, group, the icon it has now '
            . '(null when it has none: Ibexa then shows a default icon) and where that icon comes from (admin: a person chose it; ai; import; '
            . 'default; config: the YAML of the project; none). Use missing_only=true to get only those without an icon, then pick an icon for '
            . 'each with icons.search and give it with icons.set.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'missing_only' => ['type' => 'boolean', 'description' => 'Only the types and groups that have no icon'],
                'kind' => ['type' => 'string', 'enum' => ['type', 'group'], 'description' => 'Only content types, or only groups'],
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
        return [['description' => 'What still needs an icon', 'arguments' => ['missing_only' => true]]];
    }

    public function execute(array $arguments): CapabilityResult
    {
        $kind = isset($arguments['kind']) ? IconKind::tryFrom((string) $arguments['kind']) : null;
        $items = [];
        foreach ($this->scanner->scan() as $item) {
            if ($kind !== null && $item['kind'] !== $kind) {
                continue;
            }
            if (($arguments['missing_only'] ?? false) === true && $item['icon'] !== null) {
                continue;
            }
            $items[] = ['kind' => $item['kind']->value] + array_diff_key($item, ['kind' => true]);
        }

        return CapabilityResult::ok(
            sprintf('%d content types and groups listed.', count($items)),
            ['total' => count($items), 'items' => $items],
        );
    }
}
