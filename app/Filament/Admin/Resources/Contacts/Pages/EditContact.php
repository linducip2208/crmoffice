<?php

namespace App\Filament\Admin\Resources\Contacts\Pages;

use App\Filament\Admin\Resources\Contacts\ContactResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditContact extends EditRecord
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendInvitation')
                ->label('Send Portal Invitation')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn () => filled($this->record->email))
                ->requiresConfirmation()
                ->modalDescription(fn () => "Kirim link invitation portal ke {$this->record->email}. Link berlaku 48 jam.")
                ->action(function () {
                    $token = Str::random(64);
                    $this->record->update([
                        'invitation_token' => $token,
                        'invitation_expires_at' => now()->addHours(48),
                        'portal_access' => true,
                    ]);

                    $url = url("/portal/accept-invitation/{$token}");

                    // TODO: real email send via Mail::to($this->record->email)->send(new PortalInvitation($this->record, $url));
                    // For now: log + show admin the URL so they can copy/share manually.
                    logger()->info('Portal invitation', ['contact_id' => $this->record->id, 'email' => $this->record->email, 'url' => $url]);

                    Notification::make()
                        ->title('Invitation generated')
                        ->body("Share link manually until mail provider configured:\n{$url}")
                        ->success()
                        ->persistent()
                        ->send();
                }),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
