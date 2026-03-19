<?php

declare(strict_types=1);

namespace Moex\Core\Command;

use Moex\Core\Service\Security\SecurityServiceInterface;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'security:aggregates',
    description: 'Get security aggregates from MOEX ISS',
)]
final class SecurityAggregatesCommand extends Command
{
    public function __construct(
        private readonly SecurityServiceInterface $securityService,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addArgument('security', InputArgument::REQUIRED, 'Security ticker')
            ->addOption('date', 'd', InputOption::VALUE_OPTIONAL, 'Date (YYYY-MM-DD)');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $security */
        $security = $input->getArgument('security');
        /** @var string|null $date */
        $date = $input->getOption('date');

        $result = $this->securityService->getAggregates($security, $date);

        if (!$result->success) {
            $output->writeln(sprintf('<error>%s</error>', $result->error ?? 'Unknown error'));
            return Command::FAILURE;
        }

        $output->writeln(sprintf('<info>Aggregates for %s</info>', $security));
        $output->writeln('');

        foreach ($result->aggregates as $agg) {
            $output->writeln(sprintf('<comment>%s</comment>', $agg->marketTitle));
            $output->writeln(sprintf('  Trade Date: %s', $agg->tradeDate));
            $output->writeln(sprintf('  Value: %.2f', $agg->value));
            $output->writeln(sprintf('  Volume: %d', $agg->volume));
            $output->writeln(sprintf('  Trades: %d', $agg->numTrades));
            $output->writeln(sprintf('  Updated: %s', $agg->updatedAt));
            $output->writeln('');
        }

        return Command::SUCCESS;
    }
}
