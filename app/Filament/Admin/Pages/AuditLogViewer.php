<?php

namespace App\Filament\Admin\Pages;

use App\Models\AuditLog;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class AuditLogViewer extends Page
{
    protected string $view = 'filament.admin.pages.audit-log';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Audit Log';

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 7;

    protected static ?string $slug = 'audit-log';

    public string $actionFilter = '';
    public ?int $userFilter = null;
    public string $subjectFilter = '';

    public function getLogs()
    {
        return AuditLog::query()
            ->when($this->actionFilter, fn ($q) => $q->where('action', $this->actionFilter))
            ->when($this->userFilter, fn ($q) => $q->where('user_id', $this->userFilter))
            ->when($this->subjectFilter, fn ($q) => $q->where('subject_type', 'like', "%{$this->subjectFilter}%"))
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();
    }

    public function getUsersForFilter(): array
    {
        return User::pluck('name', 'id')->toArray();
    }
}
