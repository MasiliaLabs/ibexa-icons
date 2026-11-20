<?php

declare(strict_types=1);

namespace Masilia\Bundle\IbexaIcons\Command;

use Masilia\IbexaIcons\Icon\ContentModelScanner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'masilia:icons:list', description: 'List the content types and groups of the repository with their icon and where it comes from.')]
final class IconsListCommand extends Command
{
    public function __construct(private readonly ContentModelScanner $scanner)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('missing', 'm', InputOption::VALUE_NONE, 'Only the types and groups that have no icon')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Print JSON')
            ->setHelp('The icons of the YAML of the project are read in the scope of the siteaccess: run with --siteaccess=admin (the admin group) to see them as "config".');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $items = array_values(array_filter(
            $this->scanner->scan(),
            static fn (array $item): bool => !$input->getOption('missing') || $item['icon'] === null,
        ));
        $rows = array_map(static fn (array $i): array => [$i['kind']->value, $i['identifier'], $i['name'], $i['group'], $i['icon'] ?? '-', $i['source']], $items);
        if ($input->getOption('json')) {
            $output->writeln((string) json_encode(array_map(static fn (array $i): array => ['kind' => $i['kind']->value] + $i, $items), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES));

            return Command::SUCCESS;
        }
        $io = new SymfonyStyle($input, $output);
        $io->table(['Kind', 'Identifier', 'Name', 'Group', 'Icon', 'From'], $rows);
        $io->writeln(sprintf('%d shown, %d without an icon.', count($items), count(array_filter($items, static fn (array $i): bool => $i['icon'] === null))));

        return Command::SUCCESS;
    }
}
