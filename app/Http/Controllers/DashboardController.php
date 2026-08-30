<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'title' => 'Dashboard',
            'userCount' => User::count(),
            'roleCount' => Role::count(),
            'auditCount' => Activity::count(),
            'dbName' => DB::connection()->getDatabaseName(),
            'licenseStatus' => \App\Services\LicenseService::status(),
            'licenseDaysLeft' => \App\Services\LicenseService::daysLeft(),
            'activePlan' => \App\Models\Setting::get('active_plan', 'free'),
        ]);
    }
}
