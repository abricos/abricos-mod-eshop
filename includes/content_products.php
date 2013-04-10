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
$cfg = &Abricos::$config['module']['eshop'];

$cat = $man->CatalogByAdress();

if (empty($cat)){
	$brick->content = ""; return;
}
$dtl = $cat->detail;

$cat_desc = "";
// Проверка на наличие описания категории. Если его нет, не выводим блок описания. 
// <p></p> - вставляется автоматом при редактировании категории
if (!empty($dtl->descript) && $dtl->descript != "<p></p>"){
	$cat_desc = Brick::ReplaceVar($v["description"], "descript", $dtl->descript);
}

$adminButton = "";
if (EShopManager::$instance->IsAdminRole()){
	$adminButton = Brick::ReplaceVarByData($v['adminbutton'], array(
		"catid" => intval($cat->id)
	));
}

// Для главной страницы /eshop/
$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"adminbutton" => $adminButton,
	"cattitle" => !empty($cat->title) ? $cat->title : $v["deftitle"],
	"catdesc" => $cat_desc
));


// Вывод заголовка страницы
if (!empty($dtl->metaTitle)){
	Brick::$builder->SetGlobalVar('meta_title', $dtl->metaTitle);
}

// Вывод ключевых слов
if (!empty($dtl->metaKeys)){
	Brick::$builder->SetGlobalVar('meta_keys', $dtl->metaKeys);
}

// Вывод описания
if (!empty($dtl->metaDescript)){
	Brick::$builder->SetGlobalVar('meta_desc', $dtl->metaDescript);
}

return; /////////////////////////////////////////////////////////////////////

/*

// TODO: Реализация постраничного вывода под вопросом

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

/**/

?>