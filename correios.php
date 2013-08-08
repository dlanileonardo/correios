<?php

/** MODULO ADAPTADO POR ODLANIER
 * @author Odlanier de Souza Mendes
 * @copyright Dlani
 * @email mrdlani@live.com
 * @version 3.0
 * */
if (!defined('_PS_VERSION_'))
    exit;

class correios extends CarrierModule {

    public $id_carrier;
    private $_urlWebservice = "http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx?wsdl";
    private $_html = '';
    private $_postErrors = array();
    private $_factorys = array(
        "soapclient" => "SOAP Client",
        "nusoap" => "NuSoap"
    );
    private $_factory = "soapclient";
    public $servicos_todos = array(
        '41106' => 'PAC',
        '40010' => 'SEDEX',
        '40215' => 'SEDEX 10',
        '40290' => 'SEDEX HOJE',
            //'81019' => 'E-SEDEX', 
            //'44105' => 'MALOTE',
            //'41017' => 'NORMAL', 
            //'40045' => 'SEDEX A COBRAR', 
    );
    private $_moduleName = 'correios';

    function __construct() {
        $this->name = 'correios';
        $this->tab = 'shipping_logistics';
        $this->version = '3.0';
        $this->author = 'Dlani Mendes';
        $this->limited_countries = array('br');

        parent::__construct();

        /* The parent construct is required for translations */
        $this->page = basename(__file__, '.php');
        $this->displayName = $this->l('Frete Correios');
        $this->description = 'Painel de Controle dos Frete Correios.';
    }

    function install() {
        if (parent::install() == false or
                $this->registerHook('updateCarrier') == false or
                $this->registerHook('extraCarrier') == false or
                $this->registerHook('beforeCarrier') == false)
            return false;

        $this->installCarriers();

        return true;
    }

    /**
     * 
     */
    private function installCarriers() {
        $configBase = array(
            'id_tax_rules_group' => 0,
            'active' => true,
            'deleted' => 0,
            'shipping_handling' => false,
            'range_behavior' => 0,
            'delay' => array("br" => "Entrega pelos Correios."),
            'id_zone' => 1,
            'is_module' => true,
            'shipping_external' => true,
            'external_module_name' => $this->_moduleName,
            'need_range' => true,
            'url' => Tools::getHttpHost(true) . "/modules/correios/rastreio.php?objeto=@",
        );

        $arrayConfigs = array();
        foreach ($this->servicos_todos as $codServico => $servico)
            $arrayConfigs[] = array(
                "name" => "Correios - $servico",
                "cod_servico" => $codServico
            );

        foreach ($arrayConfigs as $config) {
            $config = array_merge($configBase, $config);
            $this->installExternalCarrier($config);
        }
    }

    /**
     * 
     * @param type $config
     * @return boolean
     */
    public function installExternalCarrier($config) {
        $check = Db::getInstance()->executeS("SELECT id_carrier FROM " . _DB_PREFIX_ . "carrier WHERE name = '" . $config['name'] . "' ");
        if (is_array($check) && !empty($check))
            return Db::getInstance()->autoExecute(_DB_PREFIX_ . 'carrier', array('deleted' => 0), 'UPDATE', ' name = "' . $config['name'] . '" ');

        $carrier = new Carrier();
        $carrier->name = $config['name'];
        $carrier->url = $config['url'];
        $carrier->id_tax_rules_group = $config['id_tax_rules_group'];
        $carrier->id_zone = $config['id_zone'];
        $carrier->active = $config['active'];
        $carrier->deleted = $config['deleted'];
        $carrier->delay = $config['delay'];
        $carrier->shipping_handling = $config['shipping_handling'];
        $carrier->range_behavior = $config['range_behavior'];
        $carrier->is_module = $config['is_module'];
        $carrier->shipping_external = $config['shipping_external'];
        $carrier->external_module_name = $config['external_module_name'];
        $carrier->need_range = $config['need_range'];

        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            $carrier->delay[(int) $language['id_lang']] = $config['delay']['br'];
        }

