<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$brick = Brick::$builder->brick;
$db = Abricos::$db;
$p = &$brick->param->param;
$v = &$brick->param->var;


$man = EShopManager::$instance->cManager;
$rootCat = $man->CatalogList()->GetByIndex(0);
$catList = $rootCat->childs;
$lst = "";

$imgWidth = bkint($p['imgw']);
$imgHeight = bkint($p['imgh']);


for($i=0; $i<$catList->Count();$i++){
	$cat = $catList->GetByIndex($i);
	
	$link = $child->link;
	
	if (empty($cat->foto)){
		$image = $v["imgempty"];
	}else{
		
		$imgSrc = OfflineManager::$instance->WriteImage($p['dir'], $cat->foto, $imgWidth, $imgHeight);
		
		$image = Brick::ReplaceVarByData($v["img"], array(
			"src" => $imgSrc
		));
	}
	
	$image = Brick::ReplaceVarByData($image, array(
		"w" => $imgWidth,
		"h" => $imgHeight
	));
	
	$lst .= Brick::ReplaceVarByData($v['row'], array(
		"image" => $image,
		"title" => addslashes(htmlspecialchars($cat->title)),
		"link" => $link
	));
}

$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"result" => Brick::ReplaceVarByData($v['table'], array(
		"rows" => $lst
	))
));

/*

$link = $baseUrl = $catItemMenu->link; 

foreach ($catItemMenu->child as $child){
	

	$lst .= Brick::ReplaceVarByData($brick->param->var['row'], array(
		"cattitle" => $child->source['tl'],
		"catdesc" => $child->source['dsc'],
		"image" => $image, 
		"title" => addslashes(htmlspecialchars($child->source['tl'])),
		"link" => $link
	));
	
}
/**/

?>