<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\Menu\Prompt;
use App\Models\Holiday;
use App\Traits\InteractsWithBrowser;
use App\Traits\InteractsWithYears;
use App\Commands\RunCommand;
use App\Models\Developer;
use Code16\CarbonBusiness\BusinessDays;
use CpChart\Data;
use CpChart\Image;
use DivisionByZeroError;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PhpSchool\CliMenu\CliMenu;
use UnexpectedValueException;

class GenerateGraphsAction extends Action
{
    use InteractsWithYears;
    use InteractsWithBrowser;

    /**
     * @var string
     */
    public const ENTIRE_DEVELOPMENT_TEAM = 'Entire Development Team';

    /**
     * @var string
     */
    public const LABEL_EXPECTED_STORY_POINTS = 'Expected Story Points';

    /**
     * @var string
     */
    public const LABEL_ACTUAL_STORY_POINTS = 'Actual Story Points';

    /**
     * @var string
     */
    public const LABEL_MEAN_STORY_POINTS = 'Mean Story Points';

    /**
     * Construct the action instance.
     *
     * @param Holiday $holidays
     * @param Developer $developers
     * @param BusinessDays $businessDays
     */
    public function __construct(
        private readonly Holiday $holidays,
        private readonly Developer $developers,
        private readonly BusinessDays $businessDays,
    ){
        //...
    }

