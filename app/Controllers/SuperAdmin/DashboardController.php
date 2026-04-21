<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\TenantModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $tenantModel = new TenantModel();

        $data = [
            'total_tenants' => $tenantModel->countAllResults(),
        ];

        return view('_layouts/superadmin', [
            'title' => 'Dashboard',
            'content' => view('superadmin/dashboard/index', $data),
        ]);
    }
}
