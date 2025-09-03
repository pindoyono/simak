<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Exports\SchoolTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class TestExcelExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:excel-export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Excel export functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Creating template export...');

            $export = new SchoolTemplateExport();
            $filename = 'school-template-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

            $this->info("Attempting to save as: {$filename}");

            Excel::store($export, $filename, 'local');

            $filePath = storage_path('app/' . $filename);
            $this->info("Expected file path: {$filePath}");

            if (file_exists($filePath)) {
                $this->info("Template exported successfully: {$filePath}");
                $this->info("File size: " . human_filesize(filesize($filePath)));
            } else {
                $this->error("Export failed - file not found at expected location");

                // Check if file exists elsewhere
                $altPath = storage_path('app/public/' . $filename);
                if (file_exists($altPath)) {
                    $this->info("File found at: {$altPath}");
                } else {
                    $this->error("File not found in app/ or app/public/");
                }
            }

        } catch (\Exception $e) {
            $this->error("Export failed: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
        }
    }
}

function human_filesize($bytes, $decimals = 2) {
    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}
