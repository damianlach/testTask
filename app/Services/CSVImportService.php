<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Command\ProductDataWrite;
use Carbon\Carbon;
use \App\DTO\ProductDataDTO;

class CSVImportService
{
    /**
     * @var int All processed items from exel file
     */
    private int $countAllItems = 0;

    /**
     * @var int Only Success items from exel file
     */
    private int $countSuccessImport = 0;

    /**
     * @param bool $testMode - run test mode process without saved to DB
     */

    /**
     * Get all unsaved record for end report
     * @var array
     */
    public $reportFailItems = [];

    /**
     * Missing fields raport - errors detected while importing data
     * Not correct type or missing indexes
     * @var array
     */
    public $missingFieldsRaport = [];

    /**
     * @var array|null Generates a report on errors related to loading a CSV file
     */
    public array|null $exelErrrorsRaport = null;

    /**
     * @param bool $testMode test mode - run scripts without modified database
     * @param bool $showMFR show missing fields raport
     * @param array $headers names for columns
     */
    public function __construct(private bool $testMode, private bool $showMFR, private array $headers)
    {
    }

    /**
     * Import CSV file
     *
     * @param string $filePath
     * @return array
     */
    public function importCSV(string $filePath)
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = $worksheet->toArray();
        $headersFromCSV = array_shift($data);
        $this->headerFieldsValidation($headersFromCSV);

        if ($this->exelErrrorsRaport) {
            return [
                'exelErrrorsRaport' => $this->exelErrrorsRaport
            ];
        }

        foreach ($data as $row) {
            $this->countAllItems++;
            $productData = array_combine($headersFromCSV, $row);
            $productDataDTO = new ProductDataDTO($productData);

            if ($productDataDTO->hasMissingFields()) {
                $this->reportFailItems[] = $productDataDTO->toArray();
                if ($this->showMFR) {
                    $this->missingFieldsRaport[] = $productDataDTO->getMissingFields();
                }
                continue;
            }

            // Validation and filters
            // Don't import products: that are too cheap, have too few items in stock, or are too expensive
            if ($productDataDTO->decCostInGBP < 5 || $productDataDTO->intStock < 10 || $productDataDTO->decCostInGBP > 1000) {
                $this->reportFailItems[] = $productDataDTO->toArray();
                continue;
            }
            $this->saveProductData($productDataDTO);
        }

        return [
            'countAllItems' => $this->countAllItems,
            'countSuccessImport' => $this->countSuccessImport,
            'reportFailItems' => $this->reportFailItems,
            'missingFieldsRaport' => $this->missingFieldsRaport
        ];
    }

    /**
     * Header fields validation - check if we have all necessary field
     *
     * @param array $headersFromCSV - headers from CSV file
     * @return void
     */
    private function headerFieldsValidation(array $headersFromCSV)
    {
        foreach ($this->headers as $key => $field) {
            if ($headersFromCSV[$key] != $field) {
                $this->exelErrrorsRaport['missing_headers'][] = $field;
            }
        }
    }

    /**
     * Add new record to table
     *
     * @param ProductDataDTO $productDataDTO
     * @return void
     */
    private function saveProductData(ProductDataDTO $productDataDTO)
    {
        DB::beginTransaction();
        try {
            ProductDataWrite::create($productDataDTO->toArray());
            $this->countSuccessImport++;

        } catch (\Exception $e) {
//            add more error info - not finished idea
//            $failedImports[] = [
//                'error' => $e->getMessage(),
//            ];
        }
        if ($this->testMode) {
            DB::rollBack(); // We rollback the transaction - simulate the save mode
        } else {
            DB::commit(); // We approve the transaction
        }
    }

}
