<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$brick = Brick::$builder->brick;
$p = &$brick->param->param;
$v = &$brick->param->var;

$el = $p['element'];
if (empty($el)){
	$brick->content = "";
	return;
}
if (false){
	$el = new CatalogElement();
}

$replace = array("buybutton" => "");

$modCart = Abricos::GetModule('eshopcart');

if (!empty($modCart)){
	$cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybutton', null, array("p" => array(
		"product" => $el
	)));
	$replace["buybutton"] = $cartBrick->content;
	
	$cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybuttonjsinit');
	$brick->content .= $cartBrick->content;
}

$elTypeList = EShopManager::$instance->cManager->ElementTypeList();

$elOptBase = $el->detail->optionsBase;
$elOptPers = $el->detail->optionsPers;

$aOptions = explode(",", $v['options']);
foreach ($aOptions as $sOption){
	$sOption = trim($sOption);
	if (empty($sOption)){ continue; }
	
	$replace['option-'.$sOption] = "";
	$value = "";
	if (!empty($elOptBase[$sOption])){
		$value = $elOptBase[$sOption];
	}else if (!empty($elOptPers[$sOption])){
		$value = $elOptPers[$sOption];
	}
	
	if (empty($value) || empty($v['option-'.$sOption])){ continue; }
	
	$replace['option-'.$sOption] = $v['option-'.$sOption];
}

$brick->content = Brick::ReplaceVarByData($brick->content, $replace);

?>