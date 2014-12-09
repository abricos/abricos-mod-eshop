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

$modCart = Abricos::GetModule('eshopcart');

$imgWidth = bkint($p['imgw']);
$imgHeight = bkint($p['imgh']);

Abricos::GetModule('filemanager')->EnableThumbSize(array(
    array(
        "w" => $imgWidth,
        "h" => $imgHeight
    )
));

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
    $page = 0;
    if ($adr->level > 0) {
        $page = $adr->dir[count($adr->dir) - 1];
    }

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

$tplRow = $v['row'];
$tplTable = $v['table'];

$lst = "";
$lstz = "";
for ($i = 0; $i < $elList->Count(); $i++) {

    // Override template by Element Type
    $el = $elList->GetByIndex($i);
    $ovrBrick = $overrideBricks[$el->elTypeId];

    $pOvr = &$ovrBrick->param->param;
    $vOvr = &$ovrBrick->param->var;

    $replace = array();

    for ($ii = 1; $ii <= 3; $ii++) {
        if (isset($el->ext['sklad']) && intval($el->ext['sklad']) > 0) {
            $replace['extdivquantity'.$ii] = isset($vOvr['extdivbuy'.$ii]) ? $vOvr['extdivbuy'.$ii] : $v['extdivbuy'.$ii];
        } else {
            $replace['extdivquantity'.$ii] = isset($vOvr['extdivorder'.$ii]) ? $vOvr['extdivorder'.$ii] : $v['extdivorder'.$ii];
        }
        if (isset($el->ext['price']) && doubleval($el->ext['price']) > 0) {
            $replace['extdivprice'.$ii] = isset($vOvr['extdivprice'.$ii]) ? $vOvr['extdivprice'.$ii] : $v['extdivprice'.$ii];
        } else {
            $replace['extdivprice'.$ii] = isset($vOvr['extdivnoprice'.$ii]) ? $vOvr['extdivnoprice'.$ii] : $v['extdivnoprice'.$ii];
        }
    }

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

    $replace["classcolumn"] = isset($pOvr['classcolumn']) ? $pOvr['classcolumn'] : $p['classcolumn'];;
    $replace["imgw"] = $imgWidth;
    $replace["imgh"] = $imgHeight;
    $replace["special"] = $pr_special;
    $replace["buybutton"] = "";
    $replace["image"] = $image;
    $replace["title"] = $pTitle;
    $replace["link"] = $el->URI();

    if (!empty($modCart)) {
        $cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybutton', null, array(
            "p" => array(
                "product" => $el
            )
        ));
        $replace["buybutton"] = $cartBrick->content;
    }

    if (isset($el->ext['price']) && doubleval($el->ext['price']) > 0) {
        $tplPriceBuy = isset($vOvr['pricebuy']) ? $vOvr['pricebuy'] : $v['pricebuy'];

        $replace['price'] = Brick::ReplaceVarByData($tplPriceBuy, array(
            "price" => number_format($el->ext['price'], 2, ',', ' '),
            "price_int" => number_format($el->ext['price'], 0, ',', ' ')
        ));

    } else {
        $replace['price'] = isset($vOvr['priceorder']) ? $vOvr['priceorder'] : $v['priceorder'];;
    }

    $replace["productid"] = $el->id;

    if (isset($el->ext['price']) && doubleval($el->ext['price']) > 0) {
        $lst .= Brick::ReplaceVarByData($tplRow, $replace);
    } else {
        $lstz .= Brick::ReplaceVarByData($tplRow, $replace);
    }

}

$result = Brick::ReplaceVarByData($tplTable, array(
    "rows" => $lst.$lstz
));

$brick->content = Brick::ReplaceVarByData($brick->content, array(
    "display" => $p['display'],
    "result" => $result,
    "brickid" => $brick->id,
    "currency" => $man->CurrencyDefault()->postfix
));

if (!empty($modCart)) {
    $cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybuttonjsinit');
    $brick->content .= $cartBrick->content;
}

?>