<?php

require_once '../../app/Mage.php'; umask(0); Mage::app('default');

class HelperDataTest extends PHPUnit_Framework_TestCase
{
    public function testAssert()
    {
        $helper = Mage::helper('taxify');
        $options = $helper->getCustomerTaxabilityOptions();
        $expected = array(
            '' => 'Default',
            'R' => 'Reseller / Wholesale',
            'NON' => 'Tax Exempt',
        );
        $this->assertEquals($expected, $options);
    }
}
