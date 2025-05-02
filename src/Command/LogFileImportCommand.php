<?php

namespace App\Command;

use App\Service\LogFileImporterInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to import log entries from a file into the database.
 *
 * Usage:
 *   php bin/console log:import /path/to/logfile.log
 *
 * This command reads each line from the specified log file, parses it,
 * and persists valid entries into the database.
 */
#[AsCommand(
    name: 'log:import',
    description: 'Imports log file entries into the database',
)]
class LogFileImportCommand extends Command
{
    public function __construct(private LogFileImporterInterface $logFileImporter)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'The path to the log file to process')
            ->setHelp(<<<HELP
The <info>log:import</info> command reads a log file line by line and stores valid entries into the database.

Example usage:
  <comment>php bin/console log:import /var/log/myapp.log</comment>
HELP);
    }

    /**
     * Executes the command logic.
     *
     * @param InputInterface $input The input interface to read arguments and options.
     * @param OutputInterface $output The output interface to display messages.
     *
     * @return int Returns Command::SUCCESS (0) on success or Command::FAILURE (1) on failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $logFile = $input->getArgument('file');

        try {
            $io->title('Log File Import');
            $io->note("Processing log file: $logFile");

            // Import logs using the service
            $linesProcessed = $this->logFileImporter->importLogs($logFile);

            $io->success("$linesProcessed log entries processed and imported successfully!");

            return Command::SUCCESS;
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
