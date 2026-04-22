<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class TenantFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!auth()->loggedIn()) {
            return;
        }

        $user = auth()->user();

        // Superadmin doesn't need tenant_id
        if ($user->inGroup('superadmin')) {
            return;
        }

        // If user is not superadmin, they MUST have a tenant_id
        if (empty($user->tenant_id)) {
            auth()->logout();
            return redirect()->to(config('Auth')->loginRedirect())->with('error', 'Akun Anda belum terhubung dengan tenant manapun. Silakan hubungi Super Admin.');
        }

        // Define a global constant or setting for easy access
        if (!defined('TENANT_ID')) {
            define('TENANT_ID', (int) $user->tenant_id);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
