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

$mod = EShopModule::$instance;
EShopModule::$instance->GetManager();

$man = EShopModule::$instance->GetManager()->cManager;
$modCart = Abricos::GetModule('eshopcart');

$arr = explode("x", $p['imgsize']);
$size = array(
	"w"=>$arr[0]*1,
	"h"=>$arr[1]*1
);

Abricos::GetModule('filemanager')->EnableThumbSize(array(array(
	"w" => $size['w'],
	"h" => $size['h']
)));

$productid = $mod->currentProductId;
$el = $man->Product($productid);
$cat = $man->Catalog($el->catid);

// заменяем данные по текущей категории, если нужно
$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"cattitle" => $cat->title,
	"catdesc" => $cat->detail->descript
));

$fotoList = $el->detail->fotoList;

$pTitle = addslashes(htmlspecialchars($el->title));
$pTitleSeo = "";
if (EShopConfig::$instance->seo){
	$pTitleSeo = translateruen($el->title);
	
	for ($i=0;$i<$fotoList->Count();$i++){
		$foto = $fotoList->GetByIndex($i);
		$fnm = $pTitleSeo;
		if ($i>0){
			$fnm .= "-".$i;
		}
		$foto->name = $fnm.".".$foto->extension;
	}
	
}

$imgSmList = $cphoto = $otherphoto = "";
if ($fotoList->Count() == 0){
	$image = $v["imgempty"];
}else{
	$foto = $fotoList->GetByIndex(0);
	
	$image = Brick::ReplaceVarByData($v["img"], array(
		"src" => $foto->Link($size['w'], $size['h']),
		"srcf" => $foto->Link()
	));
	

	$arr = explode("x", $p['imgsizesm']);
	$w = $arr[0]; $h = $arr[1];
	for ($i=0;$i<$fotoList->Count();$i++){
		$foto = $fotoList->GetByIndex($i);

		$imgSmList .= Brick::ReplaceVarByData($v["imgsm"], array(
			"src" => $foto->Link($w, $h),
			"fid" => $foto->id
		));
		
		if ($i == 0) {
			$cphoto = Brick::ReplaceVar("{v#cphoto}", "cphoto", $foto->id); // первая превьюшка - pidCurrent в photosData
		}else{
			$otherphoto .= ', "'.$foto->id.'":{mw:('.$size['w'].'+50),mh:('.$size['h'].'+50),bw:'.$size['w'].',bh:'.$size['h'].'}'; 
		}
	}
}

$otherphoto = Brick::ReplaceVar("{v#otherphoto}", "otherphoto", $otherphoto); //замена списка превьюшек

$brick->content = Brick::ReplaceVarByData($brick->content,  array(
	"options" => Brick::ReplaceVarByData($v["options"],  array(
		"overoptions" => $v["options".$el->elTypeId],
	)),
	"image" => $image,
	"imagelist" => $imgSmList,
	"otherphoto" => $otherphoto,
	"cphoto" => $cphoto
));

$replace = array(
	"link" => $el->URI(),
	"productid" => $productid,
	"fld_name" => $el->title
);

if (!empty($modCart)){
	$cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybutton', null, array("p" => array(
		"product" => $el
	)));
	$replace["buybutton"] = $cartBrick->content;
	
	$cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybuttonjsinit');
	$brick->content .= $cartBrick->content;
}


$elTypeList = $man->ElementTypeList();

for ($i=0;$i<2;$i++){
	$elOpts = $el->detail->optionsBase;
	if ($i == 0){
		$elType = $elTypeList->Get(0);
	}else if ($el->elTypeId > 0){
		$elType = $elTypeList->Get($el->elTypeId);
		$elOpts = $el->detail->optionsPers;
	}else{
		continue;
	}

	for ($ii=0; $ii<$elType->options->Count(); $ii++){
		$optInfo = $elType->options->GetByIndex($ii);
		$fld = "fld_".$optInfo->name;

		if ($optInfo->type == Catalog::TP_TABLE){
			$tblval = $optInfo->values[$elOpts[$optInfo->name]];
			if (!empty($tblval)){
				$replace[$fld] = $tblval['tl'];
			}
		}else{
			$replace[$fld] = $elOpts[$optInfo->name];
		}

		if (empty($replace[$fld])){
			// Если опция пуста - пробел, чтобы не рушить верстку
			$replace[$fld] = '&nbsp;';
		}

		$replace["fldnm_".$optInfo->name] = $optInfo->title;
	}
}

