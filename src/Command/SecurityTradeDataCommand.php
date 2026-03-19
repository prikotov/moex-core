<?php

declare(strict_types=1);

namespace Moex\Core\Command;

use Moex\Core\Service\Security\SecurityServiceInterface;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'security:trade-data',
    description: 'Get security trade data from MOEX ISS',
)]
final class SecurityTradeDataCommand extends Command
{
    public function __construct(
        private readonly SecurityServiceInterface $securityService,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->addArgument('security', InputArgument::REQUIRED, 'Security ticker');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $security */
        $security = $input->getArgument('security');
        $result = $this->securityService->getTradeData($security);

        if (!$result->success) {
            $output->writeln(sprintf('<error>%s</error>', $result->error ?? 'Unknown error'));
            return Command::FAILURE;
        }

        foreach ($result->securities as $sec) {
            $output->writeln(sprintf('<info>%s (%s)</info>', $sec->shortName, $sec->secid));
            $output->writeln(sprintf('  Full Name: %s', $sec->secName));
            $output->writeln(sprintf('  Board: %s (%s)', $sec->boardName, $sec->boardId));
            $output->writeln(sprintf('  Previous Price: %.2f', $sec->prevPrice));
            $output->writeln(sprintf('  Previous Date: %s', $sec->prevDate));
        }

        foreach ($result->marketData as $md) {
            $output->writeln('');
            $output->writeln('<comment>Market Data:</comment>');
            if ($md->open !== null) {
                $output->writeln(sprintf('  Open: %.2f', $md->open));
            }
            if ($md->low !== null) {
                $output->writeln(sprintf('  Low: %.2f', $md->low));
            }
            if ($md->high !== null) {
                $output->writeln(sprintf('  High: %.2f', $md->high));
            }
            if ($md->last !== null) {
                $output->writeln(sprintf('  Last: %.2f', $md->last));
            }
            if ($md->valToday !== null) {
                $output->writeln(sprintf('  Volume Today: %d', $md->valToday));
            }
            if ($md->time !== null) {
                $output->writeln(sprintf('  Time: %s', $md->time));
            }
        }

        return Command::SUCCESS;
    }
}
