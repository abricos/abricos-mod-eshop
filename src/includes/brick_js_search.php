<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$cManager = EShopModule::$instance->GetManager()->cManager;
$query = Abricos::CleanGPC('g', 'q', TYPE_STR);
$extFilterField = Abricos::CleanGPC('g', 'eff', TYPE_STR);
$extFilterValue = Abricos::CleanGPC('g', 'ef', TYPE_STR);

header('Content-type: text/plain');

$arr = $cManager->SearchAutoComplete($query, $extFilterField, $extFilterValue);

$return = array();

for ($i = 0; $i < count($arr); $i++) {
    $item = new stdClass();
    $item->tl = $arr[$i];
    $return[] = $item;
}

$json = json_encode($return);
print_r($json);
exit;

?>