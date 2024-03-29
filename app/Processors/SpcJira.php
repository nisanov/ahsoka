<?php

declare(strict_types=1);

namespace App\Processors;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class SpcJira extends Processor
{
    /**
     * Get the issues list via the processor.
     *
     * @param Carbon $month
     * @return array<int, array<string, string|int>>
     * @throws RequestException
     */
    public function getIssuesList(Carbon $month): array
    {
        $starts = $month->toDateString();
        $ends = $month->endOfMonth()->toDateString();

        $response = Http::withBasicAuth($this->server->pivot->username, $this->password)
            ->post($this->server->api, [
                'jql' => "Engineer = currentUser() AND category = 'Web Apps' AND project != 'SPC Development' AND ( type in (Task, Sub-task, 'Spike Story', 'QA - Scope Change') OR (type = Bug AND 'Found In' in ('PROD (AGL)', 'PROD (SPC)'))) AND status CHANGED FROM 'To Deploy (TEST)' to 'QA' DURING ($starts, $ends)",
                'startAt' => 0,
                'maxResults' => 1000,
                'fields' => [
                    'summary',
                    'customfield_10701',
                    'customfield_10206',
                ],
            ]);

        $response->throwUnlessStatus(Response::HTTP_OK);

        $issues = [];
        foreach ($response['issues'] ?? [] as $issue) {

            $points = (int) ($issue['fields']['customfield_10701']['value'] ?? 0);
            if (empty($points)) {
                $points = (int) ($issue['fields']['customfield_10206'] ?? 0);
            }

            $issues[] = [
                'ticket' => $issue['key'],
                'summary' => $issue['fields']['summary'],
                'points' => $points,
                'coverage' => null,
            ];
        }

        return $issues;
    }
}
