<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$brick = Brick::$builder->brick;

$query = Abricos::CleanGPC('g', 'q', TYPE_STR);
$brick->param->var['query'] = $query;

$cManager = EShopModule::$instance->GetManager()->cManager;
header('Content-type: text/plain');

$catids = array(); $elids = array();
$arr = $cManager->Search($query);

for ($i=0;$i<count($arr);$i++){
	$row = $arr[$i];
	if ($row['tp'] == "c"){
		array_push($catids, $row['id']);
	}else if ($row['tp'] == "e"){
		array_push($elids, $row['id']);
	}
}

if (count($catids) == 0 || count($elids) == 0){
	return;
}

$catList = $cManager->CatalogListLine();

$lst = "";
for ($i=0; $i<count($catids); $i++){
	$cat = $catList->Get($catids[$i]);
	$lst .= Brick::ReplaceVarByData($brick->param->var['rowcat'], array(
		"tl" => $cat->title,
		"url" => $cat->URI()
	));
}

$elListCfg = new CatalogElementListConfig();
$elListCfg->elids = $elids;
$elList = $cManager->ProductList($elListCfg);

for ($i=0; $i<$elList->Count(); $i++){
	$el = $elList->GetByIndex($i);
	$lst .= Brick::ReplaceVarByData($brick->param->var['rowel'], array(
		"tl" => $el->title,
		"url" => $el->URI()
	));
}
$brick->param->var['result'] = $lst;

?>