<?php

namespace App\Console\Commands;

use App\Services\CSVImportService;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Excel;

class ImportCsvCommand extends Command
{
    protected $signature = 'import:csv  {path : Path to CSV file}
                                        {changeHeaders? : Enter 6 headers after commas that will replace the default ones (No spaces) }
                                        {--test : Starting in test mode}
                                        {--missingFieldsRaport : show problems during process data }
                                        ';


    protected $description = 'Import CSV file into the database';

    /**
     * @var string[] default names for columns in CSV file
     */
    private $headers = ['Product Code', 'Product Name', 'Product Description', 'Stock', 'Cost in GBP', 'Discontinued'];

    /**
     * Wykonanie polecenia
     */
    public function handle()
    {
        $filePath = $this->argument('path');
        $changeHeaders = $this->argument('changeHeaders') ;
        $testMode = $this->option('test');
        $showMFR = $this->option('missingFieldsRaport');

        if($changeHeaders){
            $this->headers = explode(',', $changeHeaders); // overwriteHeaders
        }

        $csvImportService = new CSVImportService($testMode, $showMFR, $this->headers);
        $result = $csvImportService->importCSV($filePath);

        if (isset($result['exelErrrorsRaport'])) {
            $this->printErrorRepor($result['exelErrrorsRaport']);
            die();
        }
        $countFailedImport = (int)$result['countAllItems'] - (int)$result['countSuccessImport'];

        $this->info('======= RESULTS =======');
        $this->info('All processed  records: ' . $result['countAllItems']);
        $this->info('Imported records: ' . $result['countSuccessImport']);
        $this->info('Unsaved records: ' . $countFailedImport);

        $this->info(PHP_EOL);

        $this->info('====== End reports ======');
        $this->info('All unsaved records data:');

        $this->table(
            $this->headers,
            $result['reportFailItems']
        );

        $this->info(PHP_EOL);

        if ($showMFR) {
            $this->info('Missing fields raport:' . PHP_EOL);
            $this->generateTable($result['missingFieldsRaport']);
        }

    }

    /**
     * View report content
     *
     * @param array $result
     * @return void
     */
    private function printErrorRepor(array $result)
    {
        $this->error('======== Errors ========');
        $this->error('Missing headers:');

        if (isset($result['missing_headers'])) {
            foreach ($result['missing_headers'] as $error) {
                $this->error('- ' . $error);   // "Missing required field:
            }
        }
    }

    /**
     * Generate missing fields raport table
     * @return void
     */
    private function generateTable($data): void
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
