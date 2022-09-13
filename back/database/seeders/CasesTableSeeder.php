<?php

namespace Database\Seeders;

use App\Models\Cases;
use Illuminate\Database\Seeder;

class CasesTableSeeder extends Seeder
{
    private $numberOfCases = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->table(['Cases table seeder notice'], [
            ['Edit this file to change the number of Cases created'],
        ]);

        $this->command->info('Creating ' . $this->numberOfCases . ' Cases ...');
        $bar = $this->command->getOutput()->createProgressBar($this->numberOfCases);

        for ($i = 0; $i < $this->numberOfCases; ++$i) {
            Cases::factory()->create();
            $bar->advance();
        }

        $bar->finish();
        $this->command->info('');
    }
}
