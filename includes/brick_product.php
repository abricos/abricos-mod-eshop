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

$mod = EShopModule::$instance;
EShopModule::$instance->GetManager();

$man = EShopModule::$instance->GetManager()->cManager;
$modCart = Abricos::GetModule('eshopcart');


$productid = $mod->currentProductId;
$el = $man->Product($productid);
$cat = $man->Catalog($el->catid);

// заменяем данные по текущей категории, если нужно
$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"cattitle" => $cat->title,
	"catdesc" => $cat->detail->descript
));

$brickFoto = Brick::$builder->LoadBrickS("eshop", "product-foto", null, array("p" => array(
	"element" => $el
)));

$brick->content = Brick::ReplaceVarByData($brick->content,  array(
	"options" => Brick::ReplaceVarByData($v["options"],  array(
		"overoptions" => $v["options".$el->elTypeId],
	)),
	"brickfoto" => $brickFoto->content
));

$replace = array(
	"link" => $el->URI(),
	"elementid" => $productid,
	"fld_name" => $el->title
);

if (!empty($modCart)){
	$cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybutton', null, array("p" => array(
		"product" => $el
	)));
	$replace["buybutton"] = $cartBrick->content;
	
	$cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybuttonjsinit');
	$brick->content .= $cartBrick->content;
}

$ogList = $man->ElementOptionGroupList();
$ogSpec = $ogList->GetByName("specific");
$lstOGSpec = "";

$elTypeList = $man->ElementTypeList();
$replaceOption = array();

for ($i=0;$i<2;$i++){
	$elOpts = $el->detail->optionsBase;
	if ($i == 0){
		$elType = $elTypeList->Get(0);
	}else if ($el->elTypeId > 0){
		$elType = $elTypeList->Get($el->elTypeId);
		$elOpts = $el->detail->optionsPers;
	}else{
		continue;
	}

	for ($ii=0; $ii<$elType->options->Count(); $ii++){
		$optInfo = $elType->options->GetByIndex($ii);
		$fld = "fld_".$optInfo->name;

		if ($optInfo->type == Catalog::TP_TABLE){
			$tblval = $optInfo->values[$elOpts[$optInfo->name]];
			if (!empty($tblval)){
				$replace[$fld] = $tblval['tl'];
			}
		}else{
			if ($optInfo->name == 'price'){
				$replace[$fld] = number_format($elOpts[$optInfo->name], 2, ',', ' ');
			}else{
				$replace[$fld] = $elOpts[$optInfo->name];
			}
		}

		if (!empty($v['option-'.$optInfo->name])){
			$replaceOption['option-'.$optInfo->name] = empty($replace[$fld]) ? "" : $v['option-'.$optInfo->name];
		}
		
		if (empty($replace[$fld])){
			// Если опция пуста - пробел, чтобы не рушить верстку
			$replace[$fld] = '&nbsp;';
		}else{
			
			if ($ogSpec->id == $optInfo->groupid){
				$lstOGSpec .= Brick::ReplaceVarByData($v['options-specific-row'], array(
					"tl" => $optInfo->title,
					"vl" => $replace[$fld]
				));
			}
		}

		$replace["fldnm_".$optInfo->name] = $optInfo->title;
		
	}
}
$replace['disspecific'] = "none";
if (!empty($lstOGSpec)){
	$replace['options-specific'] = Brick::ReplaceVarByData($v['options-specific'], array(
		"rows" => $lstOGSpec
	));
	$replace['disspecific'] = " ";
}else{
	$replace['options-specific'] = "";	
}

$tpTable = $v["table"];
$tpRow = $v["row"];

$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"optlist" => Brick::ReplaceVarByData($tpTable, array("rows" => $tpRow))
));
$brick->content = Brick::ReplaceVarByData($brick->content, $replaceOption);
$brick->content = Brick::ReplaceVarByData($brick->content, $replace);

// Вывод заголовка страницы.
if (!empty($el->detail->metaTitle) && $el->detail->metaTitle !="&nbsp;"){
	Brick::$builder->SetGlobalVar('meta_title', $el->detail->metaTitle);
} else if (!empty($el->title) && $el->title !="&nbsp;"){
	Brick::$builder->SetGlobalVar('meta_title', $el->title);
}
// Вывод ключевых слов
if (!empty($el->detail->metaKeys) && $el->detail->metaKeys !="&nbsp;"){
	Brick::$builder->SetGlobalVar('meta_keys', $el->detail->metaKeys);
}
// Вывод описания
if (!empty($el->detail->metaDesc) && $el->detail->metaDesc !="&nbsp;"){
	Brick::$builder->SetGlobalVar('meta_desc', $el->detail->metaDesc);
}

?>