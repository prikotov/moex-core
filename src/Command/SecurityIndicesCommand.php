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
    name: 'security:indices',
    description: 'Get security indices from MOEX ISS',
)]
final class SecurityIndicesCommand extends Command
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
        $result = $this->securityService->getIndices($security);

        if (!$result->success) {
            $output->writeln(sprintf('<error>%s</error>', $result->error ?? 'Unknown error'));
            return Command::FAILURE;
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