        if ($carrier->add()) {
            $groups = Group::getGroups(true);
            foreach ($groups as $group)
                Db::getInstance()->autoExecute(_DB_PREFIX_ . 'carrier_group', array('id_carrier' => (int) ($carrier->id), 'id_group' => (int) ($group['id_group'])), 'INSERT');

            $rangePrice = new RangePrice();
            $rangePrice->id_carrier = $carrier->id;
            $rangePrice->delimiter1 = '0';
            $rangePrice->delimiter2 = '0';
            $rangePrice->add();

            $rangeWeight = new RangeWeight();
            $rangeWeight->id_carrier = $carrier->id;
            $rangeWeight->delimiter1 = '0';
            $rangeWeight->delimiter2 = '30';
            $rangeWeight->add();

            $zones = Zone::getZones(true);
            foreach ($zones as $zone) {
                Db::getInstance()->autoExecute(_DB_PREFIX_ . 'carrier_zone', array('id_carrier' => (int) ($carrier->id), 'id_zone' => (int) ($zone['id_zone'])), 'INSERT');
                Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_ . 'delivery', array('id_carrier' => (int) ($carrier->id), 'id_range_price' => (int) ($rangePrice->id), 'id_range_weight' => NULL, 'id_zone' => (int) ($zone['id_zone']), 'price' => '0'), 'INSERT');
                Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_ . 'delivery', array('id_carrier' => (int) ($carrier->id), 'id_range_price' => NULL, 'id_range_weight' => (int) ($rangeWeight->id), 'id_zone' => (int) ($zone['id_zone']), 'price' => '0'), 'INSERT');
            }

            Configuration::updateValue("PS_CORREIOS_CARRIER_{$carrier->id}", $config['cod_servico']);

            // Copy Logo
            if (!copy(dirname(__FILE__) . '/logos/' . $config['cod_servico'] . '.png', _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg'))
                return false;

            // Return ID Carrier
            return (int) ($carrier->id);
        }
        return false;
    }

    /**
     * 
     * @return boolean
     */
    public function uninstall() {
        // Uninstall Carriers
        $result = Db::getInstance()->autoExecute(_DB_PREFIX_ . 'carrier', array('deleted' => 1), 'UPDATE', ' name LIKE "Correios%" ');

        if (!Configuration::deleteByName('PS_CORREIOS_CEP_ORIG'))
            return false;

        if (!parent::uninstall() OR !$this->unregisterHook('updateCarrier'))
            return false;

        return true;
    }

    /**
     * 
     * @return type
     */
    public function getContent() {
        $output = '<h2>' . $this->displayName . '</h2>';
        if (Tools::isSubmit('submitcarrinho_correios')) {
            Configuration::updateValue('PS_CORREIOS_CEP_ORIG', intval($_POST['cep']));

            $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->
                            l('Confirmation') . '" />' . $this->l('Settings updated') . '</div>';
        }
        if (Tools::isSubmit('factory')) {
            Configuration::updateValue('PS_CORREIOS_FACTORY', $_POST['factory']);

            $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->
                            l('Confirmation') . '" />' . $this->l('Settings updated') . '</div>';
        }

        return $output . $this->displayForm();
    }

    /**
     * 
     * @return type
     */
    public function displayForm() {
        $conf = Configuration::getMultiple(array('PS_CORREIOS_CEP_ORIG'));
        $cep_orig = array_key_exists('cep', $_POST) ? $_POST['cep'] : (array_key_exists('PS_CORREIOS_CEP_ORIG', $conf) ? $conf['PS_CORREIOS_CEP_ORIG'] : '');
        include (dirname(__file__) . "/form_config.php");
        return $form_config;
    }

    /**
     * 
     * @param type $params
     * @param type $shipping_cost
     * @return boolean
     */
    public function getOrderShippingCost($params, $shipping_cost) {
        $carrier = new Carrier();
        $chave = Configuration::get("PS_CORREIOS_CARRIER_{$this->id_carrier}");
        $address = new Address($params->id_address_delivery);

        $sCepDestino = preg_replace("/([^0-9])/", "", $address->postcode);

        $paramsCorreios = array(
            "sCepDestino" => $sCepDestino,
            "nVlPeso" => (string) $params->getTotalWeight(),
            "nCdServico" => $chave,
        );

        $this->getPriceWebService($paramsCorreios);
        $custoFrete = $this->getPriceWebService($paramsCorreios);

        if ($custoFrete === false || $custoFrete === 0.0)
            return false;

        return $custoFrete + $shipping_cost;
    }

    /**
     * 
     * @param type $params
     * @return type
     */
    public function getOrderShippingCostExternal($params) {
        return $this->getOrderShippingCost($params, 0);
    }

    /**
     * 
     * @param type $params
     */
    public function hookupdateCarrier($params) {
        
    }

    /**
     * 
     * @global type $smarty
     * @param type $params
     * @return type
     */
    public function hookbeforeCarrier($params) {
        global $smarty;
        $address = new Address($params['cart']->id_address_delivery);
        $smarty->assign(array(
            "sCepDestino" => $address->postcode
        ));
        return $this->display(__file__, 'extra_carrier.tpl');
    }

    /**
     * 
     * @param type $params
     */
    public function hookextraCarrier($params) {
        
    }

    /**
     * 
     * @param type $params
     * @return type
     */
    private function getPriceWebService($params) {
        $paramsBase = array(
            "nCdEmpresa" => "",
            "sDsSenha" => "",
            "sCepOrigem" => str_pad(Configuration::get('PS_CORREIOS_CEP_ORIG'), 8, "0", STR_PAD_LEFT),
            "nCdFormato" => "1",
            "nVlComprimento" => "30",
            "nVlAltura" => "8",
            "nVlLargura" => "30",
            "nVlDiametro" => "0",
            "sCdMaoPropria" => "N",
            "nVlValorDeclarado" => "0",
            "sCdAvisoRecebimento" => "N"
        );
        $params = array_merge($paramsBase, $params);
        $hash = ( implode("|", $params) );
        $getInCache = $this->getCache($hash);

        if ($getInCache) {
            $return = $getInCache;
        } else {
            $this->_factory = Configuration::get("PS_CORREIOS_FACTORY");
            $method = "getPreco" . ucfirst(strtolower($this->_factory));
            $return = $this->$method($params, $hash);
            $this->setCache($hash, $return);
        }
        return $return;
    }

    /**
     * 
     * @global type $smarty
     * @param type $idCarrier
     * @param type $sCepDestino
     * @return type
     */
    public function getPrazoDeEntrega($idCarrier, $sCepDestino) {
        global $smarty;
        $Carrier = new Carrier($idCarrier);

        $params = array(
            "sCepOrigem" => str_pad(Configuration::get('PS_CORREIOS_CEP_ORIG'), 8, "0", STR_PAD_LEFT),
            "nCdServico" => Configuration::get("PS_CORREIOS_CARRIER_{$idCarrier}"),
            "sCepDestino" => $sCepDestino,
        );

        $this->_factory = Configuration::get("PS_CORREIOS_FACTORY");
        $method = "getPrazo" . ucfirst(strtolower($this->_factory));
        $dias = $this->$method($params);

        $smarty->assign(array(
            "nomeServico" => $Carrier->name,
            "dias" => $dias
        ));

        return $this->display(__file__, 'prazo_de_entrega.tpl');
    }

    private function getPrazoSoapclient($params) {
        try {
            $client = new SoapClient($this->_urlWebservice);
            $result = $client->CalcPrazo($params);
            if (intval($result->CalcPrazoResult->Servicos->cServico->Erro) !== 0)
                return false;
            else
                return (integer) $result->CalcPrazoResult->Servicos->cServico->PrazoEntrega;
        } catch (Exception $e) {
            return false;
        }
    }

    private function getPrazoNusoap($params) {
        require_once('vendor/lib/nusoap.php');
        $nusoap = new nusoap_client($this->_urlWebservice, 'wsdl');
        $nusoap->setUseCURL(true);
        $result = $nusoap->call("CalcPrazo", $params);
        if (intval($result['CalcPrazoResult']['Servicos']['cServico']['Erro']) !== 0) {
            return false;
        } else {
            return (integer) str_replace(",", ".", $result['CalcPrazoResult']['Servicos']['cServico']['PrazoEntrega']);
        }
    }

    /**
     * 
     * @param type $name
     * @param type $value
     */
    private function setCache($name, $value) {
        if (_PS_CACHE_ENABLED_)
            Cache::getInstance()->setQuery($name, $value);
    }

    /**
     * 
     * @param type $name
     * @return boolean
     */
    private function getCache($name) {
        if (_PS_CACHE_ENABLED_)
            return Cache::getInstance()->get(md5($name));
        return false;
    }

    /**
     * 
     * @param type $params
     * @param type $hash
     * @return boolean
     */
    private function getPrecoSoapclient($params, $hash) {
        try {
            $client = new SoapClient($this->_urlWebservice);
        } catch (Exception $e) {
            return false;
        }
        $result = $client->CalcPreco($params);
        if (intval($result->CalcPrecoResult->Servicos->cServico->Erro) !== 0) {
            $this->setCache($hash, false);
            return false;
        } else {
            return (float) str_replace(",", ".", $result->CalcPrecoResult->Servicos->cServico->Valor);
        }
    }

    /**
     * 
     * @param type $params
     * @param type $hash
     * @return boolean
     */
    private function getPrecoNusoap($params, $hash) {
        require_once('vendor/lib/nusoap.php');
        $nusoap = new nusoap_client($this->_urlWebservice, 'wsdl');
        $nusoap->setUseCURL(true);
        $result = $nusoap->call("CalcPreco", $params);
        if (intval($result['CalcPrecoResult']['Servicos']['cServico']['Erro']) !== 0) {
            $this->setCache($hash, false);
            return false;
        } else {
            return (float) str_replace(",", ".", $result['CalcPrecoResult']['Servicos']['cServico']['Valor']);
        }
    }

}

?>
