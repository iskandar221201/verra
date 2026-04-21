<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\SseEventModel;
use CodeIgniter\I18n\Time;

class SseCleanup extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Housekeeping';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'sse:cleanup';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Cleans up SSE events older than 5 minutes';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'sse:cleanup';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $model = new SseEventModel();

        // Calculate the threshold (5 minutes ago)
        $threshold = Time::now()->subMinutes(5)->toDateTimeString();

        // Delete records older than the threshold
        $deletedCount = $model->where('created_at <', $threshold)->delete();

        if ($deletedCount === false) {
            CLI::error('Failed to cleanup SSE events.');
            return;
        }

        CLI::write("✓ Deleted {$deletedCount} old SSE event records (older than {$threshold}).", 'green');
    }
}
