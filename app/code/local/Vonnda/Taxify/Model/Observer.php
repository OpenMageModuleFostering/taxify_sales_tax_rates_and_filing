<?php

class Vonnda_Taxify_Model_Observer
{

    public function __construct()
    {
        $this->config = Mage::getModel('taxify/config');
    }

    public function isEnabled($storeId=null)
    {
        return $this->config->isEnabled($storeId);
    }

    public function quoteCollectTotalsBefore(Varien_Event_Observer $observer)
    {
        $storeId = $observer->getEvent()->getQuote()->getStoreId();
        if (!$this->isEnabled($storeId)) {
            return;
        }

        Mage::getConfig()->setNode('global/sales/quote/totals/tax/class',
            'vonnda_taxify_model_sales_quote_address_total_tax');
    }

    public function salesOrderPlaceAfter(Varien_Event_Observer $observer)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $order = $observer->getEvent()->getOrder();
        $commit = Mage::getModel('taxify/request_commit');
        $commit->loadOrder($order);
        $commit->send();
    }

    public function salesOrderCancelAfter(Varien_Event_Observer $observer)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $order = $observer->getEvent()->getPayment()->getOrder();
        $cancel = Mage::getModel('taxify/request_cancel');
        $cancel->loadOrder($order);
        $cancel->send();
    }
}