$tpTable = $brick->param->var["table"];
$tpRow = $brick->param->var["row"];

$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"optlist" => Brick::ReplaceVarByData($tpTable, array("rows" => $tpRow))
));
$brick->content = Brick::ReplaceVarByData($brick->content, $replace);

// Вывод заголовка страницы.
if (!empty($el->detail->metaTitle) && $el->detail->metaTitle !="&nbsp;"){
	Brick::$builder->SetGlobalVar('meta_title', $el->detail->metaTitle);
} else if (!empty($el->title) && $el->title !="&nbsp;"){
	Brick::$builder->SetGlobalVar('meta_title', $el->title);
}
// Вывод ключевых слов
if (!empty($el->detail->metaKeys) && $el->detail->metaKeys !="&nbsp;"){
	Brick::$builder->SetGlobalVar('meta_keys', $el->detail->metaKeys);
}
// Вывод описания
if (!empty($el->detail->metaDesc) && $el->detail->metaDesc !="&nbsp;"){
	Brick::$builder->SetGlobalVar('meta_desc', $el->detail->metaDesc);
}

/* * * * * * * * * * Старый метод - на удаление * * * * * * * */
return; // TODO: Удалить старый код  * * * * * * * * * * * * * /
/* * * * * * * * * * Старый метод - на удаление * * * * * * * */

$catItemMenu = $mod->currentCatalogItem; 
$catItem = $catItemMenu->source;
$productid = $mod->currentProductId;
$catalogManager = $mod->GetCatalogManager();


// заменяем данные по текущей категории, если нужно
$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"cattitle" => $catItem['tl'],
	"catdesc" => $catItem['dsc']
));

$db = Abricos::$db;
$link = $catItemMenu->link;
$p = &$brick->param->param;

$arr = explode("x", $p['imgsize']);
$size = array(
	"w"=>$arr[0]*1,
	"h"=>$arr[1]*1
);  

$el = $mod->currentProduct;

$pTitle = addslashes(htmlspecialchars($el['fld_name']));
$pTitleSeo = "";
if (EShopConfig::$instance->seo){
	$pTitleSeo = translateruen($el['fld_name']);
}

$imgInfo = $db->fetch_array($catalogManager->FotoListThumb($el['elid'], $size['w'], $size['h'], 1));

$imgSmList = "";
if (empty($imgInfo)){
	$image = $v["imgempty"];
}else{
	$thumb = CatalogModule::FotoThumbInfoParse($imgInfo['thumb']);

	$imgName = $imgInfo['fn'];
	if (EShopConfig::$instance->seo){
		$imgName = $pTitleSeo.".".$imgInfo['ext'];
	}
	
	$image = Brick::ReplaceVarByData($v["img"], array(
		"src" => CatalogModule::FotoThumbLink($imgInfo['fid'],  $size['w'], $size['h'], $imgName), 
		"srcf" => CatalogModule::FotoThumbLink($imgInfo['fid'],  0, 0, $imgName), 
		"w" => ($thumb['w']>0 ? $thumb['w']."px" : ""),
		"h" => ($thumb['h']>0 ? $thumb['h']."px" : "")
	));
	
	// список маленьких картинок
	$arr = explode("x", $p['imgsizesm']);
	$w = $arr[0]; $h = $arr[1];
	$k = 0; //ввели для исключения первой фотки из списка js-данных
	  
	$rows = $catalogManager->FotoListThumb($el['elid'], $w, $h, $p['imglimit']);
	while (($imgInfo = $db->fetch_array($rows))){
		$thumb = CatalogModule::FotoThumbInfoParse($imgInfo['thumb']); 
		$imgSmList .= Brick::ReplaceVarByData($v["imgsm"], array(
			"src" => CatalogModule::FotoThumbLink($imgInfo['fid'], $w, $h, $imgInfo['fn']),
			"fid" => $imgInfo['fid'],
			"w" => ($thumb['w']>0 ? $thumb['w']."px" : ""),
			"h" => ($thumb['h']>0 ? $thumb['h']."px" : "")
		));
		if ($k == 0) $cphoto = Brick::ReplaceVar("{v#cphoto}", "cphoto", $imgInfo['fid']); // первая превьюшка - pidCurrent в photosData
		if ($k > 0) $otherphoto .= ', "'.$imgInfo['fid'].'":{mw:('.$size['w'].'+50),mh:('.$size['h'].'+50),bw:'.$size['w'].',bh:'.$size['h'].'}'; // если к > 0 добавляем фотку в список
		$k = $k+1;
		
	}
}
$otherphoto = Brick::ReplaceVar("{v#otherphoto}", "otherphoto", $otherphoto); //замена списка превьюшек

