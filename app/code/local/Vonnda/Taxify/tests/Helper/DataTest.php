<?php

class HelperDataTest extends PHPUnit_Framework_TestCase
{
    public function testAssert()
    {
        $helper = Mage::helper('taxify');
        $options = $helper->getCustomerTaxabilityOptions();
        $expected = array(
            '' => 'Default',
            'CLOTHING' => 'Clothing',
            'FOOD' => 'Food',
            'FREIGHT' => 'Freight',
            'NONTAX' => 'Nontax',
            'TAXABLE' => 'Taxable',
            'WINE' => 'Wine',
        );
        $this->assertEquals($expected, $options);
    }
}
