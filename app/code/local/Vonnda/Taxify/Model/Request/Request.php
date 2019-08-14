<?php

class Vonnda_Taxify_Model_Request_Request extends Mage_Core_Model_Abstract
{

    public $order;
    public $request = array();
    public $response;
    public $client;
    public $apiMethod = '';

    public function __construct()
    {
        $this->client = Mage::getModel('taxify/client');
    }

    public function loadOrder($order)
    {
        $this->order = $order; 
    }

    public function loadQuote($quote)
    {
        $this->quote = $quote;
    }

    public function getMageModel()
    {
        if ($this->order) {
            return $this->order;
        } else {
            return $this->quote;
        }
    }

    public function build()
    {
    }

    public function send()
    {
        $this->build();
        $this->response = $this->client->request($this->apiMethod, $this->request);

        return $this->response;
    }

    public function splitAddr($addr)
    {
        $splitAddr = explode(PHP_EOL, $addr);
        if (!isset($splitAddr[1])) {
            $splitAddr[1] = '';
        }

        return $splitAddr;
    }

    public function getRegionCode()
    {
        $shippingAddr = $this->getMageModel()->getShippingAddress();
        $regionId = $shippingAddr->getData('region_id');
        $region = Mage::getModel('directory/region')->load($regionId);

        return $region->getCode();
    }

}
