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
/*
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

$overrideBricks = array();

$elTypeList = $man->ElementTypeList();
for ($i = 0; $i < $elTypeList->Count(); $i++) {
    $elType = $elTypeList->GetByIndex($i);
    $elTypeName = $elType->name;

    $sOverrideBrick = "product_list-".$elTypeName;
    $overrideBricks[$elType->id] = Brick::$builder->LoadBrickS("eshop", $sOverrideBrick);
}

$lst = "";
$lstz = "";
for ($i = 0; $i < $elList->Count(); $i++) {

    // Override template by Element Type
    $el = $elList->GetByIndex($i);
    $ovrBrick = $overrideBricks[$el->elTypeId];

    $pOvr = &$ovrBrick->param->param;
    $vOvr = &$ovrBrick->param->var;

    // Templates
    $tplClassColumn = isset($pOvr['classcolumn']) ? $pOvr['classcolumn'] : $p['classcolumn'];
    $tplPriceBuy = isset($vOvr['pricebuy']) ? $vOvr['pricebuy'] : $v['pricebuy'];
    $tplExtDivBuy1 = isset($vOvr['extdivbuy1']) ? $vOvr['extdivbuy1'] : $v['extdivbuy1'];
    $tplExtDivBuy2 = isset($vOvr['extdivbuy2']) ? $vOvr['extdivbuy2'] : $v['extdivbuy2'];
    $tplExtDivBuy3 = isset($vOvr['extdivbuy3']) ? $vOvr['extdivbuy3'] : $v['extdivbuy3'];

    $tplPriceOrder = isset($vOvr['priceorder']) ? $vOvr['priceorder'] : $v['priceorder'];
    $tplExtDivOrder1 = isset($vOvr['extdivorder1']) ? $vOvr['extdivorder1'] : $v['extdivorder1'];
    $tplExtDivOrder2 = isset($vOvr['extdivorder2']) ? $vOvr['extdivorder2'] : $v['extdivorder2'];
    $tplExtDivOrder3 = isset($vOvr['extdivorder3']) ? $vOvr['extdivorder3'] : $v['extdivorder3'];

    $tplRow = $v['row'];
    $tplTable = $v['table'];


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
        "classcolumn" => $tplClassColumn,
        "imgw" => $imgWidth,
        "imgh" => $imgHeight,
        "special" => $pr_special,
        "buybutton" => "",
        "image" => $image,
        "title" => $pTitle,
        "link" => $el->URI()
    );

    if (!empty($modCart)) {
        $cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybutton', null, array("p" => array(
            "product" => $el
        )));
        $replace["buybutton"] = $cartBrick->content;
    }

    if (doubleval($el->ext['price']) > 0) {
        $replace['price'] =
            Brick::ReplaceVarByData($tplPriceBuy, array(
                "price" => number_format($el->ext['price'], 2, ',', ' '),
                "price_int" => number_format($el->ext['price'], 0, ',', ' ')
            ));

        $replace['extdiv1'] = $tplExtDivBuy1;
        $replace['extdiv2'] = $tplExtDivBuy2;
        $replace['extdiv3'] = $tplExtDivBuy3;
    } else {
        $replace['price'] = $tplPriceOrder;

        $replace['extdiv1'] = $tplExtDivOrder1;
        $replace['extdiv2'] = $tplExtDivOrder2;
        $replace['extdiv3'] = $tplExtDivOrder3;
    }

    $replace["productid"] = $el->id;

    if (doubleval($el->ext['price']) > 0) {
        $lst .= Brick::ReplaceVarByData($tplRow, $replace);
    } else {
        $lstz .= Brick::ReplaceVarByData($tplRow, $replace);
    }

}

$result = Brick::ReplaceVarByData($tplTable, array(
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
/**/
?>