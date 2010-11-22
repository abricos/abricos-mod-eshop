<?php
/**
 * @version $Id$
 * @package Abricos
 * @subpackage EShop
 * @copyright Copyright (C) 2008 Abricos All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin (roosit@abricos.org)
 */

$brick = Brick::$builder->brick;
$db = Brick::$db;
$p = &$brick->param->param;

$mod = Brick::$modules->GetModule('eshop');

$catalog = $mod->GetCatalog();
$catalogManager = $mod->GetCatalogManager(); 

$smMenu = CMSRegistry::$instance->modules->GetModule('sitemap')->GetManager()->GetMenu();

$catItemMenu = $smMenu->menuLine[count($smMenu->menuLine)-1];
$catItem = $catItemMenu->source;

$link = $baseUrl = $catItemMenu->link; 
$imgWidth = bkint($p['imgw']);
$imgHeight = bkint($p['imgh']);

$listData = $mod->GetManager()->GetProductListData();
$listPage = $listData['listPage'];
if (intval($p['page'])>0){
	$listPage = intval($p['page']);
}

$catids = $listData['catids'];

$fld = $p['fld'];
$perPage = bkint($p['count']);

$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"page" => $listPage
));

$tempArr = array();

//Проверяем наличие параметра fld у кирпича, вызывающего скрипт (выборка спецпредложений, новинок, акций и т.д.)
if ($fld){
	//выборка с сортировкой по полю параметра fld вызываемого кирпича базового типа товара
	$rows = $catalogManager->ElementList(0, $listPage, $perPage, $fld);
	// $rows = EShopQuery::ElementFldList($db, $fld, $listPage, $perPage);
} else {
	//выборка с сортировкой по полю fld_ord базового типа товара
	// $rows = EShopQuery::ElementList($db, $catids, $listPage, $perPage);
	$rows = $catalogManager->ElementList($catids, $listPage, $perPage);
}

while (($row = $db->fetch_array($rows))){
	$el = $catalogManager->Element($row['id'], true);
	if (empty($tempArr[$el['catid']])){
		$tempArr[$el['catid']] = $smMenu->FindSource('id', $el['catid']);
	}
	$link = $tempArr[$el['catid']]->link;
	// Проверка, является ли товар Новинкой, Акцией или Хитом продаж
	$pr_spec = $el['fld_akc'] != 0 ? $brick->param->var["pr_akc"] : "";
	$pr_spec .= $el['fld_new'] != 0 ? $brick->param->var["pr_new"] : "";
	$pr_spec .= $el['fld_hit'] != 0 ? $brick->param->var["pr_hit"] : "";
	$pr_spec11 = Brick::ReplaceVar($brick->param->var["pr_spec0"], "pr_spec", $pr_spec);

	$imginfo = $db->fetch_array($catalogManager->FotoListThumb($el['elid'], $imgWidth, $imgHeight, 1));

	if (empty($imginfo)){
		$image = $brick->param->var["imgempty"];
		$image = Brick::ReplaceVar($brick->param->var["imgempty"], "pr_spec1", $pr_spec11);
	}else{
		$thumb = CatalogModule::FotoThumbInfoParse($imginfo['thumb']);
		
		$image = Brick::ReplaceVarByData($brick->param->var["img"], array(
			"src" => CatalogModule::FotoThumbLink($imginfo['fid'], $imgWidth, $imgHeight, $imginfo['fn']), 
			"w" => ($thumb['w']>0 ? $thumb['w']."px" : ""),
			"h" => ($thumb['h']>0 ? $thumb['h']."px" : ""),
			"pr_spec1" => $pr_spec11
		));
	}

	$lst .= Brick::ReplaceVarByData($brick->param->var['row'], array(
		"tpl_btn" => $brick->param->var[$el['fld_sklad']==0 ? 'btnnotorder' : 'btnorder'],
		"image" => $image, 
		"title" => addslashes(htmlspecialchars($el['fld_name'])),
		"price" => $el['fld_price'],
		"link" => $link."product_".$row['id']."/",
		"productid" => $row['id']
	));
}
$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"display" => $p['display'],
	"result" => $lst
));


?>