<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\Spreadsheet;
use Google\Service\Sheets\ValueRange;
use Google\Service\Sheets\BatchUpdateValuesRequest;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    protected $client;
    protected $service;
    protected $enabled;

    public function __construct()
    {
        $this->enabled = $this->checkGoogleConfig();
        
        if ($this->enabled) {
            try {
                $this->client = new Client();
                $this->client->setAuthConfig(config('services.google.credentials_path'));
                $this->client->addScope(Sheets::SPREADSHEETS);
                $this->service = new Sheets($this->client);
            } catch (\Exception $e) {
                Log::error('Google Sheets service initialization failed: ' . $e->getMessage());
                $this->enabled = false;
            }
        }
    }

    /**
     * Check if Google configuration is available
     */
    private function checkGoogleConfig()
    {
        $credentialsPath = config('services.google.credentials_path');
        return !empty($credentialsPath) && file_exists($credentialsPath);
    }

    /**
     * Create a new Google Sheet
     */
    public function createSheet($title, $headers = [])
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'error' => 'Google Sheets service is not configured'
            ];
        }

        try {
            $spreadsheet = new Spreadsheet([
                'properties' => [
                    'title' => $title
                ]
            ]);

            $spreadsheet = $this->service->spreadsheets->create($spreadsheet);
            $spreadsheetId = $spreadsheet->spreadsheetId;

            // Add headers if provided
            if (!empty($headers)) {
                $this->updateSheet($spreadsheetId, 'Sheet1', $headers, []);
            }

            return [
                'success' => true,
                'spreadsheetId' => $spreadsheetId,
                'spreadsheetUrl' => $spreadsheet->spreadsheetUrl
            ];

        } catch (\Exception $e) {
            Log::error('Google Sheet creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Append data to sheet
     */
    public function appendToSheet($spreadsheetId, $range, $data)
    {
        if (!$this->enabled) {
            Log::info('Google Sheets append skipped - service not configured', [
                'spreadsheetId' => $spreadsheetId,
                'range' => $range,
                'data' => $data
            ]);
            return [
                'success' => false,
                'error' => 'Google Sheets service is not configured'
            ];
        }

        try {
            Log::info("GoogleSheetsService called", [
                'spreadsheetId' => $spreadsheetId,
                'range' => $range,
                'data' => $data
            ]);

            $valueRange = new ValueRange();
            $valueRange->setValues([$data]);
            
            $options = ['valueInputOption' => 'RAW'];
            
            $result = $this->service->spreadsheets_values->append(
                $spreadsheetId, 
                $range, 
                $valueRange, 
                $options
            );

            return [
                'success' => true,
                'updates' => $result->getUpdates()
            ];
            
        } catch (\Exception $e) {
            Log::error('Google Sheet append failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update sheet with data
     */
    public function updateSheet($spreadsheetId, $sheetName, $headers, $rows)
    {
        try {
            // Clear existing data first
            $this->clearSheet($spreadsheetId, $sheetName);

            // Prepare data
            $data = [];
            $data[] = new ValueRange([
                'range' => $sheetName . '!A1',
                'values' => [$headers]
            ]);

            if (!empty($rows)) {
                $data[] = new ValueRange([
                    'range' => $sheetName . '!A2',
                    'values' => $rows
                ]);
            }

            $body = new BatchUpdateValuesRequest([
                'valueInputOption' => 'RAW',
                'data' => $data
            ]);

            $result = $this->service->spreadsheets_values->batchUpdate($spreadsheetId, $body);

            return [
                'success' => true,
                'updatedCells' => $result->getTotalUpdatedCells(),
                'updatedRanges' => $result->getUpdatedRanges()
            ];

        } catch (\Exception $e) {
            Log::error('Google Sheet update failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Clear sheet content
     */
    private function clearSheet($spreadsheetId, $sheetName)
    {
        try {
            $range = $sheetName . '!A:Z';
            $this->service->spreadsheets_values->clear($spreadsheetId, $range, new \Google\Service\Sheets\ClearValuesRequest());
        } catch (\Exception $e) {
            Log::error('Google Sheet clear failed: ' . $e->getMessage());
        }
    }

    /**
     * Check if sheet exists and is accessible
     */
    public function checkSheetAccess($spreadsheetId)
    {
        try {
            $spreadsheet = $this->service->spreadsheets->get($spreadsheetId);
            return $spreadsheet !== null;

        } catch (\Exception $e) {
            return false;
        }
    }
}