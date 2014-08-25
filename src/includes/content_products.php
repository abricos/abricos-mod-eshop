<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$brick = Brick::$builder->brick;
$p = & $brick->param->param;
$v = & $brick->param->var;

$man = EShopModule::$instance->GetManager()->cManager;
$cfg = & Abricos::$config['module']['eshop'];

$cat = $man->CatalogByAdress();

if (empty($cat)) {
    $brick->content = "";
    return;
}
$dtl = $cat->detail;

$cat_desc = "";
// Проверка на наличие описания категории. Если его нет, не выводим блок описания. 
// <p></p> - вставляется автоматом при редактировании категории
if (!empty($dtl->descript) && $dtl->descript != "<p></p>") {
    $cat_desc = Brick::ReplaceVar($v["description"], "descript", $dtl->descript);
}

$adminButton = "";
if (EShopManager::$instance->IsAdminRole()) {
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

$elList = null;
foreach ($brick->child as $child) {
    if ($child->name == 'product_list') {
        $elList = $child->elementList;
    }
}
$listTotal = 0;
$listPage = 1;
if (!empty($elList)) {
    $listTotal = $elList->total;
    $listPage = $elList->cfg->page;
}

// подгрузка кирпича пагинатора с параметрами
Brick::$builder->LoadBrickS('sitemap', 'paginator', $brick, array("p" => array(
    "total" => $listTotal,
    "page" => $listPage,
    "perpage" => EShopConfig::$instance->productPageCount,
    "uri" => $cat->URI(),
    "hidepn" => "0"
)));

// Вывод ключевых слов
if (!empty($dtl->metaKeys)) {
    Brick::$builder->SetGlobalVar('meta_keys', $dtl->metaKeys);
}

// Вывод описания
if (!empty($dtl->metaDescript)) {
    Brick::$builder->SetGlobalVar('meta_desc', $dtl->metaDescript);
}

// Вывод заголовка страницы
$metaTitle = !empty($dtl->metaTitle) ? $dtl->metaTitle : $cat->title;

$phrase = Brick::$builder->phrase;

if (empty($metaTitle)){
    $metaTitle = $v["deftitle"];
    $metaTitle = $phrase->Get('eshop', 'catalog_list_meta_title', $metaTitle);
}

Brick::$builder->SetGlobalVar('meta_title', $metaTitle);


?>