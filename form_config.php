<?php

/**
 * @author Dlani
 * @copyright 2008
 */
$form_config = '
		<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
		<fieldset>
		<legend><img src="../img/admin/tab-shipping.gif" alt="" title="" />' . $this->l('Frete via Correios') . '</legend>
		
			<label>CEP</label>			
			<div class="margin-form">
			<input type="text" size="33" name="cep" value="' . $cep_orig . '" /> Ex. 99999999 </div>
			<br />
			<div class="margin-form">';

$form_config .= '</div>			
			<center><input type="submit" name="submitcarrinho_correios" value="' . $this->l('Save') . '" class="button" /></center>
			
		</fieldset>		
		</form>';
