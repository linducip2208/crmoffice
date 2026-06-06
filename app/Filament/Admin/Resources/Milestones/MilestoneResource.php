<?php

namespace App\Filament\Admin\Resources\Milestones;

use App\Filament\Admin\Resources\Milestones\Pages\CreateMilestone;
use App\Filament\Admin\Resources\Milestones\Pages\EditMilestone;
use App\Filament\Admin\Resources\Milestones\Pages\ListMilestones;
use App\Filament\Admin\Resources\Milestones\Schemas\MilestoneForm;
use App\Filament\Admin\Resources\Milestones\Tables\MilestonesTable;
use App\Models\Milestone;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MilestoneResource extends Resource
{
    protected static ?string $model = Milestone::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static ?string $navigationLabel = 'Milestones';

    protected static string|\UnitEnum|null $navigationGroup = 'Proyek';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MilestoneForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MilestonesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMilestones::route('/'),
            'create' => CreateMilestone::route('/create'),
            'edit' => EditMilestone::route('/{record}/edit'),
        ];
    }
}
