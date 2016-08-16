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

$manEShop = EShopModule::$instance->GetManager();
$man = $manEShop->cManager;

$optionsBase = $man->ElementTypeList()->Get(0)->options;

$cfg = new CatalogElementListConfig();

if (isset($p['onlyhit']) && $p['onlyhit'] == 'true') {
    $cfg->where->AddByOption($optionsBase->GetByName("hit"), ">0");
}
if (isset($p['onlynew']) && $p['onlynew'] == 'true') {
    $cfg->where->AddByOption($optionsBase->GetByName("new"), ">0");
}
if (isset($p['onlyaction']) && $p['onlyaction'] == 'true') {
    $cfg->where->AddByOption($optionsBase->GetByName("akc"), ">0");
}

$limit = intval($p['limit']);
$more = $v['more'];

if ($limit == 0) {
    $limit = 0;
    $more = "";
}

$cfg->limit = $limit;

$brick->content = Brick::ReplaceVarByData($brick->content, array(
    "more" => $more,
    "brickid" => $brick->id
));

$nbrick = Brick::$builder->LoadBrickS('eshop', 'product_list', $brick, array(
    "p" => array(
        "scroll" => 'true',
        "cfg" => $cfg
    )
));

if ($nbrick->elementList->Count() === 0) {
    $brick->content = "";
}
