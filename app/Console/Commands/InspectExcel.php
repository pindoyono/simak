<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class InspectExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspect:excel {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inspect Excel file structure';

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

        try {
            $reader = IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            $this->info("Excel file structure:");
            $this->info("===================");

            // Get headers (row 1)
            $headers = [];
            $columnCount = $worksheet->getHighestDataColumn();
            $columnIndex = 'A';

            while ($columnIndex <= $columnCount) {
                $cellValue = $worksheet->getCell($columnIndex . '1')->getFormattedValue();
                $headers[] = $cellValue;
                $this->info("Column {$columnIndex}: {$cellValue}");
                $columnIndex++;
            }

            $this->info("\nFirst few data rows:");
            $this->info("===================");

            // Show first 3 data rows
            for ($row = 2; $row <= 4; $row++) {
                $this->info("Row {$row}:");
                $columnIndex = 'A';
                $col = 0;

                while ($columnIndex <= $columnCount && $col < count($headers)) {
                    $cellValue = $worksheet->getCell($columnIndex . $row)->getFormattedValue();
                    $cellType = $worksheet->getCell($columnIndex . $row)->getDataType();
                    $this->info("  {$headers[$col]}: '{$cellValue}' (type: {$cellType})");
                    $columnIndex++;
                    $col++;
                }
                $this->info("");
            }

        } catch (\Exception $e) {
            $this->error("Failed to inspect Excel: " . $e->getMessage());
        }
    }
}
