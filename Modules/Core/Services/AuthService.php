<?php

namespace Modules\Core\Services;

use CodeIgniter\I18n\Time;

class AuthService
{
    public function login(array $user): void
    {
        // Update last_login in database
        $db = \Config\Database::connect();
        $db->table('users')
            ->where('id', (int) ($user['id'] ?? 0))
            ->update([
                'last_login' => Time::now()->toDateTimeString(),
                'updated_at' => Time::now()->toDateTimeString()
            ]);
        
        // Get updated user data including avatar
        $updatedUser = $db->table('users')->where('id', (int) ($user['id'] ?? 0))->get()->getRowArray();
        
        session()->set('auth_user', [
            'id' => $user['id'] ?? null,
            'name' => $user['name'] ?? null,
            'email' => $user['email'] ?? null,
            'role' => $user['role'] ?? null,
            'avatar' => $updatedUser['avatar'] ?? null,
            'tenant_id' => $updatedUser['tenant_id'] ?? null,
        ]);
        session()->set('last_login_at', Time::now()->toDateTimeString());
    }

    public function logout(): void
    {
        session()->remove(['auth_user', 'last_login_at']);
        session()->destroy();
    }

    public function check(): bool
    {
        return session()->get('auth_user') !== null;
    }
}


