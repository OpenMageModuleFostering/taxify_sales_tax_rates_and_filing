<?php

$installer = $this;
$installer->startSetup();
try {
    $installer->run("
    
    ALTER TABLE `" . $this->getTable('tax/tax_class') . "` 
        ADD `taxify_product_taxability` VARCHAR(255) DEFAULT '' NOT NULL COMMENT 'Used by Vonnda Taxify extension' 
        AFTER `taxify_customer_taxability`;
    
    ");
} catch(Exception $e) {
    Mage::log($e->getMessage());
}

$installer->endSetup();
