<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDataRead extends Model
{
    use HasFactory;

    protected $table = 'tbl_product_data';

    protected $fillable = [
        'intProductDataId',
        'strProductName',
        'strProductDesc',
        'strProductCode',
        'intStock',
        'decCostInGBP',
        'dtmAdded',
        'dtmDiscontinued',
        'stmTimestamp',
    ];

    // We turn off automatic timestamps because the table has its own
    public $timestamps = false;

//    Product download function
    public function getActiveProducts()
    {
//        return $this->whereNull('dtmDiscontinued')->get();
    }
}
