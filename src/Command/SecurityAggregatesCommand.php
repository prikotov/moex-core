<?php

declare(strict_types=1);

namespace Moex\Core\Command;

use Moex\Core\Helper\OutputFormatTrait;
use Moex\Core\Service\Security\Dto\SecurityAggregateDto;
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
    use OutputFormatTrait;

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
            ->addOption('date', 'd', InputOption::VALUE_OPTIONAL, 'Date (YYYY-MM-DD)')
            ->addOption('sort', 's', InputOption::VALUE_OPTIONAL, 'Sort by: value, volume, trades, date', 'date')
            ->addOption('order', 'o', InputOption::VALUE_OPTIONAL, 'Sort order: asc, desc', 'desc')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit number of results', '0')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format: table, json, csv, md', 'table');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $security */
        $security = $input->getArgument('security');
        /** @var string|null $date */
        $date = $input->getOption('date');
        $sort = $input->getOption('sort');
        $order = $input->getOption('order');
        $limit = (int)$input->getOption('limit');
        $format = $this->getFormat($input);

        $result = $this->securityService->getAggregates($security, $date);

        if (!$result->success) {
            $output->writeln(sprintf('<error>%s</error>', $result->error ?? 'Unknown error'));
            return Command::FAILURE;
        }

        $aggregates = $this->sortAggregates($result->aggregates, $sort, $order);
        if ($limit > 0) {
            $aggregates = array_slice($aggregates, 0, $limit);
        }

        if ($format !== 'table') {
            $rows = array_map(fn(SecurityAggregateDto $agg) => [
                $agg->marketTitle,
                $agg->tradeDate,
                number_format($agg->value, 2),
                (string)$agg->volume,
                (string)$agg->numTrades,
                $agg->updatedAt,
            ], $aggregates);

            return $this->outputFormat(
                $output,
                $format,
                ['Market', 'Date', 'Value', 'Volume', 'Trades', 'Updated'],
                $rows,
                sprintf('Aggregates for %s', $security)
            );
        }

        return $this->outputTable($output, $security, $aggregates);
    }

    /**
     * @param array<SecurityAggregateDto> $aggregates
     * @return array<SecurityAggregateDto>
     */
    private function sortAggregates(array $aggregates, string $sort, string $order): array
    {
        $comparator = match ($sort) {
            'value' => fn(SecurityAggregateDto $a, SecurityAggregateDto $b) => $a->value <=> $b->value,
            'volume' => fn(SecurityAggregateDto $a, SecurityAggregateDto $b) => $a->volume <=> $b->volume,
            'trades' => fn(SecurityAggregateDto $a, SecurityAggregateDto $b) => $a->numTrades <=> $b->numTrades,
            default => fn(SecurityAggregateDto $a, SecurityAggregateDto $b) => strcmp($a->tradeDate, $b->tradeDate),
        };

        usort($aggregates, $comparator);

        if ($order === 'desc') {
            $aggregates = array_reverse($aggregates);
        }

        return $aggregates;
    }

    /**
     * @param array<SecurityAggregateDto> $aggregates
     */
    private function outputTable(OutputInterface $output, string $security, array $aggregates): int
    {
        $output->writeln(sprintf('<info>Aggregates for %s</info>', $security));
        $output->writeln('');

        foreach ($aggregates as $agg) {
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
