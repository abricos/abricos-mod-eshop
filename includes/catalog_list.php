<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$brick = Brick::$builder->brick;
$v = &$brick->param->var;
$p = &$brick->param->param;

$man = EShopModule::$instance->GetManager()->cManager;

$catList = $man->CatalogList();

$cCat = $man->CatalogByAdress();

if (empty($cCat)){
	$brick->content = ""; return;
}

$cCat = $catList->Find($cCat->id);

$imgWidth = bkint($p['imgw']);
$imgHeight = bkint($p['imgh']);

Abricos::GetModule('filemanager')->EnableThumbSize(array(array(
	"w" => $imgWidth,
	"h" => $imgHeight
)));


$count = $cCat->childs->Count();

for ($i=0; $i<$count; $i++){
	$cat = $cCat->childs->GetByIndex($i);
	
	if (empty($cat->foto)){
		$image = $v["imgempty"];
	}else{
		$image = Brick::ReplaceVarByData($v["img"], array(
			"src" => $cat->FotoSrc($imgWidth, $imgHeight)
		));
	}
	$image = Brick::ReplaceVarByData($image, array(
		"w" => $imgWidth,
		"h" => $imgHeight
	));

	$lst .= Brick::ReplaceVarByData($v['row'], array(
		"width" => $imgWidth,
		"image" => $image,
		"title" => addslashes(htmlspecialchars($cat->title)),
		"link" => $cat->URI()
	));
}
$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"result" => Brick::ReplaceVarByData($v['table'], array(
		"rows" => $lst
	))
));

?>