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

$bkParser = EShopManager::$instance->GetElementBrickParser($el);
$replace = $bkParser->ParseOptionGroups($brick);

$brick->content = Brick::ReplaceVarByData($brick->content, $replace);

?>