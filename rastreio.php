<?php

/** MODULO CRIADO POR ODLANIER
 * @author Odlanier de Souza Mendes
 * @copyright Dlani
 * @email master_odlanier@hotmail.com
 * @email mends@prestashopbr.com
 * */
@ini_set('display_errors', 'OFF');

if (isset($_GET['objeto']) && $_GET['objeto']) {
    $objeto = $_GET['objeto'];
} else {
    $objeto = 'AA999999999BR0';
}

$url = "http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI=$objeto";

$doc = new DOMDocument();
$ndoc = new DOMDocument();

$ndoc->loadHTMLFile(dirname(__FILE__) . '/template_reastreio.html');

$doc->loadHTMLFile($url);

$table = $doc->getElementsByTagName('table')->item(0);

$fonts = $table->getElementsByTagName('font');

foreach ($fonts as $font)
    if ($font->getAttribute('color') === '000000')
        $font->setAttribute('color', "#cc9966");


if (is_null($table)) {
    $add = $ndoc->createTextNode('Nenhum objeto encontrado com esse número de rastreio!');
} else {
    $add = $ndoc->importNode($table, true);
}

$div = $ndoc->getElementById('table');
$div->appendChild($add);

echo $ndoc->saveHTML();