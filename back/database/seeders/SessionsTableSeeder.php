<?php

namespace Database\Seeders;

use App\Models\Sessions;
use Illuminate\Database\Seeder;

class SessionsTableSeeder extends Seeder
{
    private $numberOfSessions = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->table(['Sessions table seeder notice'], [
            ['Edit this file to change the number of Sessions created'],
        ]);

        $this->command->info('Creating ' . $this->numberOfSessions . ' Sessions ...');
        $bar = $this->command->getOutput()->createProgressBar($this->numberOfSessions);

        for ($i = 0; $i < $this->numberOfSessions; ++$i) {
            Sessions::factory()->create();
            $bar->advance();
        }

        $bar->finish();
        $this->command->info('');
    }
}
