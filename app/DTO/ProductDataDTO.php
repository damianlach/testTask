<?php

namespace App\DTO;

use Carbon\Carbon;

class ProductDataDTO
{
    public string $strProductName;
    public string $strProductDesc;
    public string $strProductCode;
    public int $intStock;
    public float $decCostInGBP;
    public ?Carbon $dtmDiscontinued;

    /**
     * @var array Table storing missing fields
     */
    public array $missingFields = [];

    /**
     * @param array $productData
     */
    public function __construct(array $productData)
    {
        $headers = array_keys($productData);

        $this->strProductCode = $this->getField($productData, $headers[0]);  // def. Product Code
        $this->strProductName = $this->getField($productData, $headers[1]); // def. Product Name
        $this->strProductDesc = $this->getField($productData, $headers[2]); // def. Product Description
        $this->intStock = $this->getField($productData, $headers[3], true); // def. Stock
        $this->decCostInGBP = $this->getField($productData, $headers[4], true); // def. Cost in GBP
        $this->dtmDiscontinued = (isset($productData[$headers[5]]) && $productData[$headers[5]] === 'yes') ? Carbon::now() : null; // Discontinued
    }

    /**
     * Method to return a field or record a missing field
     *
     * @param array $data
     * @param string $field
     * @param bool $isNumeric
     * @return float|int|mixed|string
     */
    private function getField(array $data, string $field, bool $isNumeric = false)
    {
        $ProductCode = array_key_first($data);
        if (!isset($data[$field])) {
            $this->missingFields[$ProductCode]['missing fields'][] = $field;

            return $isNumeric ? 0 : '';  // You can return a default value
        }
        return $isNumeric ? $this->validateNumeric($data[$field], $ProductCode, $field) : $data[$field];
    }

    /**
     * Validation of numeric values
     *
     * @param string $value
     * @param string $code
     * @param string $field
     * @return float|int|string
     */
    private function validateNumeric(string $value, string $code, string $field)
    {
        if (!is_numeric($value)) {
            $this->missingFields[$code]['Invalid numeric value'][] = $field . ' = ' . $value;

            return 0;
        }
        return $value;
    }

    /**
     * Convert object to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'strProductName' => $this->strProductName,
            'strProductDesc' => $this->strProductDesc,
            'strProductCode' => $this->strProductCode,
            'intStock' => $this->intStock,
            'decCostInGBP' => $this->decCostInGBP,
            'dtmDiscontinued' => $this->dtmDiscontinued ? $this->dtmDiscontinued->toDateTimeString() : null,
        ];
    }

    /**
     * Method to check if there are missing fields
     * @return bool
     */
    public function hasMissingFields(): bool
    {
        return !empty($this->missingFields);
    }

    /**
     * Method to return missing fields
     *
     * @return array
     */
    public function getMissingFields(): array
    {
        return $this->missingFields;
    }

}
