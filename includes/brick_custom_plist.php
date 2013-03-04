<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$brick = Brick::$builder->brick;
$p = &$brick->param->param;

$limit = 3;
$more = $brick->param->var['more'];

if ($p['fulllist']){
	$limit = 99;
	$more = "";
}

$brick->content = Brick::ReplaceVarByData($brick->content, array(
		"more" => $more
));

$nbrick = Brick::$builder->LoadBrickS('eshop', 'product_list', $brick, array("p" => array(
	"custwhere" => $p['custwhere'],
	"count" => $limit
)));

if ($nbrick->totalElementCount == 0){
	$brick->content = "";
}

?>