<?php

namespace Tests\Unit;

use App\Models\Command\ProductDataWrite;
use App\Services\CSVImportService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Nette\Schema\Schema;
use Tests\TestCase;
use org\bovigo\vfs\vfsStream;
use Mockery;

class CSVImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private $csvImportService;

    private $root;

    private $headers = ['Product Code', 'Product Name', 'Product Description', 'Stock', 'Cost in GBP', 'Discontinued'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->csvImportService = new CSVImportService(true, false, $this->headers);
        $this->root = vfsStream::setup('root');
    }


    public function testImportCSVWithOnlyValidData()
    {
        $fileContent = <<<CSV
        Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued
        P0001,TV,32 Tv,10,399.99,yes
        P0002,Cd Player,Nice CD player,11,50.12,yes
        P0003,VCR,Top notch VCR,12,39.33,yes
        CSV;

        $file = vfsStream::newFile('valid.csv')->at($this->root)->setContent($fileContent);
        $filePath = $file->url();
        $result = $this->csvImportService->importCSV($filePath);

        $this->assertEquals(3, $result['countAllItems']);
        $this->assertEquals(3, $result['countSuccessImport']);
        $this->assertCount(0, $result['reportFailItems']);
        $this->assertCount(0, $result['missingFieldsRaport']);
    }

    public function testImportCSVWithInvalidData()
    {
        $fileContent = <<<CSV
        Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued
        P0001,TV,32 Tv,2,399.99,yes
        P0002,Cd Player,Nice CD player,11,50.12,yes
        P0003,VCR,Top notch VCR,12,1.33,yes
        CSV;

        $file = vfsStream::newFile('valid.csv')->at($this->root)->setContent($fileContent);
        $filePath = $file->url();
        $result = $this->csvImportService->importCSV($filePath);


        $this->assertEquals(3, $result['countAllItems']);
        $this->assertEquals(1, $result['countSuccessImport']);
        $this->assertCount(2, $result['reportFailItems']);
    }

    public function testImportCSVWithInvalidDataWithMissingFieldsRaport()
    {
        $fileContent = <<<CSV
        Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued
        P0001,VCR,VHS rules,3,23,yes
        P0001,Bluray Player,Excellent picture,32,$4.33,
        P0003,Bluray Player,Excellent picture,32,4.33,
        P0004,24â€ť Monitor,Visual candy,3,45,
        CSV;

        $file = vfsStream::newFile('valid.csv')->at($this->root)->setContent($fileContent);
        $filePath = $file->url();
        $csvImportService = new CSVImportService(true, true, $this->headers);
        $result = $csvImportService->importCSV($filePath);


        $this->assertEquals(4, $result['countAllItems']);
        $this->assertEquals(0, $result['countSuccessImport']);
        $this->assertCount(4, $result['reportFailItems']);
        $this->assertCount(1, $result['missingFieldsRaport']);
    }

    public function  testImportCSVWithInvalidColumnNames()
    {
        $fileContent = <<<CSV
        Code,Name,Description,Stocks,Cost,Discontinued date
        P0001,TV,32 Tv,10,399.99,yes
        P0002,Cd Player,Nice CD player,11,50.12,yes
        P0003,VCR,Top notch VCR,12,39.33,yes
        CSV;

        $file = vfsStream::newFile('valid.csv')->at($this->root)->setContent($fileContent);
        $filePath = $file->url();
        $csvImportService = new CSVImportService(true, true, $this->headers);
        $result = $csvImportService->importCSV($filePath);

        $this->assertCount(6, $result['exelErrrorsRaport']['missing_headers']);
    }
}
