<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @copyright 2012-2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

// product-foto - кирпич подключаемый из скрипта кирпича product

$brick = Brick::$builder->brick;
$p = &$brick->param->param;
$v = &$brick->param->var;

/** @var CatalogElement $el */
$el = $p['element'];
if (empty($el)){
    $brick->content = "";
    return;
}

$p['alwaysList'] = isset($p['alwaysList']) ? $p['alwaysList'] : false;
$p['fotoCrop'] = isset($p['fotoCrop']) ? intval($p['fotoCrop']) : 1;
$p['fotosmCrop'] = isset($p['fotosmCrop']) ? intval($p['fotosmCrop']) : 1;

$a = explode("x", $p['fotosize']);
$fotoSize = array(
    "w" => intval($a[0]),
    "h" => intval($a[1]),
    "cm" => $p['fotoCrop']
);

$a = explode("x", $p['fotosmsize']);
$fotoSmallSize = array(
    "w" => intval($a[0]),
    "h" => intval($a[1]),
    "cm" => $p['fotosmCrop']
);

/** @var FileManagerModule $modFM */
$modFM = Abricos::GetModule('filemanager');
$modFM->EnableThumbSize(array(
    $fotoSize,
    $fotoSmallSize
));

$fotoList = $el->detail->fotoList;

$pTitle = addslashes(htmlspecialchars($el->title));
$pTitleSeo = "";
if (EShopConfig::$instance->seo){
    $pTitleSeo = translateruen($el->title);

    for ($i = 0; $i < $fotoList->Count(); $i++){
        $foto = $fotoList->GetByIndex($i);
        $fnm = $pTitleSeo;
        if ($i > 0){
            $fnm .= "-".$i;
        }
        $foto->name = $fnm.".".$foto->extension;
    }
}

$lstFotoSmall = $otherphoto = "";
if ($fotoList->Count() == 0){
    $tpFoto = $v["fotoempty"];
} else {
    $foto = $fotoList->GetByIndex(0);

    $tpFoto = Brick::ReplaceVarByData($v["foto"], array(
        "src" => $foto->Link($fotoSize['w'], $fotoSize['h'], $fotoSize['cm']),
        "srcf" => $foto->Link()
    ));

    $lstFotoSmall = "";

    if (!empty($p['alwaysList']) || $fotoList->Count() > 1){
        for ($i = 0; $i < $fotoList->Count(); $i++){
            $foto = $fotoList->GetByIndex($i);

            $lstFotoSmall .= Brick::ReplaceVarByData($v["fotosmall"], array(
                "src" => $foto->Link($fotoSmallSize['w'], $fotoSmallSize['h'], $fotoSmallSize['cm']),
                "srcf" => $foto->Link(),
                "fid" => $foto->filehash
            ));
        }
        $lstFotoSmall = Brick::ReplaceVarByData($v['fotoSmallList'], array(
            "list" => $lstFotoSmall
        ));
    }
}

$brick->content = Brick::ReplaceVarByData($brick->content, array(
    "foto" => $tpFoto,
    "fotolist" => $lstFotoSmall,
    "w" => $fotoSize['w'],
    "h" => $fotoSize['h'],
    "smw" => $fotoSmallSize['w'],
    "smh" => $fotoSmallSize['w'],
    "brickid" => $brick->id
));
