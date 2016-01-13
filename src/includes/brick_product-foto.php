<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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

$a = explode("x", $p['fotosize']);
$fotoSize = array(
    "w" => intval($a[0]),
    "h" => intval($a[1])
);

$a = explode("x", $p['fotosmsize']);
$fotoSmallSize = array(
    "w" => intval($a[0]),
    "h" => intval($a[1])
);

Abricos::GetModule('filemanager')->EnableThumbSize(array(
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
        "src" => $foto->Link($fotoSize['w'], $fotoSize['h']),
        "srcf" => $foto->Link()
    ));

    $lstFotoSmall = "";

    if (!empty($p['alwaysList']) ||  $fotoList->Count() > 1){
        for ($i = 0; $i < $fotoList->Count(); $i++){
            $foto = $fotoList->GetByIndex($i);

            $lstFotoSmall .= Brick::ReplaceVarByData($v["fotosmall"], array(
                "src" => $foto->Link($fotoSmallSize['w'], $fotoSmallSize['h']),
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

?>