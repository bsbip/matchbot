<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use RunTimeException;
use Cron\CronExpression;
use App\Jobs\InitiateMatch;
use Illuminate\Console\Command;
use App\ScheduledEventInitiation;
use Illuminate\Console\Scheduling\ManagesFrequencies;

class ScheduleEventInitiations extends Command
{
    use ManagesFrequencies;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:event-initiations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule event initiations for today';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ScheduledEventInitiation::all()
            ->each(function ($scheduledEventInitiation) {
                $this->info("Processing cron expression {$scheduledEventInitiation->cron_expression}");

                $this->cron($scheduledEventInitiation->cron_expression);
                $cronExpression = CronExpression::factory($this->expression);
                $nextRunDate = today();

                do {
                    try {
                        $nextRunDate = Carbon::instance($cronExpression->getNextRunDate($nextRunDate));
                    } catch (RuntimeException $e) {
                        $this->error($e);
                        break;
                    }

                    // Exclude last run
                    if (!$nextRunDate->isToday()) {
                        break;
                    }

                    InitiateMatch::dispatch([
                        'channel_id' => $scheduledEventInitiation->channel_id,
                        'text' => $scheduledEventInitiation->expire_at,
                    ])
                        ->delay($nextRunDate);
                } while ($nextRunDate->lessThan(now()->endOfDay()));
            });

        $this->info('Completed');
    }
}
