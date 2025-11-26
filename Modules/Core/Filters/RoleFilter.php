<?php

namespace Modules\Core\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $requiredRoles = is_array($arguments) ? $arguments : [];
        $user = session()->get('auth_user');
        if (! $user) {
            return redirect()->to('/auth/login');
        }

        $role = (string) ($user['role'] ?? '');
        if ($requiredRoles && ! in_array($role, $requiredRoles, true)) {
            return redirect()->back()->with('error', 'Akses ditolak');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}


