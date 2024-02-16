<?php

declare(strict_types=1);

namespace App\Processors;

use App\Models\Developer;
use App\Models\Issue;
use App\Models\Server;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;

abstract class Processor
{
    /**
     * The server used by the processor.
     *
     * @var Server
     */
    protected Server $server;

    /**
     * The developer used by the processor.
     *
     * @var Developer
     */
    protected Developer $developer;

    /**
     * The server password/token.
     *
     * @var string
     */
    protected string $password;

    /**
     * Get the issues list via the processor.
     *
     * @param Carbon $month
     * @return array<int, array<string, string|int>>
     * @throws RequestException
     */
    abstract public function getIssuesList(Carbon $month): array;

    /**
     * Construct the processor instance.
     *
     * @param Issue $issues
     * @return void
     */
    public function __construct(
        protected readonly Issue $issues,
    ) {
        //...
    }

    /**
     * Set the server for the processor instance.
     *
     * @param Server $server
     * @return self
     */
    final public function setServer(Server $server): self
    {
        $this->server = $server;

        return $this;
    }

    /**
     * Set the developer for the processor instance.
     *
     * @param Developer $developer
     * @return self
     */
    final public function setDeveloper(Developer $developer): self
    {
        $this->developer = $developer;

        return $this;
    }

    /**
     * Set the server password/token for the processor instance.
     *
     * @param string $password
     * @return self
     */
    final public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Store the issue in the system.
     *
     * @param array $issue The issue data.
     * @param Carbon $month
     * @return void
     */
    final public function storeIssue(array $issue, Carbon $month): void
    {
        $this->developer->issues()->updateOrCreate([
            'server_id' => $this->server->getKey(),
            'ticket' => $issue['ticket'],
        ], [
            'month' => $month->startOfMonth(),
            'summary' => $issue['summary'],
            'points' => $issue['points'],
            'coverage' => $issue['coverage'],
        ]);
    }
}
