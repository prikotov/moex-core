<?php

declare(strict_types=1);

namespace Moex\Core\Command;

use Moex\Core\Service\Schedule\ScheduleServiceInterface;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'schedule',
    description: 'Get trading schedule from MOEX ISS',
)]
final class ScheduleCommand extends Command
{
    public function __construct(
        private readonly ScheduleServiceInterface $scheduleService,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addOption('engine', 'e', InputOption::VALUE_OPTIONAL, 'Engine (stock, currency, futures)', 'stock')
            ->addOption('market', 'm', InputOption::VALUE_OPTIONAL, 'Market (shares, bonds, currency)');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string|null $engine */
        $engine = $input->getOption('engine');
        /** @var string|null $market */
        $market = $input->getOption('market');

        $result = $this->scheduleService->getTradingSchedule($engine, $market);

        if (!$result->success) {
            $output->writeln(sprintf('<error>%s</error>', $result->error ?? 'Unknown error'));
            return Command::FAILURE;
        }

        if (empty($result->sessions)) {
            $output->writeln('<comment>No trading sessions found</comment>');
            return Command::SUCCESS;
        }

        $output->writeln('<info>MOEX Trading Schedule</info>');
        $output->writeln('');

        foreach ($result->sessions as $session) {
            $output->writeln(sprintf(
                '<info>%s / %s</info>',
                strtoupper($session->engine),
                $session->title
            ));

            if ($session->auctionStart !== null && $session->auctionEnd !== null) {
                $output->writeln(sprintf(
                    '  Auction: %s - %s',
                    $session->auctionStart,
                    $session->auctionEnd
                ));
            }

            if ($session->startTime !== null && $session->endTime !== null) {
                $output->writeln(sprintf(
                    '  Main session: %s - %s',
                    $session->startTime,
                    $session->endTime
                ));
            }

            if ($session->clearingStart !== null && $session->clearingEnd !== null) {
                $output->writeln(sprintf(
                    '  Clearing: %s - %s',
                    $session->clearingStart,
                    $session->clearingEnd
                ));
            }

            if ($session->eveningStart !== null && $session->eveningEnd !== null) {
                $output->writeln(sprintf(
                    '  Evening session: %s - %s',
                    $session->eveningStart,
                    $session->eveningEnd
                ));
            }

            $output->writeln('');
        }

        return Command::SUCCESS;
    }
}
