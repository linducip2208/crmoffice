<?php

namespace App\Filament\Admin\Resources\Tasks\Schemas;

use App\Models\Milestone;
use App\Models\Project;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Task')->schema([
                TextInput::make('title')->label(__('crm.fields.title'))->required()->maxLength(255)->columnSpanFull(),
                Textarea::make('description')->label(__('crm.fields.description'))->rows(3)->columnSpanFull(),
                Grid::make(2)->schema([
                    Select::make('project_id')->label(__('crm.fields.project_id'))
                        ->options(fn () => Project::orderBy('name')->pluck('name', 'id'))
                        ->searchable()->live(),
                    Select::make('milestone_id')->label(__('crm.fields.milestone_id'))
                        ->options(fn ($get) => $get('project_id')
                            ? Milestone::where('project_id', $get('project_id'))->pluck('name', 'id')
                            : []),
                ]),
                Select::make('parent_task_id')->label(__('crm.fields.parent_id'))
                    ->options(function ($get, $record) {
                        $projectId = $get('project_id');
                        if (! $projectId) return [];
                        return \App\Models\Task::where('project_id', $projectId)
                            ->whereNotIn('status', ['done', 'cancelled'])
                            ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                            ->orderBy('title')
                            ->pluck('title', 'id');
                    })
                    ->searchable()
                    ->nullable()
                    ->helperText('Subtask akan tampil di bawah parent task ini.'),
                Grid::make(4)->schema([
                    Select::make('status')->label(__('crm.fields.task_status'))->options([
                        'todo' => 'To Do', 'in_progress' => 'In Progress', 'in_review' => 'In Review',
                        'done' => 'Done', 'cancelled' => 'Cancelled',
                    ])->default('todo')->required(),
                    Select::make('priority')->label(__('crm.fields.priority'))->options([
                        'low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent',
                    ])->default('medium')->required(),
                    DatePicker::make('start_date')->label(__('crm.fields.start_date'))->displayFormat('d M Y'),
                    DatePicker::make('due_date')->label(__('crm.fields.due_date'))->displayFormat('d M Y'),
                ]),
                Select::make('assignees')->label(__('crm.fields.assignees'))->multiple()->relationship('assignees', 'name')->preload()->searchable()->columnSpanFull(),
            ]),

            Section::make('Billable & Estimate')->schema([
                Grid::make(3)->schema([
                    Toggle::make('is_billable')->label(__('crm.fields.is_billable'))->default(false),
                    TextInput::make('estimate_hours')->label(__('crm.fields.estimated_hours'))->numeric()->minValue(0)->suffix('h'),
                    TextInput::make('hourly_rate')->label(__('crm.fields.hourly_rate'))->numeric()->prefix('Rp')->suffix('/h'),
                ]),
            ])->collapsed(),

            Section::make('Visibility')->schema([
                Toggle::make('is_visible_to_customer')->label('Visible to customer portal')->default(false),
            ])->collapsed(),
        ]);
    }
}
