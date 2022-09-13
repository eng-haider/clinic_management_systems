<?php

namespace Database\Seeders;

use App\Models\bills;
use Illuminate\Database\Seeder;

class billsTableSeeder extends Seeder
{
    private $numberOfbills = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->table(['bills table seeder notice'], [
            ['Edit this file to change the number of bills created'],
        ]);

        $this->command->info('Creating ' . $this->numberOfbills . ' bills ...');
        $bar = $this->command->getOutput()->createProgressBar($this->numberOfbills);

        for ($i = 0; $i < $this->numberOfbills; ++$i) {
            bills::factory()->create();
            $bar->advance();
        }

        $bar->finish();
        $this->command->info('');
    }
}
