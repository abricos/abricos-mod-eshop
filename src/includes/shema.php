<?php
/**
 * Схема таблиц данного модуля
 *
 * @package Abricos
 * @subpackage Eshop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$charset = "CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'";
$updateManager = Ab_UpdateManager::$current;
$db = Abricos::$db;
$pfx = $db->prefix;

Abricos::GetModule('eshop')->GetManager();
EShopManager::$instance->RolesDisable();

$cManager = EShopManager::$instance->cManager;

function EShopOptionAppend($name, $title, $type, $size = '') {
    $cManager = EShopManager::$instance->cManager;

    $d = new stdClass();
    $d->nm = $name;
    $d->tl = $title;
    $d->tp = $type;
    $d->sz = $size;
    return $cManager->ElementOptionSave(0, $d);
}

if ($updateManager->isInstall()) {

    EShopOptionAppend('desc', 'Описание товара', Catalog::TP_TEXT);
    EShopOptionAppend('art', 'Артикул', Catalog::TP_STRING, 32);
    EShopOptionAppend('sklad', 'Количество на складе', Catalog::TP_NUMBER, 5);
    EShopOptionAppend('price', 'Цена розничная', Catalog::TP_DOUBLE, '10,2');
    EShopOptionAppend('brand', 'Бренд', Catalog::TP_TABLE);
    EShopOptionAppend('country', 'Страна-производитель', Catalog::TP_TABLE);
    EShopOptionAppend('new', 'Новинка', Catalog::TP_BOOLEAN);
    EShopOptionAppend('hit', 'Хит продаж', Catalog::TP_BOOLEAN);
    EShopOptionAppend('akc', 'Акция', Catalog::TP_NUMBER, 10);

}

if ($updateManager->isUpdate('0.1.0.6')) {
    $db->query_write("DROP TABLE IF EXISTS ".$pfx."eshp_ordercfg");
}

if ($updateManager->isUpdate('0.1.0.8') && !$updateManager->isInstall()) {
    $db->query_write("DROP TABLE IF EXISTS ".$pfx."eshp_payment");
}

if ($updateManager->isUpdate('0.1.0.9') && !$updateManager->isInstall()) {

    // декодирование поля param под новую версию каталога
    $rows = $db->query_read("
		SELECT * 
		FROM ".$pfx."ctg_eshp_eloption
	");

    while (($row = $db->fetch_array($rows))) {
        $db->query_write("
			UPDATE ".$pfx."ctg_eshp_eloption
			SET param='".bkstr(urldecode($row['param']))."'
			WHERE eloptionid=".$row['eloptionid']."
		");
    }
}
if ($updateManager->isUpdate('0.1.0.11') && !$updateManager->isInstall()) {
    $db->query_write("DROP TABLE IF EXISTS ".$pfx."eshp_discount");
}

if ($updateManager->isUpdate('0.2')) {
    Abricos::GetModule('eshop')->permission->Install();
}

if ($updateManager->isUpdate('0.2.1') && !$updateManager->isInstall()) {

    $db->query_write("
		UPDATE `".$pfx."ctg_eshp_element`
		SET 
			title = fld_name,
			ord = fld_ord, 
			metatitle = fld_metatitle,
			metakeys = fld_metakeys,
			metadesc = fld_metadesc 
	");

    $db->query_write("
		DELETE FROM `".$pfx."ctg_eshp_eloption`
		WHERE eltypeid=0 AND (
			name='name' OR 
			name='ord' OR name='metatitle' OR 
			name='metakeys' OR name='metadesc'
		)
	");

    $db->query_write("
		ALTER TABLE `".$pfx."ctg_eshp_element`
		DROP fld_name,
		DROP fld_ord,
		DROP fld_metatitle,
		DROP fld_metakeys,
		DROP fld_metadesc
	");

}

if ($updateManager->isUpdate('0.2.2')) {
    $db->query_write("
		INSERT INTO `".$pfx."ctg_eshp_eloptgroup` (name, title, issystem, language) VALUES 
		('specific', 'Технические характеристики', 1, 'ru')
	");
}


if ($updateManager->isUpdate('0.2.3') && !$updateManager->isInstall()) {
    Abricos::GetModule('eshop')->permission->Reinstall();
}

if ($updateManager->isUpdate('0.2.3.1') && !$updateManager->isInstall()) {
    $db->query_write("
		UPDATE `".$pfx."ctg_eshp_eloptgroup` 
		SET language='ru'
		WHERE language=''
	");
}
?>