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

$replace = array(
    "buybutton" => ""
);

$modCart = Abricos::GetModule('eshopcart');

if (!empty($modCart)) {
    $cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybutton', null, array(
        "p" => array(
            "product" => $el
        )
    ));
    $replace["buybutton"] = $cartBrick->content;

    $cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybuttonjsinit');
    $brick->content .= $cartBrick->content;
}

$brick->content = Brick::ReplaceVarByData($brick->content, $replace);
