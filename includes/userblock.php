<?php
/**
 * @package Abricos
 * @subpackage Eshop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$var = &$brick->param->var; 
if (Abricos::$user->id == 0){
	$var['result'] = $var['guest'];
}else{
	$var['result'] = Brick::ReplaceVarByData($var['user'], array(
		"username" => Abricos::$user->name
	));
}
 
?>