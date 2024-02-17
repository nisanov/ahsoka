<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Carbon;

trait InteractsWithYears
{
    /**
     * Get the current financial year.
     *
     * @return string
     */
    public function getCurrentFinancialYear(): string
    {
        $today = Carbon::today();
        if ($today->month > 6) {
            $year = $today->addYear()->year;
        } else {
            $year = $today->year;
        }

        return (string) $year;
    }

    /**
     * Get the 'starts' and 'ends' range for financial year.
     * @param string $year
     * @return array<string, Carbon>
     */
    public function getRangeForFinancialYear(string $year): array
    {
        $starts = Carbon::createFromDate($year - 1, 7, 1)->startOfDay();
        $ends = $terminus = Carbon::createFromDate($year, 6, 30)->endOfDay();
        if ($ends->isFuture()) {
            $ends = Carbon::today()->endOfMonth();
        }

        return [$starts, $ends, $terminus];
    }
}
