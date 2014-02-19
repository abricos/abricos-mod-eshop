<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$brick = Brick::$builder->brick;
$p = & $brick->param->param;
$v = & $brick->param->var;

$man = EShopModule::$instance->GetManager()->cManager;
$productId = $p['productid'];
$el = $man->Element($p['productid']);

if (empty($el)) {
    $brick->content = "";
    return;
}

$cat = $man->Catalog($el->catid);

// заменяем данные по текущей категории, если нужно
$brick->content = Brick::ReplaceVarByData($brick->content, array(
    "cattitle" => $cat->title,
    "catdesc" => $cat->detail->descript
));

$arr = explode("x", $p['imgsize']);
$size = array(
    "w" => $arr[0] * 1,
    "h" => $arr[1] * 1
);

$imgWidth = $size['w'];
$imgHeight = $size['h'];

Abricos::GetModule('filemanager')->EnableThumbSize(array($size));

$pTitle = addslashes(htmlspecialchars($el->title));

$pTitleSeo = "";
if (EShopConfig::$instance->seo) {
    $pTitleSeo = translateruen($el->title);
}

$imgSmList = "";
if (empty($el->foto)) {
    $image = $v["imgempty"];
} else {

    $imgSrc = OfflineManager::$instance->WriteImage($p['dir'], $el->foto, $imgWidth, $imgHeight);

    $image = Brick::ReplaceVarByData($v["img"], array(
        "src" => $imgSrc
    ));
}

$brick->content = Brick::ReplaceVarByData($brick->content, array(
    "options" => Brick::ReplaceVarByData($brick->param->var["options"], array(
            "overoptions" => $brick->param->var["options".$el->elTypeId],
        )),
    "image" => $image,
    "imagelist" => $imgSmList,
    "otherphoto" => $otherphoto,
    "cphoto" => $cphoto
));

$replace = array();

$elTypeList = $man->ElementTypeList();

for ($i = 0; $i < 2; $i++) {
    $elOpts = $el->detail->optionsBase;
    if ($i == 0) {
        $elType = $elTypeList->Get(0);
    } else if ($el->elTypeId > 0) {
        $elType = $elTypeList->Get($el->elTypeId);
        $elOpts = $el->detail->optionsPers;
    } else {
        continue;
    }

    for ($ii = 0; $ii < $elType->options->Count(); $ii++) {
        $optInfo = $elType->options->GetByIndex($ii);
        $fld = "fld_".$optInfo->name;

        if ($optInfo->type == Catalog::TP_TABLE) {
            $tblval = $optInfo->values[$elOpts[$optInfo->name]];
            if (!empty($tblval)) {
                $replace[$fld] = $tblval['tl'];
            }
        } else {
            $replace[$fld] = $elOpts[$optInfo->name];
        }

        if (empty($replace[$fld])) {
            // Если опция пуста - пробел, чтобы не рушить верстку
            $replace[$fld] = '&nbsp;';
        }

        $replace["fldnm_".$optInfo->name] = $optInfo->title;
    }
}

$replace["fld_name"] = $el->title;

$tpTable = $brick->param->var["table"];
$tpRow = $brick->param->var["row"];

$brick->content = Brick::ReplaceVarByData($brick->content, array(
    "optlist" => Brick::ReplaceVarByData($tpTable, array("rows" => $tpRow))
));

$brick->content = Brick::ReplaceVarByData($brick->content, $replace);

/*

$elTypeId = $el['eltid'];
$elTypeList = $catalogManager->ElementTypeListArray();
if (!empty($elTypeList[$elTypeId])){
	$elTypeName = $elTypeList[$elTypeId]['nm'];
	if (!empty($brick->param->var['table-'.$elTypeName])){
		$tpTable = $brick->param->var['table-'.$elTypeName];
	}
	if (!empty($brick->param->var['row-'.$elTypeName])){
		$tpRow = $brick->param->var['row-'.$elTypeName];
	}
}

$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"optlist" => Brick::ReplaceVarByData($tpTable, array("rows" => $tpRow))
));
$brick->content = Brick::ReplaceVarByData($brick->content, $replace);

/**/
?>