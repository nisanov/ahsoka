<?php

declare(strict_types=1);

namespace App\Actions;

use App\Commands\RunCommand;
use PhpSchool\CliMenu\CliMenu;

class DeveloperManagementAction extends Action
{
    /**
     * Process the action.
     *
     * @param RunCommand $command
     * @param CliMenu $menu
     * @return void
     */
    public function __invoke(RunCommand $command, CliMenu $menu): void
    {
        $menu->flash("Not Implemented for: {$menu->getSelectedItem()->getText()}")->display();
    }
}
