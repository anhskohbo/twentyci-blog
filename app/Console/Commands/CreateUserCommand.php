<?php

namespace App\Console\Commands;

use App\Group;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $name = $this->ask('Name');
        $email = $this->ask('Email Address');
        $password = $this->secret('Password');

        tap(
            (new User())->forceFill(
                [
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'level' => Group::ADMINISTRATOR,
                ]
            )
        )->save();

        $this->info('User created successfully.');
    }
}
