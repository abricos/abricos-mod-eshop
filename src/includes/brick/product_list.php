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

if (is_object($p['cfg'])){
    $cfg = $p['cfg'];
} else {
    $cfg = new CatalogElementListConfig();
    $cfg->limit = $p['limit'];
}

if ($p['forcontent'] == 'true'){

    $cat = $man->CatalogByAdress();
    array_push($cfg->catids, $cat->id);

    $cfg->limit = EShopConfig::$instance->productPageCount;

    $adr = Abricos::$adress;
    $page = 0;
    if ($adr->level > 0){
        $page = $adr->dir[count($adr->dir) - 1];
    }

    if (preg_match("/^page[0-9]+/", $page)){
        $page = intval(substr($page, 4));
        if ($page > 0){
            $cfg->page = $page;
        }
    }

} else if (!empty($p['catPath'])){ // eshop/phones
    $cat = $man->CatalogByPath($p['catPath']);

    if (!empty($cat)){
        array_push($cfg->catids, $cat->id);
    }
} else {

    // return;
}

if ($p['notchildlist'] == 'false'){
    $catList = $cat->childs;
    for ($i = 0; $i < $catList->Count(); $i++){
        array_push($cfg->catids, $catList->GetByIndex($i)->id);
    }
}


$elList = $man->ProductList($cfg);
$brick->elementList = $elList;
if (empty($elList)){
    $brick->content = "";
    return;
}

$tplItem = $v['item'];
$tplList = $v['list'];

if (!isset($p['itemBrickName']) || empty($p['itemBrickName'])){
    $p['itemBrickName'] = 'product_list_item';
}

$p['firstMarker'] = isset($p['firstMarker']) ? $p['firstMarker'] : '';

$lst = array();
$lstz = array();
for ($i = 0; $i < $elList->Count(); $i++){

    // Override template by Element Type
    $el = $elList->GetByIndex($i);

    $elBrick = Brick::$builder->LoadBrickS('eshop', $p['itemBrickName'], null, array(
        "p" => array(
            "element" => $el
        )
    ));

    $contentItem = Brick::ReplaceVarByData($tplItem, array(
        "result" => $elBrick->content,
        "firstMarker" => $p['firstMarker']
    ));

    if (isset($el->ext['price']) && doubleval($el->ext['price']) > 0){
        $lst[] = $contentItem;
    } else {
        $lstz[] = $contentItem;
    }
}

$lst = $lst + $lstz;

$itemGroupCount = isset($p['itemGroupCount']) ? intval($p['itemGroupCount']) : 0;
$itemResult = "";

if ($itemGroupCount > 0){
    $p['firstGroupMarker'] = isset($p['firstGroupMarker']) ? $p['firstGroupMarker'] : '';
    $isFirstGroup = true;
    $groupCounter = 0;
    $temp = "";
    while (count($lst) > 0){

        $temp .= array_shift($lst);

        if ($groupCounter === $itemGroupCount - 1){
            $itemResult .= Brick::ReplaceVarByData($v['itemGroup'], array(
                "result" => $temp,
                "firstGroupMarker" => $isFirstGroup ? $p['firstGroupMarker'] : ""
            ));
            $isFirstGroup = false;
            $temp = "";
            $groupCounter = 0;
        } else {
            $groupCounter++;
        }
    }

    if ($temp !== ""){
        $itemResult .= Brick::ReplaceVarByData($v['itemGroup'], array(
            "result" => $temp,
            "firstGroupMarker" => $isFirstGroup ? $p['firstGroupMarker'] : ""
        ));
    }
} else {
    $itemResult = implode("", $lst);
}

$result = Brick::ReplaceVarByData($tplList, array(
    "result" => $itemResult
));

$brick->content = Brick::ReplaceVarByData($brick->content, array(
    "result" => $result,
    "brickid" => $brick->id,
    "currency" => $man->CurrencyDefault()->postfix,
    "classcolumn" => isset($p['classcolumn']) ? $p['classcolumn'] : ""
));

?>