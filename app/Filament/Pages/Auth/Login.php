<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;

class Login extends BaseLogin
{
    protected string $view = 'filament.auth.login';

    protected static string $layout = 'filament.auth.login-layout';

    public function getViewData(): array
    {
        $demoAccounts = [
            ['role' => 'Owner',      'email' => 'owner@crmoffice.local',      'password' => 'password', 'scope' => 'Akses penuh: roles, providers, billing, audit log'],
            ['role' => 'Admin',      'email' => 'admin@crmoffice.local',      'password' => 'password', 'scope' => 'Semua modul kecuali manage roles & reveal secret'],
            ['role' => 'Sales',      'email' => 'sales@crmoffice.local',      'password' => 'password', 'scope' => 'Leads, clients, estimates, proposals, contracts'],
            ['role' => 'Project Mgr','email' => 'pm@crmoffice.local',         'password' => 'password', 'scope' => 'Projects, milestones, tasks, time entries, gantt'],
            ['role' => 'Support',    'email' => 'support@crmoffice.local',    'password' => 'password', 'scope' => 'Tickets, SLA, knowledge base, departments'],
            ['role' => 'Accountant', 'email' => 'accountant@crmoffice.local', 'password' => 'password', 'scope' => 'Invoices, payments, credit notes, expenses, reports'],
            ['role' => 'Staff',      'email' => 'staff@crmoffice.local',      'password' => 'password', 'scope' => 'Assigned tasks + own time entries'],
        ];

        return [
            ...parent::getViewData(),
            'appName' => config('app.name', 'crmoffice'),
            'demoAccounts' => $demoAccounts,
        ];
    }
}
