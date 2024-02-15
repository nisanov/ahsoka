<?php

declare(strict_types=1);

namespace App\Actions;

use App\Abilities\InteractsWithYears;
use App\Commands\DeveloperSynchronizeIssuesCommand;
use App\Commands\RunCommand;
use App\Models\Developer;
use Illuminate\Http\Client\RequestException;
use PhpSchool\CliMenu\CliMenu;
use UnexpectedValueException;

class DeveloperSynchronizeIssuesAction extends Action
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

        try {
            $command->call(DeveloperSynchronizeIssuesCommand::class, [
                '--developer' => $developer->id,
                '--year' =>  $menu->askText()
                    ->setPromptText("Synchronize for what financial year?")
                    ->setPlaceholderText($this->getCurrentFinancialYear())
                    ->ask()
                    ->fetch(),
            ]);

            $menu->flash("Synchronization process has complete successfully")->display();
        } catch (RequestException $exception) {
            $menu->flash("Synchronization failed: {$exception->response->reason()}")->display();
        }
    }
}
