<?php

namespace App\Filament\Admin\Actions;

use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class BulkAssignAction extends BulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'bulkAssign';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Assign to User')
            ->icon('heroicon-o-user-plus')
            ->form([
                Select::make('assigned_to')
                    ->label('User')
                    ->options(fn () => User::pluck('name', 'id'))
                    ->required(),
            ])
            ->action(function ($records, array $data) {
                $count = 0;
                foreach ($records as $lead) {
                    $lead->update(['assigned_to' => $data['assigned_to']]);
                    $count++;
                }
                Notification::make()
                    ->title("Assigned $count leads to user")
                    ->success()
                    ->send();
            });
    }
}
