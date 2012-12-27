<?php
/**
 * @version $Id$
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin (roosit@abricos.org)
 */

$brick = Brick::$builder->brick;
$db = Abricos::$db;
$p = &$brick->param->param;
Abricos::GetModule('eshop');
$mod = EShopModule::$instance;

$listTotal = 0;

foreach ($brick->child as $child){
	if ($child->name == 'product_list'){
		$listTotal = $child->totalElementCount;
	}
}
if ($listTotal == 0){
	$brick->content = "";
}
?>