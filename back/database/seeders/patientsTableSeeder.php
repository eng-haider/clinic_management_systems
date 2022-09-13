<?php

namespace Database\Seeders;

use App\Models\patients;
use Illuminate\Database\Seeder;

class patientsTableSeeder extends Seeder
{
    private $numberOfpatients = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->table(['patients table seeder notice'], [
            ['Edit this file to change the number of patients created'],
        ]);

        $this->command->info('Creating ' . $this->numberOfpatients . ' patients ...');
        $bar = $this->command->getOutput()->createProgressBar($this->numberOfpatients);

        for ($i = 0; $i < $this->numberOfpatients; ++$i) {
            patients::factory()->create();
            $bar->advance();
        }

        $bar->finish();
        $this->command->info('');
    }
}
