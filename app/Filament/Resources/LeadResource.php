<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Filament\Resources\LeadResource\RelationManagers;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\HeaderAction;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Notifications\Actions\Action as NotificationAction;
use App\Imports\LeadsImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Campaign;


class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('campaign_id')
                    ->relationship('campaign', 'name')
                    ->required()
                    ->label('Campaign'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone_number')
                    ->tel()
                    ->maxLength(10),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone_number'),
                TextColumn::make('campaign.name')->label('Campaign')->sortable()->searchable(),
                TextColumn::make('created_at')->dateTime()->sortable()->label('Created'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                TableAction::make('Import Leads')
                    ->form([
                        Select::make('campaign_id')
                            ->label('Campaign')
                            ->options(Campaign::pluck('name', 'id'))
                            ->required(),

                        FileUpload::make('file')
                            ->label('Excel or CSV File')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'])
                            ->required()
                            ->disk('local')
                            ->directory('imports'),
                    ])
                    ->action(function (array $data): void {
                        $filePath = storage_path("app/private/{$data['file']}");
                        $import = new LeadsImport($data['campaign_id']);

                        Excel::import($import, $filePath);

                        $errorFilePath = storage_path('app/public/import-errors.csv');
                        if (file_exists($errorFilePath) && filesize($errorFilePath) > 0) {
                            Notification::make()
                                ->title('Import completed with errors')
                                ->warning()
                                ->body('Some rows failed to import. Download the error report.')
                                ->actions([
                                    NotificationAction::make('Download Error Report')
                                        ->url(asset('storage/import-errors.csv'))
                                        ->openUrlInNewTab(),
                                ])
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Import completed successfully')
                                ->success()
                                ->send();
                        }
                    })
                    ->icon('heroicon-m-arrow-up-tray'),
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
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
            'import' => Pages\ImportLeads::route('/import'),
        ];
    }
}
