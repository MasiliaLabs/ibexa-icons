<?php

declare(strict_types=1);

namespace Masilia\Bundle\IbexaIcons\Command;

use Masilia\Contracts\IbexaIcons\IconRegistry;
use Masilia\Contracts\IbexaIcons\IconSource;
use Masilia\IbexaIcons\Icon\ContentModelScanner;
use Masilia\IbexaIcons\Icon\DefaultIcons;
use Masilia\IbexaIcons\Icon\Sprite;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'masilia:icons:defaults', description: 'Give the icons that the Masilia packages suggest to the types and groups that have none.')]
final class IconsDefaultsCommand extends Command
{
    public function __construct(
        private readonly IconRegistry $registry,
        private readonly ContentModelScanner $scanner,
        private readonly Sprite $sprite,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be given, write nothing');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $given = 0;
        foreach ($this->scanner->scan() as $item) {
            if ($item['icon'] !== null) {
                continue;
            }
            $icon = DefaultIcons::for($item['kind'], $item['identifier']);
            if ($icon === null || !$this->sprite->has($icon)) {
                continue;
            }
            $io->writeln(sprintf(' %s %s "%s": %s', $input->getOption('dry-run') ? 'would give' : 'gave', $item['kind']->value, $item['identifier'], $icon));
            if (!$input->getOption('dry-run')) {
                $this->registry->set($item['kind'], $item['identifier'], $icon, IconSource::Default);
            }
            ++$given;
        }
        $io->success(sprintf('%d icon(s) %s.', $given, $input->getOption('dry-run') ? 'would be given' : 'given'));

        return Command::SUCCESS;
    }
}
