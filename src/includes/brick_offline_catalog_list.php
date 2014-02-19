<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$brick = Brick::$builder->brick;
$db = Abricos::$db;
$p = & $brick->param->param;
$v = & $brick->param->var;

$man = EShopManager::$instance->cManager;

$catMain = $man->CatalogList()->Find($p['catid']);
$catList = $catMain->childs;
$lst = "";

$imgWidth = bkint($p['imgw']);
$imgHeight = bkint($p['imgh']);

for ($i = 0; $i < $catList->Count(); $i++) {
    $cat = $catList->GetByIndex($i);

    $link = $cat->name."/index.html";

    if (empty($cat->foto)) {
        $image = $v["imgempty"];
    } else {

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

$cattitle = "";
if ($catMain->id > 0) {
    $cattitle = Brick::ReplaceVarByData($v['cattitle'], array(
        "title" => $catMain->title
    ));
}

$brickPList = Brick::$builder->LoadBrickS("eshop", "offline_product_list", null, array(
    "p" => array(
        "dir" => $p['dir'],
        "catid" => $p['catid']
    )
));

$brick->content = Brick::ReplaceVarByData($brick->content, array(
    "cattitle" => $cattitle,
    "result" => Brick::ReplaceVarByData($v['table'], array(
            "rows" => $lst
        )),
    "productlist" => $brickPList->content
));

?>