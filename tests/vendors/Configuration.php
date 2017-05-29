<?php

class Configuration extends ObjectModel
{

    public static function get($key, $id_lang = NULL)
    {
      if ( $key === "PS_CORREIOS_FACTORY" )
        return "Soapclient";

      if ( $key === "PS_CORREIOS_CARRIER_1" )
        return "04510";

      if( $key === "PS_CORREIOS_CEP_ORIG" )
        return "13902100";
    }

    public static function getMultiple($keys, $id_lang = NULL)
    {
      return array();
    }

}