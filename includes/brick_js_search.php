<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$cManager = EShopModule::$instance->GetManager()->cManager;
$query = Abricos::CleanGPC('p', 'query', TYPE_STR);
header('Content-type: text/plain');

$arr = $cManager->SearchAutoComplete($query);

for ($i=0;$i<count($arr);$i++){
	print($arr[$i]."\n");
}

exit;

?>