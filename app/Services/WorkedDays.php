<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Developer;
use App\Models\Holiday;
use App\Models\Leave;
use Code16\CarbonBusiness\BusinessDays;
use Illuminate\Database\Eloquent\Builder;

class WorkedDays extends BusinessDays
{
    /**
     * @var Developer
     */
    private Developer $developer;

    /**
     * Construct the service class.
     */
    public function __construct(
        private readonly Holiday $repository,
    ) {
    }

    /**
     * Set the developer for the calendar.
     *
     * @param Developer $developer The developer to set for the calendar.
     * @return self Returns an instance of the current object.
     */
    public function setDeveloper(Developer $developer): self
    {
        $this->developer = $developer;

        $this->setHolidays();
        $this->setLeaves();

        return $this;
    }

    /**
     * Sets the holidays for the developer.
     *
     * This method retrieves the holidays from the "holidays" property, filters them based on the developer's state,
     * and sets the filtered holidays as an array on the developer's "holidays" property.
     * The "holidays" property is cleared before adding the new holidays.
     *
     * @return void
     */
    private function setHolidays(): void
    {
        $this->holidays = []; // clear the parent holiday array

        $this->addHolidays($this->repository->where(
            fn (Builder $query) => $query->where('state', '=', $this->developer->state)->orWhereNull('state')
        )->pluck('date')->toArray());
    }

    /**
     * Sets the leaves for the developer.
     *
     * This method retrieves the leaves from the "leaves" relationship on the developer model, iterates over each leave,
     * and adds the corresponding closed period to the "closedDays" property by calling the "addClosedPeriod" method.
     * The "closedDays" property is cleared before adding the new closed periods.
     *
     * @return void
     */
    public function setLeaves(): void
    {
        $this->closedDays = []; // clear the parent closed day array

        $leaves = $this->developer->leaves()->get();
        foreach ($leaves as $leave) { /** @var Leave $leave */
            $this->addClosedPeriod($leave->starts_on, $leave->ends_on);
        }
    }
}
