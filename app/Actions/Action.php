<?php

declare(strict_types=1);

namespace App\Actions;

use App\Commands\RunCommand;
use PhpSchool\CliMenu\CliMenu;

abstract class Action
{
    /**
     * Process the action.
     *
     * @param RunCommand $command
     * @param CliMenu $menu
     * @return void
     */
    abstract public function __invoke(RunCommand $command, CliMenu $menu): void;
}
