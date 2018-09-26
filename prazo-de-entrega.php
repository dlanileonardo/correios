<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
//include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/correios.php');

$correios = new correios();

echo $correios->getPrazoDeEntrega(Tools::getValue('id_carrier'), Tools::getValue('sCepDestino'));