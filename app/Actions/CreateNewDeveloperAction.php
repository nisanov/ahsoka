<?php

declare(strict_types=1);

namespace App\Actions;

use App\Commands\RunCommand;
use App\Enums\Menu\Prompt;
use App\Enums\Models\Server\Type;
use App\Models\Developer;
use App\Models\Server;
use Illuminate\Database\Eloquent\Collection;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\MenuItem\LineBreakItem;
use PhpSchool\CliMenu\MenuItem\MenuMenuItem;
use PhpSchool\CliMenu\MenuItem\SelectableItem;

class CreateNewDeveloperAction extends Action
{
    /**
     * Construct the class instance.
     *
     * @param Developer $developers
     * @param Server $servers
     */
    public function __construct(
        private readonly Developer $developers,
        private readonly Server $servers,
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
        $name = $menu->askText($this->getPromptStyle(Prompt::INFO))->setPromptText("What is the developer's name?")->setPlaceholderText('Citizen Developer')->ask();
        $developer = $this->developers->create(['name' => $name->fetch()]);

        /** @var Collection<Server> $servers */
        $servers = $this->servers->where(['type' => Type::JIRA, 'active' => true])->get();
        foreach ($servers as $server) {
            $username = $menu->askText($this->getPromptStyle(Prompt::INFO))->setPromptText("What is the $server->name username?")->setPlaceholderText('citizen.developer')->ask();
            $developer->servers()->attach($server->id, ['username' => $username->fetch()]);
        }

        $menu->flash("Created a new developer named: $developer->name", $this->getPromptStyle(Prompt::SUCCESS))->display();
        $menu->setItems([
            new LineBreakItem(),
            new SelectableItem($developer->name, static fn (CliMenu $menu) => app(DeveloperManagementAction::class)($command, $menu)),
            ...array_slice($menu->getItems(), 1),
        ]);
        $menu->redraw();

        $parent = $menu->getParent();
        if ($parent instanceof CliMenu) {
            $item = $parent->getItemByIndex(1); // Synchronize Issues
            if ($item instanceof MenuMenuItem) {
                $menu = $item->getSubMenu();
                $menu->setItems([
                    new LineBreakItem(),
                    new SelectableItem($developer->name, static fn (CliMenu $menu) => app(SynchronizeIssuesAction::class)($command, $menu)),
                    ...array_slice($menu->getItems(), 1),
                ]);
            }
            $item = $parent->getItemByIndex(2); // Generate Graphs
            if ($item instanceof MenuMenuItem) {
                $menu = $item->getSubMenu();
                $menu->setItems([
                    new LineBreakItem(),
                    new SelectableItem($developer->name, static fn (CliMenu $menu) => app(GenerateGraphsAction::class)($command, $menu)),
                    ...array_slice($menu->getItems(), 1),
                ]);
            }
        }
    }
}
