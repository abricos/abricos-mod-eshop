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

$modCart = Abricos::GetModule('eshopcart');

$imgWidth = bkint($p['imgw']);
$imgHeight = bkint($p['imgh']);

Abricos::GetModule('filemanager')->EnableThumbSize(array(array(
    "w" => $imgWidth,
    "h" => $imgHeight
)));

if (is_object($p['cfg'])) {
    $cfg = $p['cfg'];
} else {
    $cfg = new CatalogElementListConfig();
    $cfg->limit = $p['limit'];
}

if ($p['forcontent'] == 'true') {

    $cat = $man->CatalogByAdress();
    array_push($cfg->catids, $cat->id);

    $cfg->limit = EShopConfig::$instance->productPageCount;

    $adr = Abricos::$adress;
    $page = $adr->dir[count($adr->dir) - 1];

    if (preg_match("/^page[0-9]+/", $page)) {
        $page = intval(substr($page, 4));
        if ($page > 0) {
            $cfg->page = $page;
        }
    }

    if ($p['notchildlist'] == 'false') {
        $catList = $cat->childs;
        for ($i = 0; $i < $catList->Count(); $i++) {
            array_push($cfg->catids, $catList->GetByIndex($i)->id);
        }
    }
} else {

    // return;
}

$elList = $man->ProductList($cfg);
$brick->elementList = $elList;
if (empty($elList)) {
    $brick->content = "";
    return;
}

$lst = "";
$lstz = "";
for ($i = 0; $i < $elList->Count(); $i++) {
    $el = $elList->GetByIndex($i);

    $pTitle = addslashes(htmlspecialchars($el->title));

    $pr_spec = !empty($el->ext['akc']) ? $v['pr_akc'] : "";
    $pr_spec .= !empty($el->ext['new']) ? $v['pr_new'] : "";
    $pr_spec .= !empty($el->ext['hit']) ? $v['pr_hit'] : "";

    $pr_special = "";
    if (!empty($pr_spec)) {
        $pr_special = Brick::ReplaceVar($v["special"], "pr_spec", $pr_spec);
    }

    if (empty($el->foto)) {
        $image = $v["imgempty"];
    } else {
        $image = Brick::ReplaceVarByData($v["img"], array(
            "src" => $el->FotoSrc($imgWidth, $imgHeight)
        ));
    }
    $image = Brick::ReplaceVarByData($image, array(
        "w" => $imgWidth,
        "h" => $imgHeight
    ));

    $replace = array(
        "classcolumn" => $p['classcolumn'],
        "imgw" => $imgWidth,
        "imgh" => $imgHeight,
        "special" => $pr_special,
        "buybutton" => "",
        "image" => $image,
        "title" => $pTitle,
        "price" => number_format($el->ext['price'], 2, ',', ' '),
        "link" => $el->URI(),
        "productid" => $el->id
    );

    if (!empty($modCart)) {
        $cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybutton', null, array("p" => array(
            "product" => $el
        )));
        $replace["buybutton"] = $cartBrick->content;
    }

    if ($el->ext['price'] > 0) {
        $lst .= Brick::ReplaceVarByData($v['row'], $replace);
    } else {
        $lstz .= Brick::ReplaceVarByData($v['row'], $replace);
    }
}

$result = Brick::ReplaceVarByData($v['table'], array(
    "rows" => $lst.$lstz
));

if ($p['scroll'] == 'true') {
    $result = Brick::ReplaceVarByData($v["scrolldiv"], array(
        "result" => $result
    ));
}

$brick->content = Brick::ReplaceVarByData($brick->content, array(
    "display" => $p['display'],
    "result" => $result,
    "brickid" => $brick->id
));

if (!empty($modCart)) {
    $cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybuttonjsinit');
    $brick->content .= $cartBrick->content;
}

?>