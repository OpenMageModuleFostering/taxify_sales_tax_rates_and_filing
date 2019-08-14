<?php

$installer = $this;
$installer->startSetup();
try {
    $installer->run("
    
    ALTER TABLE `" . $this->getTable('tax/tax_class') . "` 
        ADD `taxify_customer_taxability` VARCHAR(255) DEFAULT '' NOT NULL COMMENT 'Used by Vonnda Taxify extension' 
        AFTER `class_name`;
    
    ");
} catch(Exception $e) { Mage::log($e->getMessage()); }

// Set Wholesale Customer tax class to 'R' if
// same ID and description as default Magento.
$tax = Mage::getModel('tax/class')->load(5);
if ($tax) {
    if ($tax->getClassName() == 'Wholesale Customer') {
        $tax->setTaxifyCustomerTaxability('R');
        $tax->save();
    }
}

$installer->endSetup();
