<?php

namespace App\Imports;

use App\Models\Lead;
use App\Models\Campaign;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

use Throwable;
use Maatwebsite\Excel\Validators\Failure;

class LeadsImport implements   ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError, WithBatchInserts, WithChunkReading
{
    use Importable, SkipsFailures, SkipsErrors;

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    protected $campaignId;

    public function __construct($campaignId)
    {
        $this->campaignId = $campaignId;
    }

    public function model(array $row)
    {
        return new Lead([
            'campaign_id' => $this->campaignId,
            'name' => $row['name'],
            'email' => $row['email'],
            'phone_number' => $row['phone_number'],
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => ['required', 'regex:/^\+?[1-9]\d{1,14}$/'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'phone_number.regex' => 'The phone number must be a valid international format.',
        ];
    }

    public function onFailure(\Maatwebsite\Excel\Validators\Failure ...$failures)
    {
        $errors = [];
        foreach ($failures as $failure) {
            $errors[] = [
                'row' => $failure->row(),
                'errors' => $failure->errors(),
            ];
        }

        // Save the errors to a CSV file
        $this->generateErrorReport($errors);
    }

    protected function generateErrorReport($errors)
    {
        $csvFile = fopen(storage_path('app/public/import-errors.csv'), 'w');
        fputcsv($csvFile, ['Row', 'Validation Error']);

        foreach ($errors as $error) {
            fputcsv($csvFile, [
                $error['row'],
                implode(", ", $error['errors']),
            ]);
        }

        fclose($csvFile);
    }
}
