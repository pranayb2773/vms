<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->string()
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
                            ])
                            ->columns(2)
                        ,
                    ])->columnSpan(fn (string $operation): int => $operation === 'create' ? 3 : 2)
                ,

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                    ->label('Created At')
                                    ->content(fn (Role $role): string => $role->created_at->isoFormat('LLL'))
                                ,

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label('Last Modified At')
                                    ->content(fn (Role $role): string => $role->updated_at->isoFormat('LLL'))
                                ,
                            ])
                        ,
                    ])
                    ->hidden(fn (string $operation): bool => $operation === 'create'),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
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
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->after(fn () => app()->make(PermissionRegistrar::class)->forgetCachedPermissions()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PermissionsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
