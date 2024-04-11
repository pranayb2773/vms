<?php

namespace App\Filament\Resources\RoleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;
use Spatie\Permission\Models\Permission;

class PermissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'permissions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->string()
                    ->maxLength(255)
                    ->unique(ignoreRecord:true, modifyRuleUsing: function (Unique $rule, Get $get) {
                        return $rule->where('guard_name', $get('guard_name'));
                    }),

                Forms\Components\Select::make('guard_name')
                    ->options([
                        'web' => 'Web',
                        'api' => 'API',
                    ])
                    ->default('web')
                    ->required()
                    ->in(['web', 'api'])
                ,
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                ,

                Tables\Columns\TextColumn::make('guard_name')
                    ->formatStateUsing(fn (string $state): string => str($state)->upper()->value())
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'web' => 'primary',
                        'api' => 'danger'
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'web' => 'heroicon-m-globe-alt',
                        'api' => 'heroicon-m-cpu-chip'
                    })
                ,

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                ,

                Tables\Columns\TextColumn::make('updated_at')
                    ->date()
                    ->sortable()
                    ->toggleable()
                ,
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('all')
                                    ->label('All')
                                    ->color('primary')
                                    ->icon('heroicon-o-check-circle')
                                    ->hiddenLabel(false)
                                    ->action(function (Set $set, $state) {
                                        $ids = Permission::whereNotIn('id', $state)?->pluck('id')?->map(fn ($id): string => (string) $id)?->toArray();
                                        $set('recordId', array_merge($ids, $state));
                                    })
                            )
                    ])
                    ->recordTitle(fn (Permission $record): string => $record->name . ' - ' . str($record->guard_name)->ucfirst()->value())
                    ->recordSelectSearchColumns(['name', 'guard_name'])
                    ->preloadRecordSelect()
                    ->multiple()
                ,
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
