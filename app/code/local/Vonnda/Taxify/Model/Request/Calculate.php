<?php

class Vonnda_Taxify_Model_Request_Calculate extends Vonnda_Taxify_Model_Request_Request 
{

    public $apiMethod = 'CalculateTax';
    public $isCommited = 'false';

    public function formatCurrency($amount)
    {
        return number_format($amount, 2);
    }

    public function getShippingAmount()
    {
        $shippingAddr = $this->getMageModel()->getShippingAddress();
        if (!$shippingAddr) {
            return 0.00;
        }

        return $this->formatCurrency($shippingAddr->getShippingAmount());
    }

    public function buildDestinationAddress()
    {
        $shippingAddr = $this->getMageModel()->getShippingAddress();

        $addrParts = $this->splitAddr($shippingAddr->getData('street'));

        $addr = array();
        $addr['FirstName'] = $shippingAddr->getData('firstname');
        $addr['LastName'] = $shippingAddr->getData('lastname');
        $addr['Company'] = $shippingAddr->getData('company');
        $addr['Street1'] = $addrParts[0];
        $addr['Street2'] = $addrParts[1];
        $addr['City'] = $shippingAddr->getData('city');
        $addr['Region'] = $this->getRegionCode();
        $addr['PostalCode'] = $shippingAddr->getData('postcode');
        $addr['Country'] = $shippingAddr->getData('country_id');
        $addr['Email'] = $shippingAddr->getData('email');
        $addr['Phone'] = $shippingAddr->getData('telephone');

        $this->request['DestinationAddress'] = $addr;
    }

    public function getItemTaxabilityCode($item)
    {
        $taxClassId = $item->getProduct()->getTaxClassId();

        $map = array('0' => 'NON', '6' => 'NON');

        if (isset($map[$taxClassId])) {
            return $map[$taxClassId];
        }

        return '';
    }

    public function createLineFromMageItem($item)
    {
        $line = array();
        $line['LineNumber'] = $item->getItemId();
        $line['ItemKey'] = $item->getSku();
        $line['ActualExtendedPrice'] = number_format($item->getBaseRowTotal(), 2, '.', '');
        if ($item->getQtyOrdered()) {
            $line['Quantity'] = $item->getQtyOrdered();
        } else {
            $line['Quantity'] = $item->getData('qty');
        }
        $line['ItemDescription'] = $item->getName();

        // * (blank) : Taxify will assume the item is generally taxable "tangible goods" unless
        //   otherwise configured (by ItemKey) in Taxify
        // * "NON" : a generally non taxable item or service
        // * "FR" : Shipping - it is important to identify shipping chrged with this tax code as
        //   taxability of shipping differs state to state
        $line['ItemTaxabilityCode'] = $this->getItemTaxabilityCode($item);

        return $line;
    }

    public function isRowItemBundle($item)
    {
        if ($item->getProductType() == 'bundle') {
            return true;
        }

        return false;
    }

    public function isRowItemFixedBundle($item)
    {
        if (!$this->isRowItemBundle($item)) {
            return false;
        }

        if ($item->getProduct()->getPriceType() === '1') {
            return true;
        }

        return false;
    }

    public function isRowItem($item)
    {
        if ($this->isRowItemBundle($item)) {
            if ($this->isRowItemFixedBundle($item)) {
                return true;
            }

            return false;
        }

        if ($item->getData('base_row_total') == 0.00) {
            return false;
        }

        return true;
    }

    public function buildShipmentLineItem()
    {
        $line = array();
        $line['LineNumber'] = 0;
        $line['ItemKey'] = 'SHIPPING';
        $line['ActualExtendedPrice'] = $this->getShippingAmount();
        $line['Quantity'] = 1;
        $line['ItemDescription'] = 'Shipping';
        $line['ItemTaxabilityCode'] = 'FR';

        return $line;
    }

    public function buildOrderLineItems()
    {
        $lines = array();
        foreach ($this->getMageModel()->getAllItems() as $item) {
            if ($this->isRowItem($item) == false) {
                continue;
            }

            $lines[] = $this->createLineFromMageItem($item);
        }
        $lines[] = $this->buildShipmentLineItem();

        return $lines;
    }

    private function isShippingItem($item)
    {
        if ($item['LineNumber'] == 0 || $item['ItemKey'] == 'SHIPPING') {
            return true;
        }

        return false;
    }

