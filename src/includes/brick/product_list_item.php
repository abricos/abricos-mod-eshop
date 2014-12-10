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

// TODO: optimize
$el = $man->Product($el->id);

$modCart = Abricos::GetModule('eshopcart');

$imgWidth = bkint($p['imgw']);
$imgHeight = bkint($p['imgh']);

Abricos::GetModule('filemanager')->EnableThumbSize(array(
    array(
        "w" => $imgWidth,
        "h" => $imgHeight
    )
));


$builder = EShopModule::$instance->GetManager()->GetElementBrickBuilder($el, $brick);
$builder->Build();

$replace = array();

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

$replace["imgw"] = $imgWidth;
$replace["imgh"] = $imgHeight;
$replace["buybutton"] = "";
$replace["image"] = $image;

if (!empty($modCart)) {
    $cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybutton', null, array(
        "p" => array(
            "product" => $el
        )
    ));
    $replace["buybutton"] = $cartBrick->content;
}

/*
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
$replace["currency"] = $man->CurrencyDefault()->postfix;

/**/

$brick->content = Brick::ReplaceVarByData($brick->content, $replace);

?>