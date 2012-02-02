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
$db = Abricos::$db;
$p = &$brick->param->param;
$mod = Abricos::GetModule('eshop');

$smMenu = Abricos::GetModule('sitemap')->GetManager()->GetMenu();
$catItemMenu = $smMenu->menuLine[count($smMenu->menuLine)-1];
$catItem = $catItemMenu->source;

$catalogManager = $mod->GetCatalogManager();

// Проверка на наличие описания категории. Если его нет, не выводим блок описания. 
// <p></p> - вставляется автоматом при редактировании категории
$cat_desc = "";
if ($catItem['dsc'] != null AND $catItem['dsc'] != "<p></p>"){
	$cat_desc = Brick::ReplaceVar($brick->param->var["cat_desc_not_null"], "descript", $catItem['dsc']);
}

// Для главной страницы /eshop/
$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"cattitle" => !empty($catItem['tl']) ? $catItem['tl'] : $brick->param->var["deftitle"],
	"cat_desc" => $cat_desc
));

$link = $baseUrl = $catItemMenu->link; // ********

$listData = $mod->GetManager()->GetProductListData();

$listPage = $listData['listPage'];
$catids = $listData['catids'];

$listTotal = $catalogManager->ElementCount($catids); // ********
$perPage = bkint($p['count']); // ********

// подгрузка кирпича пагинатора с параметрами
Brick::$builder->LoadBrickS('sitemap', 'paginator', $brick, array("p" => array(
	"total" => $listTotal,
	"page" => $listPage,
	"perpage" => $perPage,
	"uri" => $baseUrl,
	"hidepn" => "0"
)));

// Вывод заголовка страницы (проверка на &nbsp;, т.к. в базе может храниться и пробел)
if (!empty($catItem['ktl']) && $catItem['ktl'] !="&nbsp;"){
	Brick::$builder->SetGlobalVar('meta_title', $catItem['ktl']);
}
else if (!empty($catItem['tl'])){
	Brick::$builder->SetGlobalVar('meta_title', $catItem['tl']);
}

// Вывод ключевых слов
if (!empty($catItem['kwds']) && $catItem['kwds'] !="&nbsp;"){
	Brick::$builder->SetGlobalVar('meta_keys', $catItem['kwds']);
}
/*
else if (!empty($catItem['tl'])){
	Brick::$builder->SetGlobalVar('meta_keys', $catItem['tl']);
}
*/
// Вывод описания
if (!empty($catItem['kdsc']) && $catItem['kdsc'] !="&nbsp;"){
	Brick::$builder->SetGlobalVar('meta_desc', $catItem['kdsc']);
}

?>