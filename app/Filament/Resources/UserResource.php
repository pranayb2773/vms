<?php

namespace App\Filament\Resources;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Unique;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('first_name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('last_name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan('full'),
                            ])->columns(2),

                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->revealable(filament()->arePasswordsRevealable())
                                    ->required()
                                    ->rule(Password::default())
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->same('passwordConfirmation')
                                    ->validationAttribute('password'),
                                Forms\Components\TextInput::make('passwordConfirmation')
                                    ->label('Confirm Password')
                                    ->password()
                                    ->revealable(filament()->arePasswordsRevealable())
                                    ->required()
                                    ->dehydrated(false),
                            ])
                            ->columns(2)
                            ->hidden(fn (string $operation): bool => $operation === 'edit')
                        ,

                        Forms\Components\Section::make('Profile Photo')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('media')
                                    ->collection('user-profile-photo')
                                    ->hiddenLabel(),
                            ])
                            ->collapsible()
                        ,

                        /*Forms\Components\Section::make('Associations')
                            ->schema([
                                Forms\Components\Select::make('roles')
                                    ->relationship('roles', 'name')
                                    ->preload()
                                    ->multiple()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->unique(modifyRuleUsing: function (Unique $rule, Get $get) {
                                                return $rule->where('guard_name', $get('guard_name'));
                                            })
                                        ,
                                        Forms\Components\Select::make('guard_name')
                                            ->options([
                                                'web' => 'Web',
                                                'api' => 'API',
                                            ])
                                            ->default('web')
                                            ->required()
                                        ,
                                    ])
                                ,

                                Forms\Components\Select::make('permissions')
                                    ->relationship('permissions', 'name')
                                    ->multiple()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->unique(modifyRuleUsing: function (Unique $rule, Get $get) {
                                                return $rule->where('guard_name', $get('guard_name'));
                                            })
                                        ,
                                        Forms\Components\Select::make('guard_name')
                                            ->options([
                                                'web' => 'Web',
                                                'api' => 'API',
                                            ])
                                            ->default('web')
                                            ->required()
                                        ,
                                    ])
                                ,
                            ]),*/
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\ToggleButtons::make('status')
                                    ->inline()
                                    ->options(
                                        collect(UserStatus::toArray())->pluck('name', 'id')
                                    )
                                    ->colors(collect(UserStatus::toArray())->pluck('color', 'id'))
                                    ->icons(collect(UserStatus::toArray())->pluck('icon', 'id'))
                                ,

                                Forms\Components\ToggleButtons::make('type')
                                    ->inline()
                                    ->options(
                                        collect(UserType::toArray())->pluck('name', 'id')
                                    )
                                    ->colors(collect(UserType::toArray())->pluck('color', 'id'))
                                    ->icons(collect(UserType::toArray())->pluck('icon', 'id'))
                                ,
                            ])
                        ,

                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                ->label('Created At')
                                ->content(fn (User $user): string => $user->created_at->isoFormat('LLL')),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label('Last Modified At')
                                    ->content(fn (User $user): string => $user->updated_at->isoFormat('LLL'))
                                ,
                            ])
                            ->hidden(fn (string $operation): bool => $operation === 'create')
                        ,
                    ])
                    ->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Name')
                    ->formatStateUsing(fn (User $user): string => $user->first_name . ' ' . $user->last_name)
                    ->searchable()
                    ->sortable()
                ,
                Tables\Columns\TextColumn::make('email')
                    ->sortable()
                    ->searchable()
                ,
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn (UserType $state): string => $state->name())
                    ->badge()
                    ->color(fn (UserType $state): string => match ($state->value) {
                        'internal' => 'primary',
                        'external' => 'danger'
                    })
                    ->icon(fn (UserType $state): string => match ($state->value) {
                        'internal' => 'heroicon-m-arrow-left-circle',
                        'external' => 'heroicon-m-arrow-right-circle',
                    })
                ,
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn (UserStatus $state): string => $state->name())
                    ->badge()
                    ->color(fn (UserStatus $state): string => $state->color())
                    ->icon(fn (UserStatus $state): string => $state->icon())
                ,
                Tables\Columns\TextColumn::make('roles.name')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->toggleable()
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
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make()
                    ->schema([
                        Components\Split::make([
                            Components\Grid::make(3)
                                ->schema([
                                    Components\TextEntry::make('first_name'),
                                    Components\TextEntry::make('last_name'),
                                    Components\TextEntry::make('email'),
                                    Components\TextEntry::make('created_at')
                                        ->badge()
                                        ->date()
                                        ->color('success')
                                    ,
                                    /*Components\TextEntry::make('roles.name')
                                        ->color('warning')
                                        ->badge()
                                    ,
                                    Components\TextEntry::make('permissions.name')
                                        ->badge()
                                        ->color('warning')
                                        ->limitList(10)
                                    ,*/
                                    Components\TextEntry::make('updated_at')
                                        ->label('Last Modified At')
                                        ->badge()
                                        ->date()
                                        ->color('info')
                                    ,
                                ]),
                            Components\SpatieMediaLibraryImageEntry::make('media')
                                ->collection('user-profile-photo')
                                ->hiddenLabel()
                                ->grow(false)
                                ->square()
                        ])->from('lg'),
                    ]),
            ]);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            //Pages\EditUser::class,
        ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RolesRelationManager::class,
            RelationManagers\PermissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
