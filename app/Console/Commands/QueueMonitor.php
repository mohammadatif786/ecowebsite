<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QueueMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:monitor {queues=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Return the number of jobs in the specified queue(s)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queues = explode(',', $this->argument('queues'));
        $jobCount = DB::table('jobs')->whereIn('queue', $queues)->count();
        echo $jobCount;
    }
}
