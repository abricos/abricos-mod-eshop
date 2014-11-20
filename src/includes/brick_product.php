<?php

/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */
/*
 * Кирпич product
 * 
 * Интернет-магазины очень разные, а перегрузка этого кирпича влечет
 * неприятные последствия сопровождения с выходом новых версий.
 * Поэтому разработан приближено к универсальному механизм сборки страницы 
 * товара, смыслк которого в разбиение на блоки. Эти блоки уже проще
 * сопровождать в шаблонах, перегружая не весь кирпич, а только определенный
 * блок
 * 
 * Переменные:
 * includebrick - перечень подключаемых кирпичей
 * 
 * Параметры:
 * includebrick - дополнительный перечень подключаемых кирпичей, 
 * 		который можно задать в параметрах из родителя  
 *
 */

$brick = Brick::$builder->brick;
$p = &$brick->param->param;
$v = &$brick->param->var;
$ph = &$brick->param->phrase;


$mod = EShopModule::$instance;
EShopModule::$instance->GetManager();

$man = EShopModule::$instance->GetManager()->cManager;

$elementid = $mod->currentProductId;
$el = $man->Product($elementid);
$cat = $man->Catalog($el->catid);

$elTypeList = $man->ElementTypeList();
$elType = $elTypeList->Get($el->elTypeId);
$sOvrBrick = "product-tp-".$elType->name;

$ovrBrick = Brick::$builder->LoadBrickS("eshop", $sOvrBrick);

$pOvr = &$ovrBrick->param->param;
$vOvr = &$ovrBrick->param->var;
$phOvr = &$ovrBrick->param->phrase;

$vIncludeBrick = isset($vOvr['includebrick']) ? $vOvr['includebrick'] : $v['includebrick'];
$pIncludeBrick = isset($p['includebrick']) ? $p['includebrick'] : '';
$brick->content = !$ovrBrick->isError && !empty($ovrBrick->content) ? $ovrBrick->content : $brick->content;

if (is_array($phOvr)){
    foreach($phOvr as $key => $value){
        $ph[$key] = $value;
    }
}

$bkParser = EShopManager::$instance->GetElementBrickParser($el);

// динамически подключить кирпичи
$sIncBricks = $vIncludeBrick;
if (!empty($pIncludeBrick)) {
    $sIncBricks .= ",".$p['includebrick'];
}

$replaceBrick = array();

$aIncBricks = explode(",", $sIncBricks);
foreach ($aIncBricks as $sIncBrick) {
    $sIncBrick = trim($sIncBrick);
    if (empty($sIncBrick)) {
        continue;
    }
    $incBrickParams = array(
        "element" => $el,
        "cat" => $cat
    );

    // если есть параметры
    $aIncBrickParam = explode("|", $sIncBrick);
    if (count($aIncBrickParam) > 1) {
        $sIncBrick = $aIncBrickParam[0];
        for ($i = 1; $i < count($aIncBrickParam); $i++) {
            $sPrm = $aIncBrickParam[$i];
            $aPrm = explode("=", $sPrm);
            if (count($aPrm) == 2) {
                $incBrickParams[$aPrm[0]] = $aPrm[1];
            }
        }
    }

    $incBrick = Brick::$builder->LoadBrickS("eshop", $sIncBrick, null, array("p" => $incBrickParams));

    $bkParser->Parse($incBrick);

    $replaceBrick["brick_".$sIncBrick] = empty($incBrick) ? "" : $incBrick->content;
}
$brick->content = Brick::ReplaceVarByData($brick->content, $replaceBrick);

$bkParser = EShopManager::$instance->GetElementBrickParser($el);
$replace = $bkParser->GetReplaceData();
$replace['brickid'] = $brick->id;

$brick->content = Brick::ReplaceVarByData($brick->content, $replace);

// Вывод заголовка страницы.
if (!empty($el->detail->metaTitle) && $el->detail->metaTitle != "&nbsp;") {
    Brick::$builder->SetGlobalVar('meta_title', $el->detail->metaTitle);
} else if (!empty($el->title) && $el->title != "&nbsp;") {
    Brick::$builder->SetGlobalVar('meta_title', $el->title);
}
// Вывод ключевых слов
if (!empty($el->detail->metaKeys) && $el->detail->metaKeys != "&nbsp;") {
    Brick::$builder->SetGlobalVar('meta_keys', $el->detail->metaKeys);
}
// Вывод описания
if (!empty($el->detail->metaDesc) && $el->detail->metaDesc != "&nbsp;") {
    Brick::$builder->SetGlobalVar('meta_desc', $el->detail->metaDesc);
}
?>