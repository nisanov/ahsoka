<?php

declare(strict_types=1);

namespace App\Actions;

use App\Abilities\InteractsWithYears;
use App\Commands\DeveloperGenerateGraphsCommand;
use App\Commands\RunCommand;
use App\Models\Developer;
use PhpSchool\CliMenu\CliMenu;
use UnexpectedValueException;

class DeveloperGenerateGraphsAction extends Action
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

        $command->call(DeveloperGenerateGraphsCommand::class, [
            '--developer' => $developer->id,
            '--graph' => $menu->askText()
                ->setPromptText("What type of graph?")
                ->setPlaceholderText('Story Points')
                ->ask()
                ->fetch(),
            '--year' =>  $menu->askText()
                ->setPromptText("Graph for what financial year?")
                ->setPlaceholderText($this->getCurrentFinancialYear())
                ->ask()
                ->fetch(),
        ]);

        $menu->flash("Generate graphs process has complete successfully")->display();
    }
}
