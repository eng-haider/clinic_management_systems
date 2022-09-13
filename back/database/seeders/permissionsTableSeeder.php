<?php

namespace Database\Seeders;

use App\Models\permissions;
use Illuminate\Database\Seeder;

class permissionsTableSeeder extends Seeder
{
    private $numberOfpermissions = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->table(['permissions table seeder notice'], [
            ['Edit this file to change the number of permissions created'],
        ]);

        $this->command->info('Creating ' . $this->numberOfpermissions . ' permissions ...');
        $bar = $this->command->getOutput()->createProgressBar($this->numberOfpermissions);

        for ($i = 0; $i < $this->numberOfpermissions; ++$i) {
            permissions::factory()->create();
            $bar->advance();
        }

        $bar->finish();
        $this->command->info('');
    }
}
