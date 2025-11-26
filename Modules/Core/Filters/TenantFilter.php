<?php

namespace Modules\Core\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;

class TenantFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $slug = $arguments[0] ?? null;
        // Fallback ambil dari header atau segment URL: /tenant/{slug}/...
        if (! $slug) {
            $slug = $request->getHeaderLine('X-Tenant-Key') ?: null;
        }
        if (! $slug) {
            $segments = $request->getUri()->getSegments();
            if (isset($segments[0]) && $segments[0] === 'tenant' && isset($segments[1])) {
                $slug = $segments[1];
            }
        }
        if (! $slug) {
            return redirect()->to('/dashboard');
        }

        try {
            Services::modulesCoreTenant(false)->resolveAndConnectFromSlug($slug);
        } catch (\Throwable $e) {
            return redirect()->to('/dashboard');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}


