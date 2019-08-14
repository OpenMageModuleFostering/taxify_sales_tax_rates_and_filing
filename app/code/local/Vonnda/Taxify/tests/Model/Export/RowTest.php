<?php

require_once '../../app/Mage.php'; umask(0); Mage::app('default');

class RowTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->model = Mage::getModel('taxify/export_row');
    }

    public function testModelExists()
    {
        $this->assertEquals('object', gettype($this->model));
    }

    public function testModelHasMap()
    {
        $row = Mage::getModel('taxify/export_row');
        $expected = array(
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

        $this->assertEquals($expected, $row->map);
    }

    public function testIn()
    {
        $queryRow = array (
          'increment_id' => '100000049',
          'created_at' => '2013-03-14 17:01:34',
          'customer_id' => NULL,
          'customer_lastname' => 'Akizian',
          'customer_firstname' => 'Mosses',
          'customer_email' => 'mosses@ebay.com',
          'street' => '10441 Jefferson Blvd., Suite 200',
          'city' => 'Culver City',
          'region' => 'California',
          'postcode' => '90232',
          'sku' => 'abl007',
          'name' => 'Classic Hardshell Suitcase 29',
          'price' => '750.0000',
          'qty_invoiced' => '0.0000',
        );

        $expected = array (
          'transaction_number' => '100000049',
          'transaction_date' => '2013-03-14 17:01:34',
          'customer_number' => NULL,
          'last_name' => 'Akizian',
          'first_name' => 'Mosses',
          'email' => 'mosses@ebay.com',
          'city' => 'Culver City',
          'state' => 'California',
          'zip' => '90232',
          'sku' => 'abl007',
          'description' => 'Classic Hardshell Suitcase 29',
          'unit_price' => '750.0000',
          'quantity' => '0.0000',
          'address' => '10441 Jefferson Blvd., Suite 200',
          'address2' => '',
          'tax_collected_type' => '',
        );

        $this->model->in($queryRow);
        $resp = $this->model->row;

        $this->assertEquals($expected, $resp);
    }

    public function testMultipleAddressLines()
    {
        $queryRow = array (
          'increment_id' => '145000002',
          'created_at' => '2015-02-17 20:33:41',
          'customer_id' => '142',
          'customer_lastname' => 'Sanborn',
          'customer_firstname' => 'Mark',
          'customer_email' => 'mark.sanborn@vonnda.com',
          'street' => '660 York St
Suite 202',
          'city' => 'San Francisco',
          'region' => 'California',
          'postcode' => '94115',
          'sku' => 'msj003xs',
          'name' => 'Slim fit Dobby Oxford Shirt',
          'price' => '140.0000',
          'qty_invoiced' => '0.0000',
        );

        $this->model->in($queryRow);
        $resp = $this->model->row;

        $this->assertEquals('660 York St', $resp['address']);
        $this->assertEquals('Suite 202', $resp['address2']);
    }
}
