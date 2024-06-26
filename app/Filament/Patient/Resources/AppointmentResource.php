<?php

namespace App\Filament\Patient\Resources;

use App\Enums\AuthRoles;
use App\Filament\Patient\Resources\AppointmentResource\Pages;
use App\Filament\Patient\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\search;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where("patient_id", "=", auth()->user()->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('patient_id')
                    ->default(Auth::user()->id),
                Forms\Components\Hidden::make('requester')
                    ->default(AuthRoles::PATIENT->value),

                Forms\Components\Select::make('doctor_id')
                    ->relationship('doctor')
                    ->getSearchResultsUsing(function(string $query){
                        return DB::table('users')
                                    ->join('doctors', 'users.id', '=', 'doctors.user_id' )
                                    ->select('users.*', 'doctors.*')
                                    ->where('name', 'LIKE', "%{$query}%")
                                    ->pluck('name', 'user_id');
                    })
                    ->searchable()
                    ->label('with doctor')
                    ->required(),

                Forms\Components\DateTimePicker::make('time')
                    ->label('appointment at')
                    ->minDate(now()->addDay())
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchable()
            ->columns([
                Tables\Columns\TextColumn::make("doctor.name")
                    ->label("with doctor")->sortable(),
                Tables\Columns\TextColumn::make('time')
                    ->sortable()
                    ->label('appointment time'),
                Tables\Columns\TextColumn::make('requester')
                    ->formatStateUsing(function(string $state){
                        return $state == AuthRoles::PATIENT->value ? "you" : 'doctor';
                    })
                    ->label('Requested By'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('cancel'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
