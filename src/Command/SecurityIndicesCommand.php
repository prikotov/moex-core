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
    name: 'security:indices',
    description: 'Get security indices from MOEX ISS',
)]
final class SecurityIndicesCommand extends Command
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
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format: table, json, csv, md', 'table');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $security */
        $security = $input->getArgument('security');
        $format = $this->getFormat($input);
        $result = $this->securityService->getIndices($security);

        if (!$result->success) {
            $output->writeln(sprintf('<error>%s</error>', $result->error ?? 'Unknown error'));
            return Command::FAILURE;
        }

        if ($format !== 'table') {
            $rows = array_map(fn($idx) => [
                $idx->secid,
                $idx->shortName,
                $idx->from,
                $idx->till ?? '',
                $idx->currentValue !== null ? number_format($idx->currentValue, 2) : '',
                $idx->lastChangePrc !== null ? number_format($idx->lastChangePrc, 2) : '',
                $idx->lastChange !== null ? number_format($idx->lastChange, 2) : '',
            ], $result->indices);

            return $this->outputFormat(
                $output,
                $format,
                ['Ticker', 'Name', 'From', 'Till', 'Value', 'Change%', 'Change'],
                $rows,
                sprintf('Indices for %s', $security)
            );
        }

        $output->writeln(sprintf('<info>Indices for %s</info>', $security));
        $output->writeln('');

        foreach ($result->indices as $idx) {
            $output->writeln(sprintf('<comment>%s (%s)</comment>', $idx->shortName, $idx->secid));
            $output->writeln(sprintf('  From: %s', $idx->from));
            if ($idx->till !== null) {
                $output->writeln(sprintf('  Till: %s', $idx->till));
            }
            if ($idx->currentValue !== null) {
                $output->writeln(sprintf('  Current Value: %.2f', $idx->currentValue));
            }
            if ($idx->lastChangePrc !== null) {
                $output->writeln(sprintf('  Last Change %%: %.2f', $idx->lastChangePrc));
            }
            if ($idx->lastChange !== null) {
                $output->writeln(sprintf('  Last Change: %.2f', $idx->lastChange));
            }
            $output->writeln('');
        }

        return Command::SUCCESS;
    }
}