    /**
     * Process the action.
     *
     * @param RunCommand $command
     * @param CliMenu $menu
     * @return void
     * @throws Exception
     */
    public function __invoke(RunCommand $command, CliMenu $menu): void
    {
        $selection = $menu->getSelectedItem()->getText();
        if ($selection === self::ENTIRE_DEVELOPMENT_TEAM) {
            $menu->flash("Not Implemented Yet!", $this->getPromptStyle(Prompt::ALERT))->display();
            return;
        }

        $developer = $this->developers->where(['name' => $menu->getSelectedItem()->getText()])->first();
        if (!$developer instanceof Developer) {
            throw new UnexpectedValueException("The developer is not an instance of \App\Models\Developer.");
        }

        $this->businessDays->addHolidays($this->holidays->where(
            fn (Builder $query) => $query->where('state', '=', $developer->state)->orWhereNull('state')
        )->pluck('date')->toArray());

        [$starts, $ends, $terminus] = $this->getRangeForFinancialYear($menu->askText($this->getPromptStyle(Prompt::INFO))
            ->setPromptText("Graph for what financial year?")
            ->setPlaceholderText($this->getCurrentFinancialYear())
            ->ask()
            ->fetch()
        );

        $graph = $menu->askText($this->getPromptStyle(Prompt::INFO))
            ->setPromptText("What type of graph?")
            ->setPlaceholderText('Story Points')
            ->ask()
            ->fetch();

        $command->getOutput()->writeln("Generating $graph graph for developer <info>$developer->name</info> for <comment>{$starts->format('y')}/{$ends->format('y')}</comment>");

        $months = $this->getMonths($starts, $ends, $developer);

        $meanExpected = array_filter(array_column($months, 'expected'), fn ($points) => $points !== VOID);
        $meanActual = array_filter(array_column($months, 'actual'), fn ($points) => $points !== VOID);

        try {
            $meanExpected = array_sum($meanExpected) / count($meanExpected);
        } catch (DivisionByZeroError) {
            $meanExpected = VOID;
        }

        try {
            $meanActual = array_sum($meanActual) / count($meanActual);
        } catch (DivisionByZeroError) {
            $meanActual = VOID;
        }

        foreach ($ends->addMonth()->monthsUntil($terminus) as $month /** @var Carbon $month */) {
            $months[$month->format('M')] = [
                'expected' => VOID,
                'actual' => VOID,
            ];
        }

        $data = new Data();
        $data->addPoints(array_column($months, 'expected'), self::LABEL_EXPECTED_STORY_POINTS);
        $data->addPoints(array_column($months, 'actual'), self::LABEL_ACTUAL_STORY_POINTS);
        $data->addPoints(array_fill(0, 12, $meanActual), self::LABEL_MEAN_STORY_POINTS);
        $data->setPalette(self::LABEL_EXPECTED_STORY_POINTS, ['R' => 65, 'G' => 105, 'B' => 226]);
        $data->setPalette(self::LABEL_ACTUAL_STORY_POINTS, ['R' => 244, 'G' => 158, 'B' => 12]);
        $data->setPalette(self::LABEL_MEAN_STORY_POINTS, $meanActual >= $meanExpected ? ['R' => 46, 'G' => 139, 'B' => 87] : ['R' => 220, 'G' => 20, 'B' => 60]);
        $data->setAxisName(0, 'Story Points');
        $data->addPoints(array_keys($months), 'Labels');
        $data->setSerieDescription('Labels', 'Months');
        $data->setAbscissa('Labels');

        $image = new Image(1000, 600, $data);
        $image->setFontProperties(['FontName' => config('font.path'), 'FontSize' => 13]);
        $image->drawText(450, 45, "$developer->name's Story Points Graph for {$starts->format('y')}/{$ends->format('y')}", [
            'FontName' => config('font.path'),
            'FontSize' => 20,
            'Align' => TEXT_ALIGN_MIDDLEMIDDLE,
        ]);
        $image->setGraphArea(100, 70, 900, 500);
        $image->drawFilledRectangle(100, 70, 900, 500, [
            'R' => 255,
            'G' => 255,
            'B' => 255,
            'Surrounding' => -200,
            'Alpha' => 10
        ]);
        $image->drawScale(['DrawSubTicks' => true]);
        $image->setShadow(true, ['X' => 1, 'Y' => 1, 'R' => 0, 'G' => 0, 'B' => 0, 'Alpha' => 10]);

        $data->setSerieDrawable(self::LABEL_EXPECTED_STORY_POINTS);
        $data->setSerieDrawable(self::LABEL_ACTUAL_STORY_POINTS);
        $data->setSerieDrawable(self::LABEL_MEAN_STORY_POINTS, false);

        $image->drawBarChart(['DisplayValues' => true, 'DisplayColor' => DISPLAY_AUTO, 'Rounded' => true, 'Surrounding' => 60]);

        $data->setSerieDrawable(self::LABEL_EXPECTED_STORY_POINTS, false);
        $data->setSerieDrawable(self::LABEL_ACTUAL_STORY_POINTS, false);
        $data->setSerieDrawable(self::LABEL_MEAN_STORY_POINTS);

        $image->drawLineChart(['DisplayValues' => true, 'DisplayColor' => DISPLAY_AUTO, 'Rounded' => true]);

        $data->setSerieDrawable(self::LABEL_EXPECTED_STORY_POINTS);
        $data->setSerieDrawable(self::LABEL_ACTUAL_STORY_POINTS);
        $data->setSerieDrawable(self::LABEL_MEAN_STORY_POINTS);

        $image->setShadow(false);
        $image->drawLegend(200, 550, ['Style' => LEGEND_NOBORDER, 'Mode' => LEGEND_HORIZONTAL]);

        $this->openBrowserImage($image, $command->getOutput());

        $menu->flash("Generate graphs process has complete successfully", $this->getPromptStyle(Prompt::SUCCESS))->display();
    }

    /**
     * Get the expected and actual points over each applicable month.
     *
     * @param Carbon $starts
     * @param Carbon $ends
     * @param Developer $developer
     * @return array
     */
    private function getMonths(Carbon $starts, Carbon $ends, Developer $developer): array
    {
        $months = [];
        foreach ($starts->monthsUntil($ends) as $month /** @var Carbon $month */) {
            $expected = VOID;
            $actual = $developer->issues()->where(['month' => $month])->sum('points') ?: VOID;
            if ($actual !== VOID) {
                $periodStarts = $month->copy()->startOfMonth();
                $periodEnds = $month->copy()->endOfMonth();
                if ($periodEnds->isFuture()) {
                    $periodEnds = Carbon::today()->endOfDay();
                }
                $expected = floor(( $this->businessDays->daysBetween($periodStarts, $periodEnds) ?: 1 ) / 5 * $developer->points_per_week);
            }
            $months[$month->format('M')] = compact('expected', 'actual');
        }

        return $months;
    }
}
