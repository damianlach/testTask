<?php

namespace App\Models\Command;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDataWrite extends Model
{
    use HasFactory;

    protected $table = 'tbl_product_data';

    // We turn off timestamps because this table has its own time fields
    public $timestamps = false;

    // We define which fields are filled (those that can be updated or inserted)
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

    // Primary key
    protected $primaryKey = 'intProductDataId';

    // If the key is not automatically incremented
    public $incrementing = true;

    //  Master Key Type
    protected $keyType = 'int';
}
