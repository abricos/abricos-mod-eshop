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

$mod = EShopModule::$instance;
$cfg = &Abricos::$config['module']['eshop'];

$smMenu = Abricos::GetModule('sitemap')->GetManager()->GetMenu();
$catItemMenu = $smMenu->menuLine[count($smMenu->menuLine)-1];
$catItem = $catItemMenu->source;

$catalogManager = $mod->GetCatalogManager();

// Проверка на наличие описания категории. Если его нет, не выводим блок описания. 
// <p></p> - вставляется автоматом при редактировании категории
$cat_desc = "";
if ($catItem['dsc'] != null AND $catItem['dsc'] != "<p></p>"){
	$cat_desc = Brick::ReplaceVar($v["description"], "descript", $catItem['dsc']);
}


$adminButton = "";
if (EShopManager::$instance->IsAdminRole()){
	$adminButton = Brick::ReplaceVarByData($v['adminbutton'], array(
		"catid" => intval($catItem['id'])
	));
}


// Для главной страницы /eshop/
$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"adminbutton" => $adminButton,
	"cattitle" => !empty($catItem['tl']) ? $catItem['tl'] : $v["deftitle"],
	"catdesc" => $cat_desc
));

$link = $baseUrl = $catItemMenu->link; 

$listData = $mod->GetManager()->GetProductListData();

$listPage = $listData['listPage'];
$catids = $listData['catids'];
// $listTotal = $catalogManager->ElementCount($catids);
$listTotal = 0;

foreach ($brick->child as $child){
	if ($child->name == 'product_list'){
		$listTotal = $child->totalElementCount;
	}
}

// подгрузка кирпича пагинатора с параметрами
Brick::$builder->LoadBrickS('sitemap', 'paginator', $brick, array("p" => array(
	"total" => $listTotal,
	"page" => $listPage,
	"perpage" => EShopConfig::$instance->productPageCount,
	"uri" => $baseUrl,
	"hidepn" => "0"
)));

if ($p['jspage'] == 'true'){
	// подгрузка кирпича пагинатора с параметрами
	Brick::$builder->LoadBrickS('eshop', 'jspage', $brick);
}

// Вывод заголовка страницы (проверка на &nbsp;, т.к. в базе может храниться и пробел)
if (!empty($catItem['ktl']) && $catItem['ktl'] != "&nbsp;"){
	Brick::$builder->SetGlobalVar('meta_title', $catItem['ktl']);
}
else if (!empty($catItem['tl'])){
	Brick::$builder->SetGlobalVar('meta_title', $catItem['tl']);
}

// Вывод ключевых слов
if (!empty($catItem['kwds']) && $catItem['kwds'] != "&nbsp;"){
	Brick::$builder->SetGlobalVar('meta_keys', $catItem['kwds']);
}

// Вывод описания
if (!empty($catItem['kdsc']) && $catItem['kdsc'] != "&nbsp;"){
	Brick::$builder->SetGlobalVar('meta_desc', $catItem['kdsc']);
}


?>