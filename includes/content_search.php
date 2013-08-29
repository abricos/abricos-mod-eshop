<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$brick = Brick::$builder->brick;

$pQuery = Abricos::CleanGPC('g', 'q', TYPE_STR);
$pFField = Abricos::CleanGPC('g', 'eff', TYPE_STR);
$pFValue = Abricos::CleanGPC('g', 'ef', TYPE_STR);

$brick->param->var['query'] = $pQuery;
$p = &$brick->param->param;
$v = &$brick->param->var;

$cManager = EShopModule::$instance->GetManager()->cManager;

$catids = array(); $elids = array();
$arr = $cManager->Search($pQuery, $pFField, $pFValue);

$redirectCat = 0;

for ($i=0;$i<count($arr);$i++){
	$row = $arr[$i];
	if ($row['tp'] == "c"){
		array_push($catids, $row['id']);

		// переход на раздел, если он релевантный поиску
		if ($row['tl'] == $pQuery){
			$redirectCat = $row['id'];
		}
	}else if ($row['tp'] == "e"){
		array_push($elids, $row['id']);
	}
}

if (count($catids) == 0 && count($elids) == 0){
	return;
}

$brickCatList = Brick::$builder->LoadBrickS("eshop", "catalog_list", null, array('p'=>array(
	"catids" => implode(",", $catids)
)));

$lst .= $brickCatList->content;

$elListCfg = new CatalogElementListConfig();
$elListCfg->elids = $elids;

$brickElList = Brick::$builder->LoadBrickS("eshop", "product_list", null, array('p'=>array(
	"cfg" => $elListCfg
)));
$lst .= $brickElList->content;

$brick->param->var['result'] = $lst;

if ($redirectCat > 0){
	$cat = $cManager->CatalogList()->Find($redirectCat);
	$v['redirect'] = Brick::ReplaceVarByData($v['redirectt'], array(
		"url" => $cat->URI()
	));
	header("Location: ".$cat->URI());
}else if (count($elids) == 1){
	$el = $cManager->Element($elids[0]);
	if (!empty($el)){
		$v['redirect'] = Brick::ReplaceVarByData($v['redirectt'], array(
			"url" => $el->URI()
		));
		header("Location: ".$el->URI());
	}
}


?>