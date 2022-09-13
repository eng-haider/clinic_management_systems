<?php

namespace Database\Seeders;

use App\Models\Users;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    private $numberOfUsers = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->table(['Users table seeder notice'], [
            ['Edit this file to change the number of Users created'],
        ]);

        $this->command->info('Creating ' . $this->numberOfUsers . ' Users ...');
        $bar = $this->command->getOutput()->createProgressBar($this->numberOfUsers);

        for ($i = 0; $i < $this->numberOfUsers; ++$i) {
            Users::factory()->create();
            $bar->advance();
        }

        $bar->finish();
        $this->command->info('');
    }
}
