<?php

class Vonnda_Taxify_Model_Config extends Mage_Core_Model_Abstract
{

    public function getApiUrl()
    {
        if ($this->isTestMode()) {
            return 'https://ws-dev.shipcompliant.com/taxify/1.0/core/service.asmx';
        }

        return 'https://ws.taxify.co/taxify/1.0/core/service.asmx';
    }

    public function isEnabled()
    {
        return Mage::getStoreConfigFlag('tax/taxify/enabled');
    }

    // This is now the API Key
    public function getApiPassword()
    {
        return Mage::getStoreConfig('tax/taxify/password');
    }

    public function getApiUsername()
    {
        // We no longer provide username, just api key as password
        return '';
    }

    public function isTestMode()
    {
        return Mage::getStoreConfigFlag('tax/taxify/test_mode');
    }

}
