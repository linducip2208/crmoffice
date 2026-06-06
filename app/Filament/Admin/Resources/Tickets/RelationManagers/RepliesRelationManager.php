<?php

namespace App\Filament\Admin\Resources\Tickets\RelationManagers;

use App\Models\CannedResponse;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RepliesRelationManager extends RelationManager
{
    protected static string $relationship = 'replies';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('canned_response_id')
                    ->label(__('crm.canned_response.select'))
                    ->placeholder('-- ' . __('crm.canned_response.select') . ' --')
                    ->options(function () {
                        return CannedResponse::where(function ($q) {
                            $q->where('is_shared', true)
                              ->orWhere('created_by', auth()->id());
                        })->orderBy('title')->pluck('title', 'id');
                    })
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        if (! $state) {
                            return;
                        }
                        $canned = CannedResponse::find($state);
                        if ($canned) {
                            $set('body', $canned->content);
                        }
                    })
                    ->columnSpanFull(),
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull(),
                Select::make('user_id')
                    ->relationship('user', 'name'),
                Select::make('contact_id')
                    ->relationship('contact', 'id'),
                TextInput::make('email_from')
                    ->email(),
                Toggle::make('is_internal')
                    ->required(),
                TextInput::make('source')
                    ->required()
                    ->default('web'),
                TextInput::make('email_message_id')
                    ->email(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('contact.id')
                    ->searchable(),
                TextColumn::make('email_from')
                    ->searchable(),
                IconColumn::make('is_internal')
                    ->boolean(),
                TextColumn::make('source')
                    ->searchable(),
                TextColumn::make('email_message_id')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
