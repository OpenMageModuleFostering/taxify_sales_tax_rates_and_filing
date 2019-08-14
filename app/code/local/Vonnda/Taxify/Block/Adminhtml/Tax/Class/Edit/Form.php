<?php

class Vonnda_Taxify_Block_Adminhtml_Tax_Class_Edit_Form
    extends Mage_Adminhtml_Block_Tax_Class_Edit_Form
{

    protected function _prepareForm()
    {
        parent::_prepareForm();

        $classType  = $this->getClassType();
        if ($classType == Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER) {
            $fieldset = $this->getForm()->getElement('base_fieldset');
            $model  = Mage::registry('tax_class');
            $fieldset->addField('taxify_code', 'select', array(
                'name'     => 'taxify_customer_taxability',
                'label'    => Mage::helper('taxify')->__('Taxify Tax Class'),
                'required' => true,
                'values'   => Mage::helper('taxify')->getCustomerTaxabilityOptions(),
                'value'    => $model->getTaxifyCustomerTaxability(),

            ));
        } else {
            $fieldset = $this->getForm()->getElement('base_fieldset');
            $model  = Mage::registry('tax_class');
            $fieldset->addField('taxify_code', 'select', array(
                'name'     => 'taxify_product_taxability',
                'label'    => Mage::helper('taxify')->__('Taxify Tax Class'),
                'required' => true,
                'values'   => Mage::helper('taxify')->getProductTaxabilityOptions(),
                'value'    => $model->getTaxifyProductTaxability(),

            ));
        }

        return $this;
    }
}
