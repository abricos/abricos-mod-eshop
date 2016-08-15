<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @copyright 2012-2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
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

/** @var FileManagerModule $fmModule */
$fmModule = Abricos::GetModule('filemanager');

$imgWidth = bkint($p['imgw']);
$imgHeight = bkint($p['imgh']);
$imgCropMode = isset($p['imgCropMode']) ? bkint($p['imgCropMode']) : 1;

$fmModule->EnableThumbSize(array(
    array(
        "w" => $imgWidth,
        "h" => $imgHeight,
        "cm" => $imgCropMode
    )
));

$builder = EShopModule::$instance->GetManager()->GetElementBrickBuilder($el, $brick);
$builder->Build();

if (empty($el->foto)){
    $image = $v["imgempty"];
} else {
    $image = Brick::ReplaceVarByData($v["img"], array(
        "src" => $el->FotoSrc($imgWidth, $imgHeight, $imgCropMode)
    ));
}

$replace = array(
    "imgw" => $imgWidth,
    "imgh" => $imgHeight,
    "buybutton" => "",
    "image" => Brick::ReplaceVarByData($image, array(
        "w" => $imgWidth,
        "h" => $imgHeight
    ))
);

if (!empty($modCart)){
    $cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybutton', null, array(
        "p" => array(
            "product" => $el
        )
    ));
    $replace["buybutton"] = $cartBrick->content;
}

$brick->content = Brick::ReplaceVarByData($brick->content, $replace);
