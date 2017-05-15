<?php

class Configuration extends ObjectModel
{

    public static function get($key, $id_lang = NULL)
    {
      if ( $key === "PS_CORREIOS_FACTORY" )
        return "Soapclient";

      if ( $key === "PS_CORREIOS_CARRIER_1" )
        return "04510"; #era "41106"; # PAC

      if ( $key === "PS_CORREIOS_CARRIER_2" )
        return "04014"; #era "40010"; # SEDEX

      if ( $key === "PS_CORREIOS_CARRIER_3" )
        return "40215"; # SEDEX 10

      if ( $key === "PS_CORREIOS_CARRIER_4" )
        return "40290"; # SEDEX HOJE

      if( $key === "PS_CORREIOS_CEP_ORIG" )
        return "01311000"; # av paulista
    }

    public static function getMultiple($keys, $id_lang = NULL)
    {
      return array();
    }

}