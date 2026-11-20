<?php

declare(strict_types=1);

namespace Masilia\Bundle\IbexaIcons\Command;

use Masilia\Contracts\IbexaIcons\IconKind;
use Masilia\Contracts\IbexaIcons\IconRegistry;
use Masilia\Contracts\IbexaIcons\IconSource;
use Masilia\Contracts\IbexaIcons\InvalidIcon;
use Masilia\IbexaIcons\Icon\ContentModelScanner;
use Masilia\IbexaIcons\Icon\LegacyIcons;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(name: 'masilia:icons:import', description: 'Bring the icons of an Ibexa YAML file (content_type and content_type_group thumbnails) into the registry.')]
final class IconsImportCommand extends Command
{
    public function __construct(
        private readonly IconRegistry $registry,
        private readonly ContentModelScanner $scanner,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'A YAML file, e.g. config/packages/ibexa_admin_icons.yaml')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be imported, write nothing');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = (string) $input->getArgument('file');
        if (!is_file($file)) {
            $io->error(sprintf('"%s" is not a file.', $file));

            return Command::FAILURE;
        }
        $legacy = LegacyIcons::read((array) Yaml::parseFile($file));
        $items = $this->scanner->scan();
        $types = array_map(static fn (array $i): string => $i['identifier'], array_filter($items, static fn (array $i): bool => $i['kind'] === IconKind::Type));
        $groups = array_map(static fn (array $i): string => $i['identifier'], array_filter($items, static fn (array $i): bool => $i['kind'] === IconKind::Group));
        $done = 0;
        $skipped = [];
        foreach ($legacy as ['kind' => $kind, 'identifier' => $identifier, 'icon' => $icon]) {
            $target = $kind === IconKind::Group ? LegacyIcons::matchGroup($identifier, array_values($groups)) : (in_array($identifier, $types, true) ? $identifier : null);
            if ($target === null) {
                $skipped[] = sprintf('%s "%s": not in the repository', $kind->value, $identifier);
                continue;
            }
            if ($input->getOption('dry-run')) {
                $io->writeln(sprintf(' would set %s "%s" to "%s"', $kind->value, $target, $icon));
                ++$done;
                continue;
            }
            try {
                if ($this->registry->set($kind, $target, $icon, IconSource::Import)) {
                    ++$done;
                } else {
                    $skipped[] = sprintf('%s "%s": a higher choice is kept', $kind->value, $target);
                }
            } catch (InvalidIcon $e) {
                $skipped[] = sprintf('%s "%s": %s', $kind->value, $target, $e->getMessage());
            }
        }
        $io->success(sprintf('%d icon(s) %s.', $done, $input->getOption('dry-run') ? 'would be imported' : 'imported'));
        if ($skipped !== []) {
            $io->section(sprintf('Skipped (%d)', count($skipped)));
            $io->listing($skipped);
        }

        return Command::SUCCESS;
    }
}
