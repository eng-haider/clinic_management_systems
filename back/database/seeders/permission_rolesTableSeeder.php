<?php

namespace Database\Seeders;

use App\Models\permission_role;
use Illuminate\Database\Seeder;

class permission_rolesTableSeeder extends Seeder
{
    private $numberOfpermission_roles = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->table(['permission_roles table seeder notice'], [
            ['Edit this file to change the number of permission_roles created'],
        ]);

        $this->command->info('Creating ' . $this->numberOfpermission_roles . ' permission_roles ...');
        $bar = $this->command->getOutput()->createProgressBar($this->numberOfpermission_roles);

        for ($i = 0; $i < $this->numberOfpermission_roles; ++$i) {
            permission_role::factory()->create();
            $bar->advance();
        }

        $bar->finish();
        $this->command->info('');
    }
}
