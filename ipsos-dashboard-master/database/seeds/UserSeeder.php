<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = DB::table('users')->where('email', 'admin@example.com')->first();
        if (!$admin) {
            factory(App\User::class, 1)->create()->each(function($u) {
                // update admin
                $u->update([
                    'name' => 'Administrator',
                    'email' => 'admin@example.com',
                    'role' => App\User::ROLE_ADMIN,
                    'avatar' => 'images/user-13.jpg',
                    'logo' => 'images/adminipsos.png',
                ]);
            });
        }
        $toyota = DB::table('users')->where('email', 'toyota@example.com')->first();
        if (!$toyota) {
            factory(App\User::class, 1)->create()->each(function($u) {
                // update client
                $u->update([
                    'name' => 'Client Toyota',
                    'email' => 'toyota@example.com',
                    'role' => App\User::ROLE_CLIENT,
                    'avatar' => 'images/avatar5.png',
                    'logo' => 'images/toyota.png',
                ]);
            });
        }
    }
}
