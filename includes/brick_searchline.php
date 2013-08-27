<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$brick = Brick::$builder->brick;

$query = Abricos::CleanGPC('g', 'q', TYPE_STR);
$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"query" => htmlspecialchars($query)
));

?>