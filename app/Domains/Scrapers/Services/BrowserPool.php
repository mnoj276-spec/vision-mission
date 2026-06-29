<?php

namespace App\Domains\Scrapers\Services;

class BrowserPool
{
    protected array $activeSessions = [];
    protected int $maxConcurrent = 3;

    /**
     * Request a browser session key from the pool.
     *
     * @param string $sourceId
     * @return string Session ID
     */
    public function acquireSession(string $sourceId): string
    {
        // Simple pool keying. In a production environment, this manages real Chrome Debugging Protocol WebSocket connections.
        $sessionId = uniqid('browser_session_', true);
        $this->activeSessions[$sessionId] = [
            'source_id' => $sourceId,
            'acquired_at' => microtime(true),
        ];

        return $sessionId;
    }

    /**
     * Release a browser session back to the pool.
     *
     * @param string $sessionId
     * @return void
     */
    public function releaseSession(string $sessionId): void
    {
        unset($this->activeSessions[$sessionId]);
    }

    /**
     * Get active session count.
     *
     * @return int
     */
    public function getActiveSessionCount(): int
    {
        return count($this->activeSessions);
    }
}
