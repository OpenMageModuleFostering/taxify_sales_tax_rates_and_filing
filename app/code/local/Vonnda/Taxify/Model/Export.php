<?php

class Vonnda_Taxify_Model_Export extends Mage_Core_Model_Abstract
{

    public $pageSize;
    public $cursor;
    public $orderStatuses = array();
    public $from = '1980-01-01 00:00:00';
    public $to = '2092-01-01 00:00:00';
    public $helper;
    public $taxClassMap = array();

    public function __construct()
    {
        parent::__construct();

        $this->resource = Mage::getSingleton('core/resource');
        $this->readConnection = $this->resource->getConnection('core_read');
        $this->salesFlatOrderTable = $this->resource->getTableName('sales/order');
        $this->salesFlatOrderAddressTable = $this->resource->getTableName('sales/order_address');
        $this->salesFlatOrderItemTable = $this->resource->getTableName('sales/order_item');
        $this->helper = Mage::helper('taxify');

        $this->cursor = 0;
        $this->pageSize = 100;
    }

    public function fetch($page)
    {
        $this->cursor = $page * $this->pageSize;
        $queryRows = $this->readConnection->fetchAll($this->buildQuery());
        $queryRows = $this->addTaxClassId($queryRows);
        $queryRows = $this->addTransactionType($queryRows);
        $rows = array();
        foreach ($queryRows as $queryRow) {
            $exportRow = Mage::getModel('taxify/export_row');
            $exportRow->in($queryRow);
            $rows[] = $exportRow->out();
        }

        $shippinglineQueryRows = $this->readConnection->fetchAll($this->buildShippingLinesQuery());
        foreach ($shippinglineQueryRows as $queryRow) {
            $exportRow = Mage::getModel('taxify/export_row');
            $exportRow->in($queryRow);
            $rows[] = $exportRow->out();
        }

        return $rows;
    }

    public function buildQuery()
    {
        
        $query = "select
    `increment_id`,
    $this->salesFlatOrderTable.created_at,
    $this->salesFlatOrderTable.customer_id,
    $this->salesFlatOrderTable.customer_lastname,
    $this->salesFlatOrderTable.customer_firstname,
    $this->salesFlatOrderTable.customer_email,
    street,
    city,
    region,
    postcode,
    sku,
    $this->salesFlatOrderItemTable.product_id,
    $this->salesFlatOrderItemTable.name,
    $this->salesFlatOrderItemTable.price,
    $this->salesFlatOrderItemTable.qty_ordered,
    $this->salesFlatOrderItemTable.tax_amount,
    $this->salesFlatOrderTable.customer_group_id
from
    $this->salesFlatOrderItemTable
left join
    $this->salesFlatOrderTable on $this->salesFlatOrderTable.entity_id = $this->salesFlatOrderItemTable.order_id
left join
    $this->salesFlatOrderAddressTable on $this->salesFlatOrderTable.shipping_address_id = $this->salesFlatOrderAddressTable.entity_id
where
    $this->salesFlatOrderTable.created_at >= '$this->from' and
    $this->salesFlatOrderTable.created_at <= '$this->to'";

        if ($this->orderStatuses) {
            $query .= "\nand $this->salesFlatOrderTable.status in ('". implode("','", $this->orderStatuses). "')";
        }

        $query .= "\nORDER BY $this->salesFlatOrderTable.created_at asc";
        $query .= "\nLIMIT $this->cursor,$this->pageSize";

        return $query;
    }

    public function buildShippingLinesQuery()
    {
        $query = "select
    `increment_id`,
    $this->salesFlatOrderTable.created_at,
    $this->salesFlatOrderTable.customer_id,
    $this->salesFlatOrderTable.customer_lastname,
    $this->salesFlatOrderTable.customer_firstname,
    $this->salesFlatOrderTable.customer_email,
    street,
    city,
    region,
    postcode,
    'SHIPPING' as sku,
    0 as product_id,
    'Shipping' as name,
    shipping_amount as price,
    1 as qty_invoiced,
    0 as tax_amount
from
    $this->salesFlatOrderTable
left join
    $this->salesFlatOrderAddressTable on $this->salesFlatOrderTable.shipping_address_id = $this->salesFlatOrderAddressTable.entity_id
where
    $this->salesFlatOrderTable.created_at >= '$this->from' and
    $this->salesFlatOrderTable.created_at <= '$this->to'";

        if ($this->orderStatuses) {
            $query .= "\nand $this->salesFlatOrderTable.status in ('". implode("','", $this->orderStatuses). "')";
        }

        $query .= "\nORDER BY $this->salesFlatOrderTable.created_at asc";
        $query .= "\nLIMIT $this->cursor,$this->pageSize";

        return $query;
    }

    public function addTaxClassId($queryRows)
    {
        $map = $this->getProductTaxClassMap();
        $newRows = array();
        foreach ($queryRows as $row) {
            $row['tax_class_id'] = '';
            if (isset($map[$row['product_id']])) {
                $row['tax_class_id'] = $map[$row['product_id']];
            }
            $newRows[] = $row;
        }

        return $newRows;
    }

    public function addTransactionType($queryRows)
    {
        $newRows = array();
        foreach ($queryRows as $row) {
            $row['transaction_type'] = '';
            if (isset($row['customer_group_id'])) {
                $row['transaction_type'] = $this->getTaxifyCustomerTaxabilityFromGroup($row['customer_group_id']);
            }
            $newRows[] = $row;
        }

        return $newRows;
    }

    public function getProductTaxClassMap()
    {
        if (isset($this->productTaxClassMap)) {
            return $this->productTaxClassMap;
        }

        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('tax_class_id');

        $map = array();
        foreach ($products as $product) {
            $map[$product->getId()] = $product->getTaxClassId();
        }
        $this->productTaxClassMap = $map;

        return $this->productTaxClassMap;
    }

    public function getTaxifyCustomerTaxabilityFromGroup($groupId)
    {
        if (array_key_exists($groupId, $this->taxClassMap)) {
            return $this->taxClassMap[$groupId];
        }

        $taxClass = $this->helper->getTaxifyCustomerTaxabilityFromGroup($groupId);
        $this->taxClassMap[$groupId] = $taxClass;

        return $taxClass;
    }

}
