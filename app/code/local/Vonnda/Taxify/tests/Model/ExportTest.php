<?php

require_once '../../app/Mage.php'; umask(0); Mage::app('default');

use \Mockery as m;

class ExportTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->model = Mage::getModel('taxify/export');
        $this->assertEquals('object', gettype($this->model));
        $this->queryResult = unserialize(file_get_contents('code/tests/fixtures/exportQueryResult.txt'));
    }

    public function tearDown()
    {
        m::close();
    }

    public function testBuildQuery()
    {
        $this->model->cursor = 20;
        $resp = $this->model->buildQuery();

        $expected = 'select
    `increment_id`,
    sales_flat_order.created_at,
    sales_flat_order.customer_id,
    sales_flat_order.customer_lastname,
    sales_flat_order.customer_firstname,
    sales_flat_order.customer_email,
    street,
    city,
    region,
    postcode,
    sku,
    sales_flat_order_item.product_id,
    sales_flat_order_item.name,
    sales_flat_order_item.price,
    sales_flat_order_item.qty_ordered,
    sales_flat_order_item.tax_amount,
    sales_flat_order.customer_group_id
from
    sales_flat_order_item
left join
    sales_flat_order on sales_flat_order.entity_id = sales_flat_order_item.order_id
left join
    sales_flat_order_address on sales_flat_order.shipping_address_id = sales_flat_order_address.entity_id
where
    sales_flat_order.created_at >= \'1980-01-01 00:00:00\' and
    sales_flat_order.created_at <= \'2092-01-01 00:00:00\'
ORDER BY sales_flat_order.created_at asc
LIMIT 20,100';
        
        $this->assertEquals($expected, $resp);
    }

    public function testBuildQueryAddsStatusFilters()
    {
        $this->model->orderStatuses = array('complete', 'pending');
        $resp = $this->model->buildQuery();
        $this->assertRegExp('/where/', $resp);
        $this->assertRegExp('/pending/', $resp);
    }

    public function testBuildQueryAddsFromFilter()
    {
        $this->model->from = '2013-04-03 17:25:53';
        $this->model->to = '2016-04-03 17:25:53';
        $resp = $this->model->buildQuery();
        $this->assertRegExp('/2013-04-03 17:25:53/', $resp);
        $this->assertRegExp('/2016-04-03 17:25:53/', $resp);
    }

    public function testFetch()
    {
        $mock = m::mock('connection');
        $mock->shouldReceive('fetchAll')->andReturn($this->queryResult);
        $this->model->readConnection = $mock;
        $resp = $this->model->fetch();

        $this->assertTrue(is_array($resp));
    }

    // integration
    public function testAddTaxClassId()
    {
        $model = Mage::getModel('taxify/export');
        $mock = m::mock('connection');
        $mock->shouldReceive('fetchAll')->andReturn(array(array()));
        $model->readConnection = $mock;
        $resp = $model->fetch();

        $this->assertTrue(array_key_exists('tax_collected_type', $resp[0]), 'It should contain tax_collected_type');
    }

    public function testBuildShippingLinesQuery()
    {
        $this->model->cursor = 20;
        $expected = "select
    `increment_id`,
    sales_flat_order.created_at,
    sales_flat_order.customer_id,
    sales_flat_order.customer_lastname,
    sales_flat_order.customer_firstname,
    sales_flat_order.customer_email,
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
    sales_flat_order
left join
    sales_flat_order_address on sales_flat_order.shipping_address_id = sales_flat_order_address.entity_id
where
    sales_flat_order.created_at >= '1980-01-01 00:00:00' and
    sales_flat_order.created_at <= '2092-01-01 00:00:00'
ORDER BY sales_flat_order.created_at asc
LIMIT 20,100";

        $resp = $this->model->buildShippingLinesQuery();

        $this->assertFalse(is_null($resp));
        $this->assertEquals($expected, $resp);
    }

    public function testGetTaxifyCustomerTaxibilityFromGroup()
    {
        $this->model->taxClassMap = array('1' => '', '4' => 'NON');
        $resp = $this->model->getTaxifyCustomerTaxabilityFromGroup(1);

        $this->assertEquals('', $resp);

        $resp = $this->model->getTaxifyCustomerTaxabilityFromGroup(4);

        $this->assertEquals('NON', $resp);
    }

}
