<?php

class Vonnda_Taxify_Model_Request_Commit extends Vonnda_Taxify_Model_Request_Request
{

    public $apiMethod = 'CommitTax';

    public function build()
    {
        $this->request['DocumentKey'] = 'q-'. $this->order->getQuoteId();
        $this->request['CommitedDocumentKey'] = $this->order->getIncrementId();
    }
}
