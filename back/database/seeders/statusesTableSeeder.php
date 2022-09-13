<?php

namespace Database\Seeders;

use App\Models\status;
use Illuminate\Database\Seeder;

class statusesTableSeeder extends Seeder
{
    private $numberOfstatuses = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->table(['statuses table seeder notice'], [
            ['Edit this file to change the number of statuses created'],
        ]);

        $this->command->info('Creating ' . $this->numberOfstatuses . ' statuses ...');
        $bar = $this->command->getOutput()->createProgressBar($this->numberOfstatuses);

        for ($i = 0; $i < $this->numberOfstatuses; ++$i) {
            status::factory()->create();
            $bar->advance();
        }

        $bar->finish();
        $this->command->info('');
    }
}
