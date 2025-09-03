<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\SchoolImport;
use Maatwebsite\Excel\Facades\Excel;

class TestExcelImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:excel-import {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Excel import functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('file');
        $filePath = storage_path('app/public/imports/' . $filename);

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        }

        $this->info("Testing import from: {$filePath}");
        $this->info("File size: " . $this->human_filesize(filesize($filePath)));

        try {
            $import = new SchoolImport();
            Excel::import($import, $filePath);

            $errors = $import->failures();
            $errorCount = $errors->count();
            $importedCount = $import->getImportedCount();
            $skippedCount = $import->getSkippedCount();

            $this->info("Import completed!");
            $this->info("Imported rows: {$importedCount}");

            if ($skippedCount > 0) {
                $this->info("Skipped empty rows: {$skippedCount}");
            }

            if ($errorCount > 0) {
                $this->warn("Found {$errorCount} validation errors:");

                foreach ($errors->take(10) as $failure) {
                    $this->error("Row {$failure->row()}: " . implode(', ', $failure->errors()));
                }

                if ($errorCount > 10) {
                    $this->warn("... and " . ($errorCount - 10) . " more errors");
                }
            } else {
                $this->info("No validation errors found!");
            }

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $this->error("Validation failed:");
            foreach ($e->failures() as $failure) {
                $this->error("Row {$failure->row()}: " . implode(', ', $failure->errors()));
            }
        } catch (\Exception $e) {
            $this->error("Import failed: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
        }
    }

    private function human_filesize($bytes, $decimals = 2) {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
}
