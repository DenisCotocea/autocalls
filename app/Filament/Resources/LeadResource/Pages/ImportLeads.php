<?php

namespace App\Filament\Resources\LeadResource\Pages;

use App\Imports\LeadsImport;
use App\Models\Campaign;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use App\Filament\Resources\LeadResource;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;

class ImportLeads extends Page
{
    protected static string $resource = LeadResource::class;

    public $file;
    public $campaign_id;

    public function mount()
    {
        $this->campaign_id = null;
        $this->file = null;
    }

    public function importLeads()
    {
        $this->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'file' => 'required|file|mimes:xlsx,csv',
        ]);

        try {
            $import = new LeadsImport($this->campaign_id);
            $imported = Excel::import($import, $this->file);

            // Notification after success
            Notification::make()
                ->title('Import Completed')
                ->success()
                ->body('The leads have been imported successfully!');

            // If there are failed rows
            $this->sendFailedImportNotification();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Import Failed')
                ->danger()
                ->body('An error occurred during the import. Please check your file and try again.');
        }
    }

    public function sendFailedImportNotification()
    {
        if (file_exists(storage_path('app/public/import-errors.csv'))) {
            Notification::make()
                ->title('Import Completed with Errors')
                ->warning()
                ->body('Some rows failed validation. Download the error report below.')
                ->actions([
                    Action::make('Download Error Report')
                        ->url(storage_path('app/public/import-errors.csv'))
                        ->openUrlInNewTab(),
                ]);
        }
    }

    public function getBreadcrumbs(): array
    {
        return [
            ['label' => 'Leads', 'url' => route('filament.resources.leads.index')],
            ['label' => 'Import Leads'],
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('campaign_id')
                ->label('Campaign')
                ->options(Campaign::all()->pluck('name', 'id'))
                ->required(),
            FileUpload::make('file')
                ->label('Upload Leads File (CSV or Excel)')
                ->acceptedFileTypes(['.csv', '.xlsx'])
                ->required(),
        ];
    }

    protected function getActions(): array
    {
        return [
            Action::make('importLeads')
                ->label('Import Leads')
                ->action('importLeads')
                ->color('primary')
                ->icon('heroicon-o-cloud-upload'),
        ];
    }
}
