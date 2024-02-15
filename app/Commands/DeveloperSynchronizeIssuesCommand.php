<?php

declare(strict_types=1);

namespace App\Commands;

use App\Abilities\InteractsWithYears;
use App\Models\Developer;
use App\Processors\Processor;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Symfony\Component\Console\Command\Command;
use UnexpectedValueException;
use function count;

class DeveloperSynchronizeIssuesCommand extends Ahsoka
{
    use InteractsWithYears;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'developer:synchronize {--developer= : The developer record identifier} {--year= : The financial year to synchronize}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Synchronize developer issues';

    /**
     * Construct the command instance.
     *
     * @return void
     */
    public function __construct(
        private readonly Developer $developers,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws RequestException
     */
    public function handle(): int
    {
        $developer = $this->developers->find($this->option('developer'));
        if (!$developer instanceof Developer) {
            throw new UnexpectedValueException("The developer is not an instance of \App\Models\Developer.");
        }

        [$starts, $ends] = $this->getRangeForFinancialYear($this->option('year'));

        $this->output->writeln("Synchronizing developer <info>$developer->name</info> for <comment>{$starts->format('M-Y')}/{$ends->format('M-Y')}</comment>");

        foreach ($developer->servers as $server) {

            if (!$server->active) {
                continue;
            }

            $this->output->writeln("\nProcessing <info>$server->name</info> server");

            $password = $this->secret("Enter your password/token for $server->name");

            $processor = app($server->processor); /** @var Processor $processor */
            $processor->setServer($server);
            $processor->setDeveloper($developer);
            $processor->setPassword($password);

            foreach ($starts->monthsUntil($ends) as $month /** @var Carbon $month */) {
                $issues = $processor->getIssuesList($month);
                $maximum = count($issues);
                if ($maximum) {
                    $progress = $this->output->createProgressBar($maximum);
                    $progress->setFormat("Querying <info>$server->name</info> server for <comment>{$month->format('M-Y')}</comment> %current%/%max% [%bar%] %percent:3s%%");
                    $progress->start();
                    foreach ($issues as $issue) {
                        $processor->storeIssue($issue, $month);
                        $progress->advance();
                    }
                    $progress->finish();
                    $this->output->newLine();
                } else {
                    $this->output->writeln("Querying <info>$server->name</info> server for <comment>{$month->format('M-Y')}</comment> <fg=red;options=bold>no issues returned</>");
                }
            }

            $this->output->writeln("\nProcessed <info>$server->name</info> server <comment>successfully</comment>");
        }

        return Command::SUCCESS;
    }
}
