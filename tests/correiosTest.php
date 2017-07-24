<?php

class correiosTest extends PHPUnit_Framework_TestCase {

    /**
    * Testa o construtor da classe
    */
    public function testConstructClass()
    {
        $this->assertInstanceOf('correios', new correios);
    }

    public function testGetContent()
    {
        $correios = new correios();
        $this->assertContains('submitcarrinho_correios', $correios->getContent());
    }

    public function testGetOrderShippingCostSEDEXHoje()
    {
        $this->testGetOrderShppingCost("4", 10, 34.600000000000001);
    }

    public function testGetOrderShippingCostSEDEX10()
    {
        $this->testGetOrderShppingCost("3", 10, 29.200000000000003);
    }

    public function testGetOrderShippingCostSEDEX()
    {
        $this->testGetOrderShppingCost("2", 10, 17.199999999999999);
    }

    public function testGetOrderShppingCost($id_carrier = "1", $shipping_cost = 10, $expected_value = 16.1)
    {
        $correios = new correios();
        $correios->id_carrier = $id_carrier;
        $params = new Params();
        $frete = $correios->getOrderShippingCost($params, $shipping_cost);
        $this->assertEquals($shipping_cost + $expected_value, $frete);
    }
}