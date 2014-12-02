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

$pQuery = Abricos::CleanGPC('g', 'q', TYPE_STR);
$pFField = Abricos::CleanGPC('g', 'eff', TYPE_STR);
$pFValue = Abricos::CleanGPC('g', 'ef', TYPE_STR);

$cManager = EShopModule::$instance->GetManager()->cManager;

$extFilterCol = "";

if (!empty($p['extfilter'])) {
    $aEF = explode(":", $p['extfilter']);

    $elTypeList = $cManager->ElementTypeList();
    $elTypeBase = $elTypeList->Get(0);
    $option = $elTypeBase->options->GetByName($aEF[0]);
    if (!empty($option) && $option->type == Catalog::TP_TABLE) {

        $lst = "";
        foreach ($option->values as $value) {
            $lst .= Brick::ReplaceVarByData($v['option'], array(
                "id" => $value['id'],
                "selected" => $value['id'] == $pFValue ? 'selected' : '',
                "tl" => htmlspecialchars($value['tl'])
            ));
        }
        $extFilterCol = Brick::ReplaceVarByData($v["textfilter"], array(
            "fld" => $option->name,
            "select" => Brick::ReplaceVarByData($v['select'], array(
                "value" => $pFValue,
                "tl" => empty($aEF[1]) ? "" : $aEF[1],
                "rows" => $lst
            ))
        ));
    }

}

$brick->content = Brick::ReplaceVarByData($brick->content, array(
    "query" => htmlspecialchars($pQuery),
    "extfiltercol" => $extFilterCol,
    "brickid" => $brick->id
));

?>