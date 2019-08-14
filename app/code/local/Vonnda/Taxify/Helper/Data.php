<?php

class Vonnda_Taxify_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getCustomerTaxabilityOptions()
    {
        return array(
            '' => 'Default',
            'R' => 'Reseller / Wholesale',
            'NON' => 'Tax Exempt',
        );
    }

    public function getTaxifyCustomerTaxability($customerId)
    {
        $customer = Mage::getModel('customer/customer')->load($customerId);
        if (!$customer->getId()) {
            return '';
        }

        $taxId = $customer->getTaxClassId();
        $tax = Mage::getModel('tax/class')->load($taxId);

        return $tax->getTaxifyCustomerTaxability();
    }

    public function getTaxifyCustomerTaxabilityFromGroup($groupId)
    {
        $group = Mage::getModel('customer/group')->load($groupId);
        if (!$group->getTaxClassId()) {
            return '';
        }

        $tax = Mage::getModel('tax/class')->load($group->getTaxClassId());

        return $tax->getTaxifyCustomerTaxability();
    }
}
