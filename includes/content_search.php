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

?>