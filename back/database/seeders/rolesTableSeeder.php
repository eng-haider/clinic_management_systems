<?php

namespace Database\Seeders;

use App\Models\roles;
use Illuminate\Database\Seeder;

class rolesTableSeeder extends Seeder
{
    private $numberOfroles = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->table(['roles table seeder notice'], [
            ['Edit this file to change the number of roles created'],
        ]);

        $this->command->info('Creating ' . $this->numberOfroles . ' roles ...');
        $bar = $this->command->getOutput()->createProgressBar($this->numberOfroles);

        for ($i = 0; $i < $this->numberOfroles; ++$i) {
            roles::factory()->create();
            $bar->advance();
        }

        $bar->finish();
        $this->command->info('');
    }
}
