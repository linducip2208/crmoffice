<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // CRM
            'view.client', 'view_any.client', 'create.client', 'update.client',
            'delete.client', 'restore.client', 'force_delete.client', 'export.client', 'import.client',
            'view.contact', 'view_any.contact', 'create.contact', 'update.contact', 'delete.contact',
            'view.lead', 'view_any.lead', 'create.lead', 'update.lead', 'delete.lead',
            'convert.lead', 'export.lead', 'import.lead',
            'view.activity', 'create.activity', 'update.activity', 'delete.activity',

            // Sales
            'view.estimate', 'view_any.estimate', 'create.estimate', 'update.estimate',
            'delete.estimate', 'send.estimate', 'convert.estimate',
            'view.proposal', 'view_any.proposal', 'create.proposal', 'update.proposal',
            'delete.proposal', 'send.proposal',
            'view.contract', 'view_any.contract', 'create.contract', 'update.contract',
            'delete.contract', 'send.contract',
            'view.invoice', 'view_any.invoice', 'create.invoice', 'update.invoice',
            'delete.invoice', 'send.invoice', 'mark_paid.invoice', 'void.invoice', 'apply_credit_note.invoice',
            'view.payment', 'view_any.payment', 'create.payment', 'refund.payment', 'delete.payment',
            'view.credit_note', 'view_any.credit_note', 'create.credit_note',
            'update.credit_note', 'delete.credit_note',
            'view.item', 'manage.item',
            'view.tax_rate', 'manage.tax_rate',
            'view.currency', 'manage.currency',
            'view.expense', 'view_any.expense', 'create.expense', 'update.expense', 'delete.expense',

            // Projects & Tasks
            'view.project', 'view_any.project', 'create.project', 'update.project',
            'delete.project', 'manage_members.project',
            'view.milestone', 'create.milestone', 'update.milestone', 'delete.milestone',
            'view.task', 'view_any.task', 'create.task', 'update.task', 'delete.task', 'assign.task',
            'view.time_entry', 'view_any.time_entry', 'create.time_entry',
            'update.time_entry', 'delete.time_entry', 'view_others.time_entry',
            'view.discussion', 'create.discussion', 'update.discussion', 'delete.discussion',

            // Support
            'view.ticket', 'view_any.ticket', 'create.ticket', 'update.ticket', 'delete.ticket',
            'assign.ticket', 'escalate.ticket', 'reply.ticket', 'internal_note.ticket',
            'view.department', 'manage.department',
            'view.ticket_priority', 'manage.ticket_priority',
            'view.ticket_status', 'manage.ticket_status',
            'view.sla_policy', 'manage.sla_policy',
            'view.kb_category', 'manage.kb_category',
            'view.kb_article', 'view_any.kb_article', 'create.kb_article',
            'update.kb_article', 'delete.kb_article', 'publish.kb_article',

            // Cross-cutting
            'view.calendar', 'manage_own.calendar',
            'view.notification', 'manage_own.notification',
            'view.goal', 'view_any.goal', 'create.goal', 'update.goal', 'delete.goal', 'view_others.goal',
            'view.survey', 'view_any.survey', 'create.survey', 'update.survey', 'delete.survey',
            'view.announcement', 'manage.announcement',
            'view.report.sales', 'view.report.leads', 'view.report.project_profitability',
            'view.report.time', 'view.report.tickets', 'view.report.expenses',

            // Platform / Admin
            'view.user', 'view_any.user', 'create.user', 'update.user', 'delete.user',
            'impersonate.user', 'disable.user',
            'view.role', 'manage.role',
            'view.permission',
            'view.custom_field', 'manage.custom_field',
            'view.provider', 'manage.provider', 'reveal_secret.provider', 'test.provider',
            'view.setting', 'manage.setting',
            'view.audit_log',
            'view.webhook', 'manage.webhook', 'replay.webhook_delivery',
            'view.file', 'delete.file', 'delete_others.file',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
