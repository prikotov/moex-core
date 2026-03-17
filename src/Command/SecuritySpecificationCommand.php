<?php

declare(strict_types=1);

namespace Moex\Skill\Command;

use Moex\Skill\Service\Security\SecurityServiceInterface;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'security:specification',
    description: 'Get security specification from MOEX ISS',
)]
final class SecuritySpecificationCommand extends Command
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
        $security = $input->getArgument('security');
        $result = $this->securityService->getSpecification($security);

        if (!$result->success) {
            $output->writeln(sprintf('<error>%s</error>', $result->error));
            return Command::FAILURE;
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
