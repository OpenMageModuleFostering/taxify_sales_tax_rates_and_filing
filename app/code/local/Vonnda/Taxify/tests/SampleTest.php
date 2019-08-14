<?php

require_once '../../app/Mage.php'; umask(0); Mage::app('default');

class ClientTest extends PHPUnit_Framework_TestCase
{
    public function testAssert()
    {
        $client = Mage::getModel('taxify/client');
        $this->assertTrue(true);
    }
}
