<?php

namespace Database\Seeders;

use App\Models\doctors;
use Illuminate\Database\Seeder;

class doctorsTableSeeder extends Seeder
{
    private $numberOfdoctors = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->table(['doctors table seeder notice'], [
            ['Edit this file to change the number of doctors created'],
        ]);

        $this->command->info('Creating ' . $this->numberOfdoctors . ' doctors ...');
        $bar = $this->command->getOutput()->createProgressBar($this->numberOfdoctors);

        for ($i = 0; $i < $this->numberOfdoctors; ++$i) {
            doctors::factory()->create();
            $bar->advance();
        }

        $bar->finish();
        $this->command->info('');
    }
}