$brick->content = Brick::ReplaceVarByData($brick->content,  array(
	"options" => Brick::ReplaceVarByData($v["options"],  array(
		"overoptions" => $v["options".$el['eltid']],
	)),
	"image" => $image,
	"imagelist" => $imgSmList,
	"otherphoto" => $otherphoto,
	"cphoto" => $cphoto
));

$replace = array(
	"link" => $link."product_".$productid."/",
	"productid" => $productid
);

$el['fld_sklad'] = !empty($el['fld_sklad']) ? $el['fld_sklad'].' шт.' : 'Нет в наличии';

if (!empty($modCart)){
	$cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybutton', null, array("p" => array(
		"product" => $el
	)));
	$replace["buybutton"] = $cartBrick->content;
}

if (!empty($modCart)){
	$cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybuttonjsinit');
	$brick->content .= $cartBrick->content;
}

$etArr = $catalogManager->ElementOptionListByType(0, true);

$elTypeArr = $catalogManager->ElementTypeListArray();


if ($el['eltid'] > 0){
	$etArrEl = $catalogManager->ElementOptionListByType($el['eltid'], true);
	$etArr = array_merge($etArr, $etArrEl);
}
foreach ($etArr as $etRow){
	$fld = "fld_".$etRow['nm'];
	
	// Если опция пуста - пробел, чтобы не рушить верстку
	$el[$fld] = !empty($el[$fld]) ? $el[$fld] : '&nbsp;';
	if ($etRow['nm'] != 'desc'){
		// $el[$fld] = htmlspecialchars($el[$fld]);
	}
	$replace[$fld] = $el[$fld];
	// Если тип опции - таблица (fldtp = 5), то необходимо получить значение опции из таблицы
	if	($etRow['fldtp'] == 5){
		// Получаем значение опции 'tl'. '' - т.к. тип товара - default 
		$val = $catalogManager->ElementOptionFieldTableValue($elTypeArr[$etRow['eltid']]['nm'], $etRow['nm'], $el[$fld]);
		$replace[$fld] = $val['tl'];
	}
	
	$replace["fldnm_".$etRow['nm']] = $etRow['tl'];
}
$replace["fld_name"] = $el['tl'];

$tpTable = $v["table"];
$tpRow = $v["row"];
$elTypeId = $el['eltid'];
$elTypeList = $catalogManager->ElementTypeListArray();
if (!empty($elTypeList[$elTypeId])){
	$elTypeName = $elTypeList[$elTypeId]['nm'];
	if (!empty($v['table-'.$elTypeName])){
		$tpTable = $v['table-'.$elTypeName];
	}
	if (!empty($v['row-'.$elTypeName])){
		$tpRow = $v['row-'.$elTypeName];
	}
}

$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"optlist" => Brick::ReplaceVarByData($tpTable, array("rows" => $tpRow))
));
$brick->content = Brick::ReplaceVarByData($brick->content, $replace);

?>