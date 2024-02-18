<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\Menu\Prompt;
use App\Enums\Models\Server\Type;
use App\Models\Server;
use App\Traits\InteractsWithYears;
use App\Commands\RunCommand;
use App\Models\Developer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use PhpSchool\CliMenu\CliMenu;
use UnexpectedValueException;

class SynchronizeIssuesAction extends Action
{
    use InteractsWithYears;

    /**
     * Construct the action instance.
     *
     * @param Developer $developers
     * @return void
     */
    public function __construct(
        private readonly Developer $developers,
    ){
        //...
    }

    /**
     * Process the action.
     *
     * @param RunCommand $command
     * @param CliMenu $menu
     * @return void
     */
    public function __invoke(RunCommand $command, CliMenu $menu): void
    {
        $developer = $this->developers->where(['name' => $menu->getSelectedItem()->getText()])->first();
        if (!$developer instanceof Developer) {
            throw new UnexpectedValueException("The developer is not an instance of \App\Models\Developer.");
        }

        [$starts, $ends] = $this->getRangeForFinancialYear($menu->askText($this->getPromptStyle(Prompt::INFO))
            ->setPromptText("Synchronize for what financial year?")
            ->setPlaceholderText($this->getCurrentFinancialYear())
            ->ask()
            ->fetch()
        );

        try {

            /** @var Collection<Server> $servers */
            $servers = $developer->servers()->where(['type' => Type::JIRA, 'active' => true])->get();
            foreach ($servers as $server) {

                $this->configureServerPassword($server, $menu);

                $processor = $server->processor;
                $processor->setServer($server);
                $processor->setDeveloper($developer);
                $processor->setPassword($server->pivot->password);

                $command->getOutput()->writeln("Synchronizing developer <info>$developer->name</info> on <info>$server->name</info> for <comment>{$starts->format('M-Y')}/{$ends->format('M-Y')}</comment>");

                foreach ($starts->monthsUntil($ends) as $month /** @var Carbon $month */) {
                    $issues = $processor->getIssuesList($month);
                    $maximum = count($issues);
                    if ($maximum) {
                        $progress = $command->getOutput()->createProgressBar($maximum);
                        $progress->setFormat("Querying <info>$server->name</info> server for <comment>{$month->format('M-Y')}</comment> %current%/%max% [%bar%] %percent:3s%%");
                        $progress->start();
                        foreach ($issues as $issue) {
                            $processor->storeIssue($issue, $month);
                            $progress->advance();
                        }
                        $progress->finish();
                        $command->getOutput()->newLine();
                    } else {
                        $command->getOutput()->writeln("Querying <info>$server->name</info> server for <comment>{$month->format('M-Y')}</comment> <fg=red;options=bold>no issues returned</>");
                    }
                }

                $command->getOutput()->writeln("Processed <info>$server->name</info> server <comment>successfully</comment>");
            }

            $menu->flash("Synchronization process has completed successfully", $this->getPromptStyle(Prompt::SUCCESS))->display();

        } catch (RequestException $exception) {

            $menu->flash("Synchronization failed: {$exception->response->reason()}", $this->getPromptStyle(Prompt::ALERT))->display();
        }
    }

    /**
     * Configure the password for the given server.
     *
     * @param Server $server The server for which to configure the password.
     * @param CliMenu $menu The CLI menu object used for user interaction.
     * @return void
     */
    private function configureServerPassword(Server $server, CliMenu $menu): void
    {
        if ($server->pivot->password === null) {
            $server->pivot->password = $menu->askPassword($this->getPromptStyle(Prompt::INFO))
                ->setPromptText("What is the password/token for $server->name?")
                ->ask()
                ->fetch();

            $response = $menu->askText($this->getPromptStyle(Prompt::INFO))
                ->setPromptText("Would you like to store the password/token for $server->name (only slightly secure)?")
                ->setPlaceholderText('Yes')
                ->ask()
                ->fetch();
            if ($response === 'Yes') {
                $server->pivot->save();
            }
        }
    }
}
