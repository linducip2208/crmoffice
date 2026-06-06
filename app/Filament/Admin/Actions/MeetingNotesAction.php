<?php

namespace App\Filament\Admin\Actions;

use Filament\Actions\Action;
use Illuminate\Support\Facades\View;

class MeetingNotesAction extends Action
{
    protected string $relatedType;

    protected int $relatedId;

    public static function getDefaultName(): ?string
    {
        return 'meetingNotes';
    }

    public function relatedType(string $type): static
    {
        $this->relatedType = $type;

        return $this;
    }

    public function relatedId(int $id): static
    {
        $this->relatedId = $id;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Catat Meeting')
            ->icon('heroicon-o-microphone')
            ->color('primary')
            ->modalHeading('Catat Meeting — AI')
            ->modalWidth('3xl')
            ->modalContent(fn () => View::make('filament.admin.partials.meeting-notes-modal', [
                'relatedType' => $this->relatedType,
                'relatedId' => $this->relatedId,
                'generateUrl' => route('admin.ai.meeting-notes'),
                'modalId' => $this->getId(),
            ])->render())
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup');
    }
}
