<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

// product-foto - кирпич подключаемый из скрипта кирпича product

$brick = Brick::$builder->brick;
$p = & $brick->param->param;
$v = & $brick->param->var;

$el = $p['element'];
if (empty($el)) {
    $brick->content = "";
    return;
}

$a = explode("x", $p['fotosize']);
$fotoSize = array("w" => intval($a[0]), "h" => intval($a[1]));

$a = explode("x", $p['fotosmsize']);
$fotoSmallSize = array("w" => intval($a[0]), "h" => intval($a[1]));

Abricos::GetModule('filemanager')->EnableThumbSize(array($fotoSize, $fotoSmallSize));

if (false) {
    $el = new CatalogElement();
}

$fotoList = $el->detail->fotoList;

$pTitle = addslashes(htmlspecialchars($el->title));
$pTitleSeo = "";
if (EShopConfig::$instance->seo) {
    $pTitleSeo = translateruen($el->title);

    for ($i = 0; $i < $fotoList->Count(); $i++) {
        $foto = $fotoList->GetByIndex($i);
        $fnm = $pTitleSeo;
        if ($i > 0) {
            $fnm .= "-".$i;
        }
        $foto->name = $fnm.".".$foto->extension;
    }
}

$lstFotoSmall = $otherphoto = "";
if ($fotoList->Count() == 0) {
    $tpFoto = $v["fotoempty"];
} else {
    $foto = $fotoList->GetByIndex(0);

    $tpFoto = Brick::ReplaceVarByData($v["foto"], array(
        "src" => $foto->Link($fotoSize['w'], $fotoSize['h']),
        "srcf" => $foto->Link()
    ));

    for ($i = 0; $i < $fotoList->Count(); $i++) {
        $foto = $fotoList->GetByIndex($i);

        $lstFotoSmall .= Brick::ReplaceVarByData($v["fotosmall"], array(
            "src" => $foto->Link($fotoSmallSize['w'], $fotoSmallSize['h']),
            "srcf" => $foto->Link(),
            "fid" => $foto->filehash
        ));
    }
}

$brick->content = Brick::ReplaceVarByData($brick->content, array(
    "foto" => $tpFoto,
    "fotolist" => $lstFotoSmall
));
$brick->content = Brick::ReplaceVarByData($brick->content, array(
    "w" => $fotoSize['w'],
    "h" => $fotoSize['h'],
    "smw" => $fotoSmallSize['w'],
    "smh" => $fotoSmallSize['w'],
    "brickid" => $brick->id
));

?>