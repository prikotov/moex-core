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
    name: 'security:specification',
    description: 'Get security specification from MOEX ISS',
)]
final class SecuritySpecificationCommand extends Command
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
        $result = $this->securityService->getSpecification($security);

        if (!$result->success) {
            $output->writeln(sprintf('<error>%s</error>', $result->error ?? 'Unknown error'));
            return Command::FAILURE;
        }

        if ($format !== 'table') {
            $rows = array_map(fn($spec) => [
                $spec->secid,
                $spec->shortName,
                $spec->isin,
                $spec->regNumber,
                $spec->typeName,
                $spec->group,
                (string)$spec->listLevel,
                $spec->issueDate,
                $spec->faceValue ?? '',
                $spec->faceUnit ?? '',
                $spec->issueSize !== null ? (string)$spec->issueSize : '',
            ], $result->specifications);

            return $this->outputFormat(
                $output,
                $format,
                ['Ticker', 'Name', 'ISIN', 'RegNumber', 'Type', 'Group', 'List', 'IssueDate', 'FaceValue', 'Currency', 'IssueSize'],
                $rows,
                sprintf('Specification for %s', $security)
            );
        }

        foreach ($result->specifications as $spec) {
            $output->writeln(sprintf('<info>%s</info>: %s', $spec->name, $spec->secid));
            $output->writeln(sprintf('  Short Name: %s', $spec->shortName));
            $output->writeln(sprintf('  ISIN: %s', $spec->isin));
            $output->writeln(sprintf('  Reg Number: %s', $spec->regNumber));
            $output->writeln(sprintf('  Type: %s', $spec->typeName));
            $output->writeln(sprintf('  Group: %s', $spec->group));
            $output->writeln(sprintf('  List Level: %d', $spec->listLevel));
            $output->writeln(sprintf('  Issue Date: %s', $spec->issueDate));
            if ($spec->faceValue !== null) {
                $output->writeln(sprintf('  Face Value: %s %s', $spec->faceValue, $spec->faceUnit ?? ''));
            }
            if ($spec->issueSize !== null) {
                $output->writeln(sprintf('  Issue Size: %d', $spec->issueSize));
            }
            $output->writeln('');
        }

        return Command::SUCCESS;
    }
}
