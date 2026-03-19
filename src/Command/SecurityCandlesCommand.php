<?php

declare(strict_types=1);

namespace Moex\Core\Command;

use Moex\Core\Service\Candle\CandleServiceInterface;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'security:candles',
    description: 'Get historical candles for a security',
)]
final class SecurityCandlesCommand extends Command
{
    public function __construct(
        private readonly CandleServiceInterface $candleService,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addArgument('ticker', InputArgument::REQUIRED, 'Security ticker (e.g. SBER)')
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'Start date (Y-m-d)')
            ->addOption('to', 't', InputOption::VALUE_OPTIONAL, 'End date (Y-m-d)')
            ->addOption('interval', 'i', InputOption::VALUE_OPTIONAL, 'Candle interval (minutes)', '60')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Max candles to show', '100');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ticker = $input->getArgument('ticker');
        $from = $input->getOption('from');
        $to = $input->getOption('to');
        $interval = $input->getOption('interval');
        $limit = (int)$input->getOption('limit');

        $result = $this->candleService->getCandles($ticker, $from, $to, $interval);

        if (!$result->success) {
            $output->writeln(sprintf('<error>Error: %s</error>', $result->error));
            return Command::FAILURE;
        }

        $candles = array_slice($result->candles, -$limit);

        if ($candles === []) {
            $output->writeln('<comment>No candles found</comment>');
            return Command::SUCCESS;
        }

        $output->writeln(sprintf('<info>Candles for %s:</info>', $ticker));
        $output->writeln('');

        $output->writeln(sprintf(
            '<info>%-20s %10s %10s %10s %10s %12s</info>',
            'Time',
            'Open',
            'High',
            'Low',
            'Close',
            'Volume'
        ));

        foreach ($candles as $candle) {
            $output->writeln(sprintf(
                '%-20s %10.2f %10.2f %10.2f %10.2f %12d',
                $candle->begin->format('Y-m-d H:i'),
                $candle->open,
                $candle->high,
                $candle->low,
                $candle->close,
                $candle->volume
            ));
        }

        $output->writeln('');
        $output->writeln(sprintf('<info>Total candles: %d</info>', count($candles)));

        return Command::SUCCESS;
    }
}
