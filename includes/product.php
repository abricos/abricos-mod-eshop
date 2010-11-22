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
$mod = Brick::$modules->GetModule('eshop');

$catItemMenu = $mod->currentCatalogItem; 
$catItem = $catItemMenu->source;
$productId = $mod->currentProductId;
$catalogManager = $mod->GetCatalogManager();

// заменяем данные по текущей категории, если нужно
$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"cattitle" => $catItem['tl'],
	"catdesc" => $catItem['dsc']
));

$db = Brick::$db;
$link = $catItemMenu->link;
$p = &$brick->param->param;

$arr = explode("x", $p['imgsize']);
$size = array("w"=>$arr[0],"h"=>$arr[1]);  

$el = $mod->currentProduct;

$imginfo = $db->fetch_array($catalogManager->FotoListThumb($el['elid'], $size['w'], $size['h'], 1));
 	
$imgSmList = "";
if (empty($imginfo)){
	$image = $brick->param->var["imgempty"];
}else{
	$thumb = CatalogModule::FotoThumbInfoParse($imginfo['thumb']); 
	$image = Brick::ReplaceVarByData($brick->param->var["img"], array(
		"src" => CatalogModule::FotoThumbLink($imginfo['fid'],  $size['w'], $size['h'], $imginfo['fn']), 
		"w" => ($thumb['w']>0 ? $thumb['w']."px" : ""),
		"h" => ($thumb['h']>0 ? $thumb['h']."px" : "")
	));
	
	// список маленьких картинок
	$arr = explode("x", $p['imgsizesm']);
	$w = $arr[0]; $h = $arr[1];
	$k = 0; //ввели для исключения первой фотки из списка js-данных
	  
	$rows = $catalogManager->FotoListThumb($el['elid'], $w, $h, $p['imglimit']);
	while (($imginfo = $db->fetch_array($rows))){
		$thumb = CatalogModule::FotoThumbInfoParse($imginfo['thumb']); 
		$imgSmList .= Brick::ReplaceVarByData($brick->param->var["imgsm"], array(
			"src" => CatalogModule::FotoThumbLink($imginfo['fid'], $w, $h, $imginfo['fn']),
			"fid" => $imginfo['fid'],
			"w" => ($thumb['w']>0 ? $thumb['w']."px" : ""),
			"h" => ($thumb['h']>0 ? $thumb['h']."px" : "")
		));
		if ($k == 0) $cphoto = Brick::ReplaceVar("{v#cphoto}", "cphoto", $imginfo['fid']); // первая превьюшка - pidCurrent в photosData
		if ($k > 0) $otherphoto .= ', "'.$imginfo['fid'].'":{mw:250,mh:250,bw:200,bh:200}'; // если к > 0 добавляем фотку в список
		$k = $k+1;
		
	}
}
$otherphoto = Brick::ReplaceVar("{v#otherphoto}", "otherphoto", $otherphoto); //замена списка превьюшек

$brick->content = Brick::ReplaceVarByData($brick->content,  array(
	"options" => Brick::ReplaceVarByData($brick->param->var["options"],  array(
		"overoptions" => $brick->param->var["options".$el['eltid']],
	)),
	"image" => $image,
	"imagelist" => $imgSmList,
	"otherphoto" => $otherphoto,
	"cphoto" => $cphoto
));

$replace = array(
	"link" => $link."product_".$productId."/"
);

$rows = $catalogManager->ElementOptionListByType($el['eltid']);
$el['fld_sklad'] = !empty($el['fld_sklad']) ? $el['fld_sklad'].' шт.' : 'Нет в наличии';

if (!$el['fld_sklad'] OR $el['fld_sklad'] == 0)	{
	$replace = array(
		"add2cart" => $brick->param->var['sklad0']
	);
} else {
	$btn = array(
		"productid" => $productId
	);
	$brick->param->var['button'] = Brick::ReplaceVarByData($brick->param->var['button'], $btn);
	$replace = array(
		"link" => $link."product_".$productId."/",
		"productid" => $productId,
		"add2cart" => $brick->param->var['button']
	);	
};

while (($row = $db->fetch_array($rows))){
	$fld = "fld_".$row['nm'];
	
	// Если опция пуста - пробел, чтобы не рушить верстку
	$el[$fld] = !empty($el[$fld]) ? $el[$fld] : '&nbsp;';
	if ($row['nm'] != 'desc'){
		// $el[$fld] = htmlspecialchars($el[$fld]);
	}
	$replace[$fld] = $el[$fld];
	// Если тип опции - таблица (fldtp = 5), то необходимо получить значение опции из таблицы
	if	($row['fldtp'] == 5){
		// Получаем значение опции 'tl'. '' - т.к. тип товара - default 
		$val = $catalogManager->ElementOptionFieldTableValue('', $row['nm'], $el[$fld]);
		$replace[$fld] = $val['tl'];
	}
	
	$replace["fldnm_".$row['nm']] = $row['tl'];
}

$brick->content = Brick::ReplaceVarByData($brick->content, $replace);

// Вывод заголовка страницы.
if (!empty($el['fld_metatitle']) && $el['fld_metatitle'] !="&nbsp;"){
	Brick::$builder->SetGlobalVar('meta_title', $el['fld_metatitle']);
} else if (!empty($el['fld_name']) && $el['fld_name'] !="&nbsp;"){
	Brick::$builder->SetGlobalVar('meta_title', $el['fld_name']);
}
// Вывод ключевых слов
if (!empty($el['fld_metakeys']) && $el['fld_metakeys'] !="&nbsp;"){
	Brick::$builder->SetGlobalVar('meta_keys', $el['fld_metakeys']);
}
// Вывод описания
if (!empty($el['fld_metadesc']) && $el['fld_metadesc'] !="&nbsp;"){
	Brick::$builder->SetGlobalVar('meta_desc', $el['fld_metadesc']);
}
?>