<?php

declare(strict_types=1);

namespace App\Commands;

use App\Actions\DeveloperCreateAction;
use App\Actions\DeveloperGenerateGraphsAction;
use App\Actions\DeveloperManagementAction;
use App\Actions\DeveloperSynchronizeIssuesAction;
use App\Models\Developer;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\MenuItem\MenuItemInterface;
use PhpSchool\CliMenu\MenuItem\SelectableItem;
use Symfony\Component\Console\Command\Command;

class RunCommand extends Ahsoka
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'run';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Run the application';

    /**
     * @param Developer $developers
     */
    public function __construct(
        private readonly Developer $developers,
    ){
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->menu('Ahsoka Main Menu')
            ->addSubMenu('Synchronize Issues', fn (CliMenuBuilder $builder) => $this->getSynchronizeIssuesMenu($builder))
            ->addSubMenu('Generate Graphs', fn (CliMenuBuilder $builder) => $this->getGenerateGraphsMenu($builder))
            ->addSubMenu('Manage Developers', fn (CliMenuBuilder $builder) => $this->getManageDeveloperMenu($builder))
            ->addSubMenu('System Maintenance', fn (CliMenuBuilder $builder) => $builder
                ->setTitle('System Maintenance')
                ->addLineBreak()
                ->addItem('Update Application', fn () => $this->call(SystemUpdateCommand::class))
                ->addItem('Create Database', fn () => $this->call(SystemInstallCommand::class))
            )
            ->open();

        $this->info("{$this->getApplicationName()} ♥ see you next time ♥");

        return Command::SUCCESS;
    }

    /**
     * Get the action callable.
     *
     * @param string $action
     * @return callable
     */
    public function getAction(string $action): callable
    {
        return fn (CliMenu $menu) => app($action)($this, $menu);
    }

    /**
     * Get the 'synchronize issues' menu.
     *
     * @param CliMenuBuilder $builder
     * @return CliMenuBuilder
     */
    public function getSynchronizeIssuesMenu(CliMenuBuilder $builder): CliMenuBuilder
    {
        $builder->setTitle('Synchronize Issues For');
        $builder->addLineBreak();

        foreach ($this->getDeveloperSelectableItemActions($this->getAction(DeveloperSynchronizeIssuesAction::class)) as $action) {
            $builder->addMenuItem($action);
        }

        return $builder;
    }

    /**
     * Get the 'synchronize issues' menu.
     *
     * @param CliMenuBuilder $builder
     * @return CliMenuBuilder
     */
    public function getGenerateGraphsMenu(CliMenuBuilder $builder): CliMenuBuilder
    {
        $builder->setTitle('Generate Graphs For');
        $builder->addLineBreak();

        foreach ($this->getDeveloperSelectableItemActions($this->getAction(DeveloperGenerateGraphsAction::class)) as $action) {
            $builder->addMenuItem($action);
        }

        $builder->addMenuItem(new SelectableItem('Entire Development Team', fn (CliMenu $menu) => $menu->flash("Not Implemented Yet")->display()));

        return $builder;
    }

    /**
     * Get the 'developer management' menu.
     *
     * @param CliMenuBuilder $builder
     * @return CliMenuBuilder
     */
    public function getManageDeveloperMenu(CliMenuBuilder $builder): CliMenuBuilder
    {
        $builder->setTitle('Developer Management For');
        $builder->addLineBreak();

        foreach ($this->getDeveloperSelectableItemActions($this->getAction(DeveloperManagementAction::class)) as $action) {
            $builder->addMenuItem($action);
        }

        $builder->addMenuItem(new SelectableItem('Create New Developer', $this->getAction(DeveloperCreateAction::class)));

        return $builder;
    }

    /**
     * Get the developer selectable item actions.
     *
     * @param callable $action
     * @return array<MenuItemInterface>
     */
    public function getDeveloperSelectableItemActions(callable $action): array
    {
        $actions = [];
        foreach ($this->developers->orderBy('name')->pluck('name') as $developer) {
            $actions[] = new SelectableItem($developer, $action);
        }

        return $actions;
    }
}
