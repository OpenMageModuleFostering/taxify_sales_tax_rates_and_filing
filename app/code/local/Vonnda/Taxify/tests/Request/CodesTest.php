<?php

class CodesTest extends PHPUnit_Framework_TestCase
{

    public function testCodesModelExists()
    {
        $client = Mage::getModel('taxify/request_codes');
        $this->assertTrue(
            is_object($client),
            'The model taxify/request_codes should exist'
        );
    }

    public function testBuildRequest()
    {
        $client = Mage::getModel('taxify/request_codes');
        $expected = array('GetCodes' => array('CodeType' => 'Item'));

        $client->build();

        $this->assertEquals($expected, $client->request);
    }

    public function testGetCodes()
    {
        $client = Mage::getModel('taxify/request_codes');
        $resp = new stdClass;
        $resp->GetCodesResult->Codes->string = array('foo', 'bar');
        $client->response = $resp;

        $client->getCodes();

        $this->assertEquals(array('', 'foo', 'bar'), $client->getCodes());
    }

    public function testGetCodesWithLabels()
    {
        $client = Mage::getModel('taxify/request_codes');
        $resp = new stdClass;
        $resp->GetCodesResult->Codes->string = array('foo', 'bar');
        $client->response = $resp;
        $expected = array(
            '' => 'Default',
            'foo' => 'Foo',
            'bar' => 'Bar'
        );

        $client->getCodesWithLabels();

        $this->assertEquals($expected, $client->getCodesWithLabels());
    }

    public function testGetCustomerCodes()
    {
        $client = Mage::getModel('taxify/request_codes');
        $client->codeType = 'Customer';
        $expected = array('GetCodes' => array('CodeType' => 'Customer'));

        $client->build();

        $this->assertEquals($expected, $client->request);
    }
}
