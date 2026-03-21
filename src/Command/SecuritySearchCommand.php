<?php

declare(strict_types=1);

namespace Moex\Core\Command;

use Moex\Core\Helper\OutputFormatTrait;
use Moex\Core\Service\Security\SecurityServiceInterface;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'security:search',
    description: 'Search securities on MOEX by name or ticker',
)]
final class SecuritySearchCommand extends Command
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
            ->addArgument('query', InputArgument::REQUIRED, 'Search query (name or ticker)')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit results', 20)
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format: table, json, csv, md', 'table');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $query */
        $query = $input->getArgument('query');
        /** @var int $limit */
        $limit = (int)$input->getOption('limit');
        $format = $this->getFormat($input);

        $result = $this->securityService->search($query);

        if (!$result->success) {
            $output->writeln(sprintf('<error>%s</error>', $result->error ?? 'Unknown error'));
            return Command::FAILURE;
        }

        if (empty($result->securities)) {
            $output->writeln('<comment>No securities found</comment>');
            return Command::SUCCESS;
        }

        $securities = array_slice($result->securities, 0, $limit);

        if ($format !== 'table') {
            $rows = array_map(fn($sec) => [
                $sec->secid,
                $sec->shortName,
                $sec->typeName,
                $sec->group,
            ], $securities);

            return $this->outputFormat(
                $output,
                $format,
                ['Ticker', 'Name', 'Type', 'Group'],
                $rows,
                sprintf('Search results for "%s"', $query)
            );
        }

        foreach ($securities as $sec) {
            $output->writeln(sprintf(
                '<info>%-10s</info> %s [%s]',
                $sec->secid,
                $sec->shortName,
                $sec->group
            ));
        }

        $output->writeln('');
        $output->writeln(sprintf('<comment>Total: %d (showing %d)</comment>', count($result->securities), count($securities)));

        return Command::SUCCESS;
    }
}
