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
        // Set SSE headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Disable buffering for Nginx

        $tenantId = $this->tenant_id;
        $lastEventId = (int) $this->request->getGet('lastEventId') ?: 0;

        // Long-polling loop
        // Limit execution time to avoid php timeout (e.g. 30 seconds)
        $startTime = time();
        $maxDuration = 25;

        while (time() - $startTime < $maxDuration) {
            $events = $this->sseModel->getNewEvents($tenantId, $channelId, $waNumber, $lastEventId);

            if (!empty($events)) {
                foreach ($events as $event) {
                    echo "id: " . $event['id'] . "\n";
                    echo "event: " . $event['event_type'] . "\n";
                    echo "data: " . $event['payload'] . "\n\n";

                    $lastEventId = $event['id'];
                }

                // Flush output buffer to client
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }

            // Sleep for 1 second before next poll
            sleep(1);

            // Check if connection is closed by client
            if (connection_aborted()) {
                break;
            }
        }
    }
}
