<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Server;
use App\Traits\InteractsWithYears;
use App\Commands\RunCommand;
use App\Enums\Models\Holiday\State;
use App\Enums\Models\Holiday\Type;
use App\Enums\Models\Server\Type as ServerType;
use App\Models\Holiday;
use Carbon\CarbonPeriod;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use PhpSchool\CliMenu\CliMenu;
use Symfony\Component\HttpFoundation\Response;
use function count;

class SynchronizeHolidaysAction extends Action
{
    use InteractsWithYears;

    /**
     * Construct the action instance.
     *
     * @return void
     */
    public function __construct(
        private readonly Server $servers,
        private readonly Holiday $holidays,
    ) {
        //...
    }

    /**
     * Invoke the action.
     *
     * @param RunCommand $command
     * @param CliMenu $menu
     * @return void
     */
    public function __invoke(RunCommand $command, CliMenu $menu): void
    {
        try {

            $server = $this->servers->where('type', ServerType::HOLIDAY)->first();
            if ($server->token === null) {
                $server->token = $menu->askPassword()
                    ->setPromptText("What is the API key?")
                    ->ask()
                    ->fetch();

                $response = $menu->askText()
                    ->setPromptText("Would you like to store the token (only slightly secure)?")
                    ->setPlaceholderText('Yes')
                    ->ask()
                    ->fetch();
                if ($response === 'Yes') {
                    $server->save();
                }
            }

            $command->getOutput()->writeln("Synchronizing holidays...");

            foreach (CarbonPeriod::create(Carbon::today()->subYear(), '1 year', 3) as $period) {

                $year = $period->format('Y');
                $holidays = $this->scan($server->api, $server->token, $year);

                $progress = $command->getOutput()->createProgressBar(count($holidays));
                $progress->setFormat("Querying <info>$server->api</info> server for <comment>$year</comment> %current%/%max% [%bar%] %percent:3s%%");
                $progress->start();

                foreach ($holidays as $holiday) {

                    ['token' => $token, 'state' => $state, 'date' => $date] = $holiday;

                    $this->holidays->updateOrCreate(compact('token', 'state', 'date'), $holiday);
                }

                $progress->finish();

                $command->getOutput()->newLine();

                usleep(500000); // half a second rate limit
            }

            $command->getOutput()->writeln("Synchronized holidays <comment>successfully</comment>");

            $menu->flash("Synchronization process has complete successfully")->display();

        } catch (RequestException $exception) {

            $menu->flash("Synchronization failed: {$exception->response->reason()}")->display();
        }
    }

    /**
     * Perform the year specific public holiday scan.
     *
     * @param string $api
     * @param string $key
     * @param string $year
     * @return array<int, array<string, string>>
     * @throws RequestException
     */
    public function scan(string $api, string $key, string $year): array
    {
        $response = Http::get("$api?api_key=$key&country=AU&year=$year");
        $response->throwUnlessStatus(Response::HTTP_OK);

        $holidays = [];
        foreach ($response['response']['holidays'] ?? [] as $holiday) {

            // check that the primary type matches those that are applicable
            $type = Type::tryFrom($holiday['primary_type']);
            if ($type === null) {
                continue;
            }

            // the state is set to null when this public holiday is nationwide
            if (!is_array($holiday['states'])) {
                $holiday['states'] = [['name' => null]];
            }

            foreach ($holiday['states'] as $state) {
                // when the state is not set to a nationwide public holiday
                if ($state['name'] !== null) {
                    // check that the state matches those that are applicable
                    $state['name'] = State::tryFrom($state['name']);
                    if ($state['name'] === null) {
                        continue;
                    }
                }

                $holidays[] = [
                    'name' => $holiday['name'],
                    'description' => $holiday['description'],
                    'token' => $holiday['urlid'],
                    'type' => $type,
                    'state' => $state['name'],
                    'date' => Carbon::parse($holiday['date']['iso']),
                ];
            }
        }

        return $holidays;
    }
}
