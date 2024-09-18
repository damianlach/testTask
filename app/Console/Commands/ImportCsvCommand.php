<?php

namespace App\Console\Commands;

use App\Services\CSVImportService;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Excel;

class ImportCsvCommand extends Command
{
    protected $signature = 'import:csv {path : Path to CSV file} {--test : Starting in test mode} {--missingFieldsRaport : show problems during process data }';

    protected $description = 'Import CSV file into the database';

    /**
     * Wykonanie polecenia
     */
    public function handle()
    {
        $filePath = $this->argument('path');
        $testMode = $this->option('test');
        $showMFR = $this->option('missingFieldsRaport');

        $csvImportService = new CSVImportService($testMode, $showMFR);
        $result = $csvImportService->importCSV($filePath);
        $countFailedImport = (int)$result['countAllItems'] - (int)$result['countSuccessImport'];

        $this->info('======= RESULTS =======');
        $this->info('All processed  records: ' . $result['countAllItems']);
        $this->info('Imported records: ' . $result['countSuccessImport']);
        $this->info('Unsaved records: ' . $countFailedImport);

        $this->info(PHP_EOL);

        $this->info('====== End reports ======');
        $this->info('All unsaved records data:');
        $this->table(
            ['Product Name', 'Product Desc', 'Product Code', 'Stock', 'Cost In GBP', 'Discontinued'],
            $result['reportFailItems']
        );

        $this->info(PHP_EOL);

        if ($showMFR) {
            $this->info('Missing fields raport:' . PHP_EOL);
            $this->generateTable($result['missingFieldsRaport']);
        }

    }

    /**
     * Generate missing fields raport table
     * @return void
     */
    private function generateTable($data) : void
    {
        if (!empty($data)) {
            foreach ($data as $item) {
                foreach ($item as $id => $details) {
                    $this->info("ID: $id");
                    foreach ($details as $issue => $fields) {
                        $this->info("  Issue: $issue");
                        foreach ($fields as $field) {
                            $this->info("    - $field");
                        }
                    }
                    $this->info(PHP_EOL . str_repeat('-', 35) . PHP_EOL);
                }
            }
        }
    }
}
