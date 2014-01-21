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

$man = EShopModule::$instance->GetManager()->cManager;

$imgWidth = bkint($p['imgw']);
$imgHeight = bkint($p['imgh']);

Abricos::GetModule('filemanager')->EnableThumbSize(array(array(
	"w" => $imgWidth,
	"h" => $imgHeight
)));

$cfg = new CatalogElementListConfig();
$cfg->limit = 500;

$cat = $man->Catalog($p['catid']);

array_push($cfg->catids, $cat->id); 

$elList = $man->ProductList($cfg);
$brick->elementList = $elList;
if (empty($elList)){ $brick->content = ""; return; }

$lst = ""; $lstz = "";
for ($i=0;$i<$elList->Count();$i++){
	$el = $elList->GetByIndex($i);
	
	$pTitle = addslashes(htmlspecialchars($el->title));
	
	$pr_spec = !empty($el->ext['akc']) ? $v['pr_akc'] : "";
	$pr_spec .= !empty($el->ext['new']) ? $v['pr_new'] : "";
	$pr_spec .= !empty($el->ext['hit']) ? $v['pr_hit'] : "";

	$pr_special = "";
	if (!empty($pr_spec)){
		$pr_special = Brick::ReplaceVar($v["special"], "pr_spec", $pr_spec);
	}
	
	if (empty($el->foto)){
		$image = $v["imgempty"];
	}else{
		$imgSrc = OfflineManager::$instance->WriteImage($p['dir'], $el->foto, $imgWidth, $imgHeight);
		
		$image = Brick::ReplaceVarByData($v["img"], array(
			"src" => $imgSrc
		));
	}
	$image = Brick::ReplaceVarByData($image, array(
		"w" => $imgWidth,
		"h" => $imgHeight
	));

	$replace = array(
		"special" => $pr_special,
		"tpl_btn" => $v[$el->ext['sklad']==0 ? 'btnnotorder' : 'btnorder'],
		"image" => $image,
		"title" => $pTitle,
		"price" => $el->ext['price'],
		"link" => "product".$el->id.".html",
		"productid" => $el->id
	);
	
	if ($el->ext['price'] > 0){
		$lst .=  Brick::ReplaceVarByData($v['row'], $replace);
	}else{
		$lstz .=  Brick::ReplaceVarByData($v['row'], $replace);
	}
}

$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"display" => $p['display'],
	"result" => Brick::ReplaceVarByData($v['table'], array(
		"rows" => $lst.$lstz
	))
));


?>