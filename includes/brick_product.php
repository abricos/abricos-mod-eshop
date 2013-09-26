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
 *		который можно задать в параметрах из родителя  
 *
 */

$brick = Brick::$builder->brick;
$p = &$brick->param->param;
$v = &$brick->param->var;

$mod = EShopModule::$instance;
EShopModule::$instance->GetManager();

$man = EShopModule::$instance->GetManager()->cManager;
$modCart = Abricos::GetModule('eshopcart');

$elementid = $mod->currentProductId;
$el = $man->Product($elementid);
$cat = $man->Catalog($el->catid);

// динамически подключить кирпичи
$sIncBricks =  $v['includebrick'];
if (!empty($p['includebrick'])){
	$sIncBricks .= ",".$p['includebrick'];
}

$replaceBrick = array();

$aIncBricks = explode(",", $sIncBricks);
foreach ($aIncBricks as $sIncBrick){
	$sIncBrick = trim($sIncBrick);
	if (empty($sIncBrick)){ continue; }
	$incBrick = Brick::$builder->LoadBrickS("eshop", $sIncBrick, null, array("p" => array(
		"element" => $el,
		"cat" => $cat
	)));
	
	$replaceBrick["brick_".$sIncBrick] = empty($incBrick) ? "" : $incBrick->content;
}
$brick->content = Brick::ReplaceVarByData($brick->content,  $replaceBrick);

$replace = array(
	"link" => $el->URI(),
	"elementid" => $elementid,
	"title" => $el->title,
	"name" => $el->name,
	"cattitle" => $cat->title,
	"catdesc" => $cat->detail->descript
);

$ogList = $man->ElementOptionGroupList();
$ogSpec = $ogList->GetByName("specific");
$lstOGSpec = "";

$elTypeList = $man->ElementTypeList();

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

		$replace["fldtl_".$optInfo->name] = $optInfo->title;
		
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