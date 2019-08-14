<?php

require_once '../../app/Mage.php'; umask(0); Mage::app('default');

class CancelTest extends PHPUnit_Framework_TestCase
{

    public function testAssert()
    {
        $client = Mage::getModel('taxify/commit');
        $this->assertTrue(true);
    }

}
