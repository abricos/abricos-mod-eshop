<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$catManager = EShopModule::$instance->GetManager()->GetCatalogManager();
$query = Abricos::CleanGPC('p', 'query', TYPE_STR);
$extFilterField = Abricos::CleanGPC('p', 'eff', TYPE_STR);
$extFilterValue = Abricos::CleanGPC('p', 'ef', TYPE_STR);

header('Content-type: text/plain');

$arr = $catManager->SearchAutoComplete($query, $extFilterField, $extFilterValue);

for ($i=0;$i<count($arr);$i++){
	print($arr[$i]."\n");
}

exit;

?>