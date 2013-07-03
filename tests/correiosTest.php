<?php

// $this->expectOutputString('foo');
// print $correios->getContent();
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

    public function testGetOrderShippingCost()
    {
        $correios = new correios();
        $correios->id_carrier = "1";
        $params = new Params();
        $shipping_cost = 10;
        $frete = $correios->getOrderShippingCost($params, $shipping_cost);
        $this->assertEquals(24.8, $frete);
    }

}