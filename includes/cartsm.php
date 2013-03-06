<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$brick = Brick::$builder->brick;
$mod = Abricos::GetModule('eshop');
$modMan = $mod->GetManager();

$info = $modMan->CartInfo();

$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"count" => $info['qty'],
	"summ" => $info['sum']
));

?>