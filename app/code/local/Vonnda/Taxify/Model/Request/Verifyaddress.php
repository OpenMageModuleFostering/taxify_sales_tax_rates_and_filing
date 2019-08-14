<?php

class Vonnda_Taxify_Model_Request_Verifyaddress extends Vonnda_Taxify_Model_Request_Request
{

    public $apiMethod = 'VerifyAddress';

    public function build()
    {
        $shippingAddr = $this->order->getShippingAddress();
        $addrParts = $this->splitAddr($shippingAddr->getData('street'));
        $this->request['Street1'] = $addrParts[0];
        $this->request['Street2'] = $addrParts[1];
        $this->request['City'] = $shippingAddr->getData('city');
        $this->request['Region'] = $this->getRegionCode();
        $this->request['PostalCode'] = $shippingAddr->getData('postcode');
        $this->request['Country'] = $shippingAddr->getData('country_id');
    }

}
