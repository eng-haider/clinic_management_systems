<?php

namespace Database\Seeders;

use App\Models\SessionsCases;
use Illuminate\Database\Seeder;

class SessionsCasesTableSeeder extends Seeder
{
    private $numberOfSessionsCases = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->table(['SessionsCases table seeder notice'], [
            ['Edit this file to change the number of SessionsCases created'],
        ]);

        $this->command->info('Creating ' . $this->numberOfSessionsCases . ' SessionsCases ...');
        $bar = $this->command->getOutput()->createProgressBar($this->numberOfSessionsCases);

        for ($i = 0; $i < $this->numberOfSessionsCases; ++$i) {
            SessionsCases::factory()->create();
            $bar->advance();
        }

        $bar->finish();
        $this->command->info('');
    }
}
