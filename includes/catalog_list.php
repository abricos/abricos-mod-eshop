<?php
/**
 * @version $Id$
 * @package Abricos
 * @subpackage EShop
 * @copyright Copyright (C) 2011 Abricos All rights reserved.
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
$rootMenu = $smMenu->menuLine[count($smMenu->menuLine)-1];

$catItemMenu = null;
$adress = CMSRegistry::$instance->adress;

if ($adress->level == 0){
	foreach ($rootMenu->child as $child){
		if ($child->name == 'eshop'){
			$catItemMenu = $child;
			break;
		}
	}
}else {
	$catItemMenu = $rootMenu;
}

if (is_null($catItemMenu)){
	$brick->content = "";
	return;
}
$catItem = $catItemMenu->source;

$link = $baseUrl = $catItemMenu->link; 
$imgWidth = bkint($p['imgw']);
$imgHeight = bkint($p['imgh']);

foreach ($catItemMenu->child as $child){
	$link = $child->link;
	$imageid = $child->source['img'];
	
	if (empty($imageid)){
		$image = $brick->param->var["imgempty"];
	}else{
		$thumb = CatalogModule::FotoThumbInfoParse($imginfo['thumb']);
		
		$image = Brick::ReplaceVarByData($brick->param->var["img"], array(
			"src" => CatalogModule::FotoThumbLink($imageid, $imgWidth, $imgHeight, 'image')
		));
	}
	
	$image = Brick::ReplaceVarByData($image, array(
		"w" => $imgWidth,
		"h" => $imgHeight
	));
	
	$lst .= Brick::ReplaceVarByData($brick->param->var['row'], array(
		"image" => $image, 
		"title" => addslashes(htmlspecialchars($child->source['tl'])),
		"link" => $link
	));
}
$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"result" => Brick::ReplaceVarByData($brick->param->var['table'], array(
		"rows" => $lst
	)) 
));

?>