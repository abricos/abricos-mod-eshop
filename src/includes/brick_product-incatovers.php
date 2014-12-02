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

$el = $p['element'];
if (empty($el)) {
    $brick->content = "";
    return;
}

$man = EShopModule::$instance->GetManager()->cManager;

$catid = $el->catid;

$cfg = new CatalogElementListConfig();
$cfg->limit = 0;

array_push($cfg->catids, $catid);

$nbrick = Brick::$builder->LoadBrickS('eshop', 'product_list', $brick, array(
    "p" => array(
        "scroll" => 'true',
        "cfg" => $cfg
    )
));

$brick->content = Brick::ReplaceVarByData($brick->content, array(
    "result" => $nbrick->content
));

?>