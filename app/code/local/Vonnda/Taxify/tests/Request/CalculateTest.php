<?php

require_once '../../app/Mage.php'; umask(0); Mage::app('default');

class CalculateTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->calculateSuccess = unserialize(file_get_contents(__DIR__. '/../fixtures/calculateSuccess.txt'));
        $this->calculateMultiSimpleProductSuccess = unserialize(file_get_contents(__DIR__. '/../fixtures/calculateMultiSimpleProductSuccess.txt'));
    }


    public function testCreateLineFromMageItem()
    {
        $calculate = $this->getMock('Vonnda_Taxify_Model_Request_Calculate', array('getItemTaxabilityCode'));
        $calculate->expects($this->any())
            ->method('getItemTaxabilityCode')
            ->will($this->returnValue(''));

        $item = $this->getMock('item', array('getItemId', 'getSku', 'getBaseRowTotal', 
        'getQtyOrdered', 'getData', 'getName'));

        $item->expects($this->any())
            ->method('getItemId')
            ->will($this->returnValue(123));

        $item->expects($this->any())
            ->method('getSku')
            ->will($this->returnValue('abc123'));

        $item->expects($this->any())
            ->method('getData')
            ->with($this->equalTo('qty'))
            ->will($this->returnValue('1.0000'));

        $item->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('This is a foo'));

        $resp = $calculate->createLineFromMageItem($item);
        $this->assertEquals(123, $resp['LineNumber']);
        $this->assertEquals('abc123', $resp['ItemKey']);
        $this->assertEquals('1', $resp['Quantity']);
        $this->assertEquals('This is a foo', $resp['ItemDescription']);
    }

    public function testGetTaxByLineNumber()
    {
        $calculate = Mage::getModel('taxify/request_calculate');
        $calculate->response = $this->calculateSuccess;
        $resp = $calculate->getTaxByLineNumber(2539);

        $this->assertEquals(8.75, $resp['rate']);
        $this->assertEquals(39.375, $resp['amount']);
    }

    public function testGetTaxByLineNumberWithMultipleProducts()
    {
        $calculate = Mage::getModel('taxify/request_calculate');
        $calculate->response = $this->calculateMultiSimpleProductSuccess;
        $resp = $calculate->getTaxByLineNumber(2543);

        $this->assertEquals(8.75, $resp['rate']);
        $this->assertEquals(25.8125, $resp['amount']);
    }

    public function testItemTaxabilityCode()
    {
        $calculate = Mage::getModel('taxify/request_calculate');

        $product = $this->getMock('product', array('getTaxClassId'));
        $product->method('getTaxClassId')
            ->will($this->returnValue(6));

        $item = $this->getMock('item', array('getProduct'));
        $item->method('getProduct')
            ->will($this->returnValue($product));

        $resp = $calculate->getItemTaxabilityCode($item);

        $this->assertEquals('NON', $resp);


        $calculate = Mage::getModel('taxify/request_calculate');

        $product = $this->getMock('product', array('getTaxClassId'));
        $product->method('getTaxClassId')
            ->will($this->returnValue(0));

        $item = $this->getMock('item', array('getProduct'));
        $item->method('getProduct')
            ->will($this->returnValue($product));

        $resp = $calculate->getItemTaxabilityCode($item);

        $this->assertEquals('NON', $resp);
    }

}
