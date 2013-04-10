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
$cfg = &Abricos::$config['module']['eshop'];


$imgWidth = bkint($p['imgw']);
$imgHeight = bkint($p['imgh']);

Abricos::GetModule('filemanager')->EnableThumbSize(array(array(
	"w" => $imgWidth,
	"h" => $imgHeight
)));


$db = Abricos::$db;
$mod = EShopModule::$instance;

$catalog = $mod->GetCatalog();
$catalogManager = $mod->GetCatalogManager(); 

$smMenu = Abricos::GetModule('sitemap')->GetManager()->GetMenu();

$catItemMenu = $smMenu->menuLine[count($smMenu->menuLine)-1];
$catItem = $catItemMenu->source;

$link = $baseUrl = $catItemMenu->link; 

$listData = $mod->GetManager()->GetProductListData();

$listPage = $listData['listPage'];
if (intval($p['page'])>0){
	$listPage = intval($p['page']);
}

$catids = $listData['catids'];

if ($p['notchildlist']){
	$catids = array($catItem['id']);
}

$tempArr = array();

$custOrder = empty($p['custorder']) ? "fld_price=0, fld_price" : $p['custorder'];

if ($p['forcontent'] == 'true'){
	$p['count'] = EShopConfig::$instance->productPageCount;
}

$rows = $catalogManager->ElementList($catids, $listPage, bkint($p['count']), $p['custwhere'], $custOrder, $p['overfields']);

$brick->totalElementCount = $catalogManager->ElementCount($catids, $p['custwhere']);

$elTypeList = $catalogManager->ElementTypeListArray();

$lstResult = "";
$strList = array();

$etArr0 = $catalogManager->ElementOptionListByType($el['eltid'], true);

while (($row = $db->fetch_array($rows))){
	$el = $catalogManager->Element($row['id'], true);
	$el['fld_name'] = $el['tl'];
	
	if (empty($tempArr[$el['catid']])){
		$tempArr[$el['catid']] = $smMenu->FindSource('id', $el['catid']);
	}
	$link = $tempArr[$el['catid']]->link;
	
	$pTitle = addslashes(htmlspecialchars($el['fld_name']));
	$pTitleSeo = "";
	if (EShopConfig::$instance->seo){
		$pTitleSeo = translateruen($el['fld_name']);
	}

	// Проверка, является ли товар Новинкой, Акцией или Хитом продаж
	$pr_spec = $el['fld_akc'] != 0 ? $v["isakc"] : "";
	$pr_spec .= $el['fld_new'] != 0 ? $v["pr_new"] : "";
	$pr_spec .= $el['fld_hit'] != 0 ? $v["pr_hit"] : "";
	
	$pr_special = "";
	if (!empty($pr_spec)){
		$pr_special = Brick::ReplaceVar($v["special"], "pr_spec", $pr_spec);
	}

	$imginfo = $db->fetch_array($catalogManager->FotoListThumb($el['elid'], $imgWidth, $imgHeight, 1));
	
	if (empty($imginfo)){
		$image = Brick::ReplaceVarByData($v["imgempty"], array(
		));
	}else{
		$thumb = CatalogModule::FotoThumbInfoParse($imginfo['thumb']);
		
		$imgName = $imginfo['fn'];
		if (EShopConfig::$instance->seo){
			$imgName = $pTitleSeo.".".$imginfo['ext'];
		}
		
		$image = Brick::ReplaceVarByData($brick->param->var["img"], array(
			"src" => CatalogModule::FotoThumbLink($imginfo['fid'], $imgWidth, $imgHeight, $imgName), 
			"w" => ($thumb['w']>0 ? $thumb['w']."px" : ""),
			"h" => ($thumb['h']>0 ? $thumb['h']."px" : "")
		));
	}
	$replace = array(
		"special" => $pr_special,
		"tpl_btn" => $brick->param->var[$el['fld_sklad']==0 ? 'btnnotorder' : 'btnorder'],
		"image" => $image, 
		"title" => $pTitle,
		"price" => $el['fld_price'],
		"desc" => $el['fld_desc'],
		"link" => $link."product_".$row['id']."/",
		"productid" => $row['id']
	);
	
	
	$etArr = $catalogManager->ElementOptionListByType($el['eltid'], true);
	$etArr = array_merge($etArr0, $etArr);
	
	foreach ($etArr as $etRow){
		$fld = "fld_".$etRow['nm'];
		
		// Если опция пуста - пробел, чтобы не рушить верстку
		$el[$fld] = !empty($el[$fld]) ? $el[$fld] : '&nbsp;';
		if ($etRow['nm'] != 'desc'){
			// $el[$fld] = htmlspecialchars($el[$fld]);
		}
		$replace[$fld] = $el[$fld];
		/*
		// Если тип опции - таблица (fldtp = 5), то необходимо получить значение опции из таблицы
		if	($row['fldtp'] == 5){
			// Получаем значение опции 'tl'. '' - т.к. тип товара - default 
			$val = $catalogManager->ElementOptionFieldTableValue('', $row['nm'], $el[$fld]);
			$replace[$fld] = $val['tl'];
		}/**/
		
		$replace["fldnm_".$etRow['nm']] = $etRow['tl'];
	}
	$isChangeType = false;
	$tpRow = $brick->param->var['row'];
	$elTypeId = $el['eltid'];
	if (!empty($elTypeList[$elTypeId])){
		$elTypeName = $elTypeList[$elTypeId]['nm'];
		if (!empty($brick->param->var['row-'.$elTypeName])){
			$tpRow = $brick->param->var['row-'.$elTypeName];
			$isChangeType = true;
		}
	}
	$strList[$isChangeType ? $elTypeId : 0] .= Brick::ReplaceVarByData($tpRow, $replace);
}

$lstResult = "";
foreach ($strList as $key => $value){
	$tpTable = $brick->param->var['table'];
	$elTypeName = $elTypeList[$key]['nm'];
	if (!empty($brick->param->var['table-'.$elTypeName])){
		$tpTable = $brick->param->var['table-'.$elTypeName];
	}
	$lstResult .= Brick::ReplaceVarByData($tpTable, array(
		"page" => $listPage, "rows" => $value
	));
}

$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"display" => $p['display'],
	"result" => $lstResult
));


?>