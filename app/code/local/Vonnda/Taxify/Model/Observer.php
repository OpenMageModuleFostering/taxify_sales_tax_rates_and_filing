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

        // Swap out Magento tax calc with our own (this is per store, see above)
        Mage::getConfig()->setNode('global/sales/quote/totals/tax/class',
            'vonnda_taxify_model_sales_quote_address_total_tax');
    }

    public function salesOrderPlaceAfter(Varien_Event_Observer $observer)
    {
        if (!$this->isEnabled()) {
            return;
        }

        // Send final commit tax call to Taxify after order is created
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

        // Send cancel tax call to Taxify after order is cancelled
        $order = $observer->getEvent()->getPayment()->getOrder();
        $cancel = Mage::getModel('taxify/request_cancel');
        $cancel->loadOrder($order);
        $cancel->send();
    }
}
