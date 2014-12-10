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

$el = $p['element'];

$bkParser = EShopManager::$instance->GetElementBrickParser($el);


$modCart = Abricos::GetModule('eshopcart');

$imgWidth = bkint($p['imgw']);
$imgHeight = bkint($p['imgh']);

Abricos::GetModule('filemanager')->EnableThumbSize(array(
    array(
        "w" => $imgWidth,
        "h" => $imgHeight
    )
));

$elTypeBricks = array();

$elTypeList = $man->ElementTypeList();
for ($i = 0; $i < $elTypeList->Count(); $i++) {
    $elType = $elTypeList->GetByIndex($i);
    $elTypeName = $elType->name;

    $elTypeBricks[$elType->id] =
        Brick::$builder->LoadBrickS("eshop", $brick->name."-tp-".$elTypeName);
}


$ovrBrick = $elTypeBricks[$el->elTypeId];

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
$replace["brickid"] = $brick->id;
$replace["productid"] = $el->id;
$replace["currency"] = $man->CurrencyDefault()->postfix;

$brick->content = Brick::ReplaceVarByData($brick->content, $replace);

?>