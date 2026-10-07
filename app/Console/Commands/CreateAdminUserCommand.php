<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

#[Signature(<<<'SIGNATURE'
user:create-admin {email? : E-mail do usuário} {--name= : Nome de exibição} {--password= : Senha}
SIGNATURE)]
#[Description('Cria um usuário administrador ou promove um usuário existente')]
class CreateAdminUserCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = trim($this->argument('email') ?? $this->ask('E-mail'));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Informe um e-mail válido.');

            return self::FAILURE;
        }

        $existingUser = User::where('email', $email)->first();

        if ($existingUser !== null) {
            return $this->promote($existingUser);
        }

        $name = trim($this->option('name') ?? $this->ask('Nome', Str::before($email, '@')));

        if ($name === '') {
            $this->error('Informe um nome.');

            return self::FAILURE;
        }

        $password = $this->option('password') ?? $this->secret('Senha');
        $validator = Validator::make(
            ['password' => $password],
            ['password' => ['required', 'string', Password::defaults()]],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
        $user->is_admin = true;
        $user->is_active = true;
        $user->save();

        $this->info("Usuário {$email} criado como administrador.");

        return self::SUCCESS;
    }

    /**
     * Promote an existing user to administrator and ensure the account is active.
     */
    private function promote(User $user): int
    {
        if ($user->is_admin && $user->is_active) {
            $this->info("O usuário {$user->email} já é administrador.");

            return self::SUCCESS;
        }

        $user->is_admin = true;
        $user->is_active = true;
        $user->save();

        $this->info("O usuário {$user->email} agora é administrador.");

        return self::SUCCESS;
    }
}
