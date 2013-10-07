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

$man = EShopModule::$instance->GetManager()->GetCatalogManager();

$optionsBase = $man->ElementTypeList()->Get(0)->options;

$cfg = new CatalogElementListConfig();

if ($p['onlyhit']=='true'){
	$cfg->where->AddByOption($optionsBase->GetByName("hit"), ">0");
}
if ($p['onlynew']=='true'){
	$cfg->where->AddByOption($optionsBase->GetByName("new"), ">0");
}
if ($p['onlyaction']=='true'){
	$cfg->where->AddByOption($optionsBase->GetByName("akc"), ">0");
}

$limit = intval($p['limit']);
$more = $v['more'];

if ($limit == 0){
	$limit = 0;
	$more = "";
}

$cfg->limit = $limit;

$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"more" => $more
));

$nbrick = Brick::$builder->LoadBrickS('eshop', 'product_list', $brick, array("p" => array(
	"cfg" => $cfg
)));

if ($nbrick->elementList->Count() == 0){
	$brick->content = "";
}

?>