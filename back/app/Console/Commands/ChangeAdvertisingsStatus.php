<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use App\Http\Controllers\API\AdvertisingController;


class ChangeAdvertisingsStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Advertisings:vaild';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        $Advertising=new AdvertisingController;
        $Advertising->ChangeAdvertisingStatus();
    }
}
