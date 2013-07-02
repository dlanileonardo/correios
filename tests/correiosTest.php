<?php

class correiosTest extends PHPUnit_Framework_TestCase {

	/**
	* Testa o construtor da classe
	*/
	public function testConstructClass()
    {
        $correios = new correios();
        $this->assertTrue(is_object($correios) && $correios instanceof correios);
    }

}