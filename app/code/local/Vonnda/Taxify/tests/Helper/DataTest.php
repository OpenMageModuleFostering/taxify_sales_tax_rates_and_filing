<?php

class HelperDataTest extends PHPUnit_Framework_TestCase
{
    public function testAssert()
    {
        $helper = Mage::helper('taxify');
        $options = $helper->getCustomerTaxabilityOptions();
        $expected = array(
            '' => 'None',
            'RESALE' => 'Resale',
            'RETAIL' => 'Retail',
            'USETAX' => 'Usetax',
        );
        $this->assertEquals($expected, $options);
    }

}
