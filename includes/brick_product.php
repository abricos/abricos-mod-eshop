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

$man = EShopModule::$instance->GetManager()->GetCatalogManager();

$elementid = $mod->currentProductId;
$el = $man->Product($elementid);
$cat = $man->Catalog($el->catid);

$bkParser = EShopManager::$instance->GetElementBrickParser($el);

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
	
	$bkParser->Parse($incBrick);
	
	$replaceBrick["brick_".$sIncBrick] = empty($incBrick) ? "" : $incBrick->content;
}
$brick->content = Brick::ReplaceVarByData($brick->content,  $replaceBrick);

$bkParser = EShopManager::$instance->GetElementBrickParser($el);
$replace = $bkParser->GetReplaceData();

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