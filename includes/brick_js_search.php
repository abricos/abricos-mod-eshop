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


exit;

/*
$man = BlogModule::$instance->GetManager();

$tags = $man->TagListByLikeQuery($query);
for ($i=0;$i<count($tags);$i++){
	print ($tags[$i]."\n");
}
/**/

?>