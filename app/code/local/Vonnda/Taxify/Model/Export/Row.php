<?php

class Vonnda_Taxify_Model_Export_Row extends Mage_Core_Model_Abstract
{
    public $map = array();
    public $queryRow = array();
    public $row = array();

    public function __construct()
    {
        parent::__construct();

        $this->map = array(
            'increment_id' => 'transaction_number',
            'taxability_code' => 'transaction_type',
            'created_at' => 'transaction_date',
            'customer_id' => 'customer_number',
            'customer_lastname' => 'last_name',
            'customer_firstname' => 'first_name',
            '' => 'company',
            'customer_email' => 'email',
            '' => 'phone',
            '' => 'address',
            '' => 'address2',
            'city' => 'city',
            'region' => 'state',
            'postcode' => 'zip',
            '' => 'item_type',
            'sku' => 'sku',
            'name' => 'description',
            'price' => 'unit_price',
            'qty_invoiced' => 'quantity',
            'tax_amount' => 'tax_collected',
        );
    }

    public function splitStreet()
    {
        $parts = explode("\n", $this->queryRow['street']);
        $this->row['address'] = $parts[0];
        if (isset($parts[1])) {
            $this->row['address2'] = $parts[1];
        } else {
            $this->row['address2'] = '';
        }
        unset($this->row['street']);
    }

    public function in($queryRow)
    {
        $this->queryRow = $queryRow;
        $this->mapValues();
        $this->splitStreet();
        $this->mapItemTaxabilityCode();
        $this->removeUnwantedColumns();
    }

    public function removeUnwantedColumns()
    {
        unset($this->row['tax_class_id']);
        unset($this->row['product_id']);
    }

    public function mapValues()
    {
        $this->row = array();
        foreach ($this->queryRow as $key => $value) {
            $newKey = $key;
            if (isset($this->map[$key])) {
                $newKey = $this->map[$key];
            }
            $this->row[$newKey] = $value;
        }
    }

    public function out()
    {
        return $this->row;
    }

    public function mapItemTaxabilityCode()
    {
        if (!isset($this->row['tax_class_id'])) {
            $this->row['tax_collected_type'] = '';
            return;
        }

        $taxClassId = $this->row['tax_class_id'];

        $map = array('0' => 'NON', '6' => 'NON');

        if (isset($map[$taxClassId])) {
            $this->row['tax_collected_type'] = $map[$taxClassId];
            return;
        }

        $this->row['tax_collected_type'] = '';
    }

}
