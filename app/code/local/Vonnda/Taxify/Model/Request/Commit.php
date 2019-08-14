<?php

// Responsible for creating a taxify CommitTax API call
// This is typically called after the order is placed
class Vonnda_Taxify_Model_Request_Commit extends Vonnda_Taxify_Model_Request_Request
{

    public $apiMethod = 'CommitTax';

    public function build()
    {
        $this->request['DocumentKey'] = 'q-'. $this->order->getQuoteId();
        $this->request['CommitedDocumentKey'] = $this->order->getIncrementId();
    }
}
