<?php

namespace App\Filament\Admin\Resources\Tickets\Schemas;

use App\Models\Client;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Project;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use App\Services\NumberSequence;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Ticket')->schema([
                Grid::make(3)->schema([
                    TextInput::make('number')->label(__('crm.fields.number'))->required()->maxLength(40)
                        ->default(fn () => NumberSequence::peek('ticket'))
                        ->unique(ignoreRecord: true)
                        ->helperText('Auto-generated; editable before save.'),
                    Select::make('client_id')->label(__('crm.fields.client_id'))
                        ->options(fn () => Client::orderBy('company_name')->pluck('company_name', 'id'))
                        ->searchable()->live(),
                    Select::make('contact_id')->label(__('crm.fields.contact_id'))
                        ->options(fn ($get) => $get('client_id')
                            ? Contact::where('client_id', $get('client_id'))->get()->mapWithKeys(fn ($c) => [$c->id => $c->full_name])
                            : []),
                ]),
                TextInput::make('subject')->label(__('crm.fields.subject'))->required()->maxLength(255)->columnSpanFull(),
                Textarea::make('body')->label(__('crm.fields.body'))->rows(5)->columnSpanFull(),
            ]),

            Section::make('Routing')->schema([
                Grid::make(4)->schema([
                    Select::make('department_id')->label(__('crm.fields.department_id'))
                        ->options(fn () => Department::where('is_active', true)->pluck('name', 'id'))
                        ->required()
                        ->default(fn () => Department::where('is_active', true)->value('id')),
                    Select::make('priority_id')->label(__('crm.fields.priority_id'))
                        ->options(fn () => TicketPriority::where('is_active', true)->orderBy('order')->pluck('name', 'id'))
                        ->required()
                        ->default(fn () => TicketPriority::where('name', 'Medium')->value('id')),
                    Select::make('status_id')->label(__('crm.fields.status_id'))
                        ->options(fn () => TicketStatus::orderBy('order')->pluck('name', 'id'))
                        ->required()
                        ->default(fn () => TicketStatus::where('name', 'Open')->value('id')),
                    Select::make('assigned_to')->label(__('crm.fields.assigned_to'))
                        ->options(fn () => User::where('is_active', true)->pluck('name', 'id'))
                        ->searchable(),
                    Select::make('related_project_id')->label(__('crm.fields.project_id'))
                        ->options(fn () => Project::orderBy('name')->pluck('name', 'id'))
                        ->searchable(),
                ]),
            ]),
        ]);
    }
}
