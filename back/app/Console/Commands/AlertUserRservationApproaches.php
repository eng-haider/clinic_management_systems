<?php

namespace App\Console\Commands;
use App\Http\Controllers\API\NotificationController;
use Illuminate\Console\Command;

class AlertUserRservationApproaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Notification:AlertUserRservationApproaches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AlertUserRservationApproaches';

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
        $Notification=new NotificationController;
        $Notification->AlertUserRservationApproaches();
    }
}
