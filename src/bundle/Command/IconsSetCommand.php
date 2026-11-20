<?php

declare(strict_types=1);

namespace Masilia\Bundle\IbexaIcons\Command;

use Masilia\Contracts\IbexaIcons\IconKind;
use Masilia\Contracts\IbexaIcons\IconRegistry;
use Masilia\Contracts\IbexaIcons\IconSource;
use Masilia\Contracts\IbexaIcons\InvalidIcon;
use Masilia\IbexaIcons\Icon\ContentModelScanner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'masilia:icons:set', description: 'Give an icon (a symbol of the Ibexa icon set) to a content type or a group.')]
final class IconsSetCommand extends Command
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
            ->addArgument('kind', InputArgument::REQUIRED, 'type or group')
            ->addArgument('identifier', InputArgument::REQUIRED, 'The identifier of the content type, or the name of the group')
            ->addArgument('icon', InputArgument::REQUIRED, 'The name of a symbol, e.g. home, calendar, folder');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $kind = IconKind::tryFrom((string) $input->getArgument('kind'));
        if ($kind === null) {
            $io->error('The kind is "type" or "group".');

            return Command::INVALID;
        }
        $identifier = (string) $input->getArgument('identifier');
        if (!$this->scanner->exists($kind, $identifier)) {
            $io->error(sprintf('There is no %s "%s" in the repository (masilia:icons:list shows them).', $kind->value, $identifier));

            return Command::FAILURE;
        }
        try {
            $this->registry->set($kind, $identifier, (string) $input->getArgument('icon'), IconSource::Admin);
        } catch (InvalidIcon $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
        $io->success(sprintf('%s "%s" has the icon "%s".', ucfirst($kind->value), $identifier, $input->getArgument('icon')));

        return Command::SUCCESS;
    }
}