    private function numNonShippingItems($items)
    {
        $count = 0;
        foreach ($items as $item) {
            if (!$this->isShippingItem($item)) {
                $count = $count + 1;
            }
        }

        return $count;
    }

    public function spreadDiscountToItems($items, $discountAmount)
    {
        // No items, no discount spreading
        if ($this->numNonShippingItems($items) < 1) {
            return $items;
        }

        $pennies = (int) $discountAmount * 100;
        while ($pennies > 0) {
            foreach ($items as $index => $item) {
                if ($this->isShippingItem($item)) {
                    continue; // Don't subtract discount from shipping line items
                }
                $items[$index]['ActualExtendedPrice'] = number_format($items[$index]['ActualExtendedPrice'] - 0.01, 2);
                if ($items[$index]['ActualExtendedPrice'] == 0) {
                    $pennies = 0; // Stop, can't give a negative price on products
                }
                $pennies = $pennies - 1;
            }
        }

        return $items;
    }

    public function getTotalDiscount()
    {
        if (!$this->getMageModel()) {
            return 0;
        }

        $totals = $this->getMageModel()->getTotals();
        if (!$totals) {
            return 0;
        }
        $discount = 0;
        foreach ($totals as $total) {
            if ($total->getValue() < 0) {
                $discount = $discount + abs($total->getValue());
            }
        }

        return $discount;
    }

    public function build()
    {
        if ($this->order) {
            $this->request['DocumentKey'] = $this->order->getIncrementId();
        } else {
            $this->request['DocumentKey'] = 'q-'. $this->getMageModel()->getId();
        }

        $originalTimeZone = date_default_timezone_get();

        date_default_timezone_set('America/Los_Angeles');
        $this->request['TaxDate'] = date('Y-m-d');
        date_default_timezone_set($originalTimeZone);

        $this->request['Lines']['TaxRequestLine'] = $this->spreadDiscountToItems($this->buildOrderLineItems(), $this->getTotalDiscount());
        $this->buildDestinationAddress();
        $this->request['IsCommited'] = false;
        $this->request['CustomerKey'] = $this->getMageModel()->getCustomerId();

        // (blank) : default, consumer
        // "NON" : tax exempt customer – sales tax will not be charged
        // "R" : reseller / wholesale transaction - sales tax will not be charged
        $this->request['CustomerTaxabilityCode'] = Mage::helper('taxify')->getTaxifyCustomerTaxabilityFromGroup($this->getMageModel()->getCustomerGroupId());

        return $this->request;
    }

    public function convertPercentRate($percent)
    {
        return $percent * 100;
    }

    public function getTaxByLineNumber($lineNumber)
    {
        $tax = array(
            'amount' => 0.00,
            'rate' => '0',
        );
        if (!$this->response) {
            return $tax;
        }

        if (!isset($this->response->CalculateTaxResult->TaxLineDetails->TaxLineDetail)) {
            return $tax;
        }

        if (is_array($this->response->CalculateTaxResult->TaxLineDetails->TaxLineDetail)) {
            foreach ($this->response->CalculateTaxResult->TaxLineDetails->TaxLineDetail as $line) {
                if (!isset($line->LineNumber)) {
                    continue;
                }

                if ($line->LineNumber == $lineNumber) {
                    $tax['amount'] = $line->SalesTaxAmount;
                    $tax['rate'] = $this->convertPercentRate($line->TaxRate);
                }
            }
        }

        if (isset($this->response->CalculateTaxResult->TaxLineDetails->TaxLineDetail->LineNumber)) {
            if ($this->response->CalculateTaxResult->TaxLineDetails->TaxLineDetail->LineNumber != $lineNumber) {
                return $tax;
            }
        }

        if (isset($this->response->CalculateTaxResult->TaxLineDetails->TaxLineDetail->TaxRate)) {
            $tax['rate'] = $this->convertPercentRate($this->response->CalculateTaxResult->TaxLineDetails->TaxLineDetail->TaxRate);
        }

        if (isset($this->response->CalculateTaxResult->TaxLineDetails->TaxLineDetail->SalesTaxAmount)) {
            $tax['amount'] = $this->response->CalculateTaxResult->TaxLineDetails->TaxLineDetail->SalesTaxAmount;
        }

        return $tax;
    }

}
