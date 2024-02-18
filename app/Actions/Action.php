<?php

declare(strict_types=1);

namespace App\Actions;

use App\Commands\RunCommand;
use App\Enums\Menu\Prompt;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\MenuStyle;

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

    /**
     * Get the menu prompt window style.
     *
     * @param Prompt $prompt
     * @return MenuStyle
     */
    final public function getPromptStyle(Prompt $prompt): MenuStyle
    {
        return (new MenuStyle())
            ->setBg($prompt->background())
            ->setFg($prompt->foreground())
        ;
    }
}
