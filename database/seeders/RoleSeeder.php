<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $all = Permission::pluck('name')->toArray();

        // Owner — everything
        $owner = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner->syncPermissions($all);

        // Admin — everything except owner role management + secret reveal + impersonate
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(
            array_diff($all, ['manage.role', 'impersonate.user', 'reveal_secret.provider'])
        );

        // Sales — leads, clients, estimates/proposals/contracts; limited invoice
        $sales = Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);
        $sales->syncPermissions(array_filter($all, fn ($p) => str_contains($p, '.client')
            || str_contains($p, '.contact')
            || str_contains($p, '.lead')
            || str_contains($p, '.activity')
            || str_contains($p, '.estimate')
            || str_contains($p, '.proposal')
            || str_contains($p, '.contract')
            || in_array($p, ['view.invoice', 'view_any.invoice', 'create.invoice', 'update.invoice',
                'view.calendar', 'manage_own.calendar', 'view.notification', 'manage_own.notification',
                'view.report.sales', 'view.report.leads',
                'view.goal', 'view_any.goal', 'view.survey', 'view_any.survey',
                'view.announcement', 'create.task', 'view.task', 'view_any.task'])
        ));

        // Project Manager — projects, tasks, milestones, time
        $pm = Role::firstOrCreate(['name' => 'pm', 'guard_name' => 'web']);
        $pm->syncPermissions(array_filter($all, fn ($p) => str_contains($p, '.project')
            || str_contains($p, '.milestone')
            || str_contains($p, '.task')
            || str_contains($p, '.time_entry')
            || str_contains($p, '.discussion')
            || in_array($p, ['view.client', 'view.contact', 'create.invoice', 'view.invoice',
                'view.calendar', 'manage_own.calendar', 'view.notification', 'manage_own.notification',
                'view.report.project_profitability', 'view.report.time', 'view.report.expenses',
                'view.goal', 'view.task', 'view.activity', 'create.activity'])
        ));

        // Support — tickets, KB
        $support = Role::firstOrCreate(['name' => 'support', 'guard_name' => 'web']);
        $support->syncPermissions(array_filter($all, fn ($p) => str_contains($p, '.ticket')
            || str_contains($p, '.kb_')
            || str_contains($p, '.department')
            || str_contains($p, '.sla_policy')
            || in_array($p, ['view.client', 'view.contact', 'view.project',
                'view.calendar', 'manage_own.calendar', 'view.notification', 'manage_own.notification',
                'view.report.tickets', 'view.goal', 'view.activity', 'create.activity'])
        ));

        // Accountant — financial entities
        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $accountant->syncPermissions(array_filter($all, fn ($p) => str_contains($p, '.invoice')
            || str_contains($p, '.payment')
            || str_contains($p, '.credit_note')
            || str_contains($p, '.expense')
            || str_contains($p, '.tax_rate')
            || str_contains($p, '.currency')
            || str_contains($p, '.item')
            || str_contains($p, '.estimate')
            || in_array($p, ['view.client', 'view_any.client', 'view.contact',
                'view.calendar', 'manage_own.calendar', 'view.notification', 'manage_own.notification',
                'view.report.sales', 'view.report.project_profitability',
                'view.report.time', 'view.report.expenses', 'view.goal', 'view_others.time_entry'])
        ));

        // Staff — tasks assigned + time entries
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff->syncPermissions([
            'view.task', 'view_any.task', 'update.task',
            'view.time_entry', 'create.time_entry', 'update.time_entry',
            'view.calendar', 'manage_own.calendar',
            'view.notification', 'manage_own.notification',
            'view.client', 'view.contact', 'view.project',
            'view.activity', 'create.activity',
            'view.goal',
            'view.ticket', 'create.ticket',
            'view.report.time',
        ]);
    }
}
