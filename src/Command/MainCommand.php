<?php

declare(strict_types=1);

namespace Moex\Skill\Command;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'main',
    description: 'MOEX Skill CLI',
)]
final class MainCommand extends Command
{
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>MOEX Skill</info>');
        $output->writeln('<comment>Use --help to see available commands</comment>');

        return Command::SUCCESS;
    }
}
