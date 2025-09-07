<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    protected function getFormSchema(): array
    {
        return [
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getRememberFormComponent(),
        ];
    }

    protected function getLayoutData(): array
    {
        return [
            'hasTopbar' => false,
        ];
    }
}
