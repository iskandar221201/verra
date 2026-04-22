<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\SseEventModel;

class SseController extends BaseController
{
    protected SseEventModel $sseModel;

    public function __construct()
    {
        $this->sseModel = new SseEventModel();
    }

    /**
     * SSE endpoint for live updates
     */
    public function stream($channelId, $waNumber)
    {
        // Disable any output buffering from CI or PHP
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Set SSE headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Disable buffering for Nginx

        $tenantId = $this->tenant_id;
        $lastEventId = (int) $this->request->getGet('lastEventId') ?: 0;

        // Send immediate heartbeat so browser knows connection is alive
        echo ": heartbeat\n\n";
        flush();

        // Long-polling loop — reduced to 15s to free threads faster
        $startTime = time();
        $maxDuration = 15;

        while (time() - $startTime < $maxDuration) {
            // Check if connection is closed by client FIRST
            if (connection_aborted()) {
                break;
            }

            $events = $this->sseModel->getNewEvents($tenantId, $channelId, $waNumber, $lastEventId);

            if (!empty($events)) {
                foreach ($events as $event) {
                    echo "id: " . $event['id'] . "\n";
                    echo "event: " . $event['event_type'] . "\n";
                    echo "data: " . $event['payload'] . "\n\n";

                    $lastEventId = $event['id'];
                }
                flush();
            }

            // Sleep for 2 seconds before next poll (reduced DB pressure)
            sleep(2);
        }
    }
}
