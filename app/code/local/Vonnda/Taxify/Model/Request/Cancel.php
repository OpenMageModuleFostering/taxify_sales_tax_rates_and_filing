<?php

class Vonnda_Taxify_Model_Request_Cancel extends Vonnda_Taxify_Model_Request_Request
{

    public $apiMethod = 'CancelTax';

    public function build()
    {
        $this->request['DocumentKey'] = $this->order->getIncrementId();
    }

}
