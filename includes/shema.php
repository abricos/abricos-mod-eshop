<?php
/**
 * Схема таблиц данного модуля.
 * 
 * @version $Id$
 * @package Abricos
 * @subpackage Sys
 * @copyright Copyright (C) 2008 Abricos. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin (roosit@abricos.org)
 */

$charset = "CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'";
$updateManager = Ab_UpdateManager::$current; 
$db = Abricos::$db;
$pfx = $db->prefix;

$catalogManager = Abricos::GetModule('eshop')->GetCatalogManager();
$catalogManager->DisableRole();

if ($updateManager->isInstall()){
	
	$catalogManager->ElementOptionAppend(0, 0, 3, 'name', 'Название', 'Название товара', 1, '{"cst":{"en":0,"inpen":0,"inp":"","onlden":0,"onld":""},"size":"255","def":""}');
	$catalogManager->ElementOptionAppend(0, 0, 7, 'desc', 'Описание товара', '', 0, '{"cst":{"en":0,"inpen":0,"inp":"","onlden":0,"onld":""}}');  
	$catalogManager->ElementOptionAppend(0, 0, 3, 'art', 'Артикул', '', 1, '{"cst":{"en":0,"inpen":0,"inp":"","onlden":0,"onld":""},"size":"32","def":""}');
	$catalogManager->ElementOptionAppend(0, 0, 1, 'sklad', 'Количество на складе', '', 0, '{"cst":{"en":0,"inpen":0,"inp":"","onlden":0,"onld":""},"size":"5","def":"0"}');
	$catalogManager->ElementOptionAppend(0, 0, 2, 'price', 'Цена розничная', '', 0, '{"cst":{"en":0,"inpen":0,"inp":"","onlden":0,"onld":""},"size":"10,2","def":"0"}');
	$catalogManager->ElementOptionAppend(0, 0, 5, 'brand', 'Бренд', '', 0, '{"cst":{"en":0,"inpen":0,"inp":"","onlden":0,"onld":""}}');
	$catalogManager->ElementOptionAppend(0, 0, 5, 'country', 'Страна-производитель', '', 0, '{"cst":{"en":0,"inpen":0,"inp":"","onlden":0,"onld":""}}');
	$catalogManager->ElementOptionAppend(0, 0, 3, 'metatitle', 'Тег title', '', 0, '{"cst":{"en":0,"inpen":0,"inp":"","onlden":0,"onld":""},"size":"255","def":""}');
	$catalogManager->ElementOptionAppend(0, 0, 3, 'metakeys', 'Тег keywords', '', 0, '{"cst":{"en":0,"inpen":0,"inp":"","onlden":0,"onld":""},"size":"255","def":""}');
	$catalogManager->ElementOptionAppend(0, 0, 3, 'metadesc', 'Тег description', '', 0, '{"cst":{"en":0,"inpen":0,"inp":"","onlden":0,"onld":""},"size":"255","def":""}');
	$catalogManager->ElementOptionAppend(0, 0, 0, 'new', 'Новинка', '', 0, '{"cst":{"en":0,"inpen":0,"inp":"","onlden":0,"onld":""},"def":"0"}');
	$catalogManager->ElementOptionAppend(0, 0, 0, 'hit', 'Хит продаж', '', 0, '{"cst":{"en":0,"inpen":0,"inp":"","onlden":0,"onld":""},"def":"0"}');
	$catalogManager->ElementOptionAppend(0, 0, 1, 'akc', 'Акция', '', 0, '{"cst":{"en":0,"inpen":0,"inp":"","onlden":0,"onld":""},"size":"10","def":"0"}');
}

if ($updateManager->isUpdate('0.1.0.2')){
	
	$db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."eshp_cart (
		  `cartid` int(10) unsigned NOT NULL auto_increment COMMENT 'Идентификатор записи',
		  `userid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Локальный идентификатор пользователя',
		  `session` varchar(32) NOT NULL DEFAULT '' COMMENT 'Сессия пользователя если userid=0',
		  `dateline` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата добавления в корзину',
		  `productid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Идентификатор продукта',
		  `quantity` int(5) unsigned NOT NULL DEFAULT 0 COMMENT 'Кол-во единиц продукта',
		  `price` double(10,2) unsigned NOT NULL DEFAULT 0 COMMENT 'Цена за единицу',
		  PRIMARY KEY  (`cartid`),
		  KEY `userid` (`userid`),
		  KEY `session` (`session`)
		)".$charset
	);
}

if ($updateManager->isUpdate('0.1.0.3')){

	$db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."eshp_order (
		  `orderid` int(10) unsigned NOT NULL auto_increment COMMENT 'Идентификатор записи',
		  `userid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Идентификатор пользователя, товар заказал авторизованный пользователь',
		  `firstname` varchar(50) NOT NULL DEFAULT '' COMMENT 'Имя',
		  `lastname` varchar(50) NOT NULL DEFAULT '' COMMENT 'Фамилия',
		  `secondname` varchar(50) NOT NULL DEFAULT '' COMMENT 'Отчество',
		  `phone` varchar(250) NOT NULL DEFAULT '' COMMENT 'Телефоны, если несколько - разделены запятой',
		  `adress` TEXT NOT NULL COMMENT 'Адрес доставки',
		  `extinfo` TEXT NOT NULL COMMENT 'Дополнительная информация',
		  `status` int(1) unsigned NOT NULL Default 0 COMMENT 'Статус заказа: 0-заказ создан, 1-принят к исполнению, 2-выполнен',
		  `secretkey` varchar(32) NOT NULL DEFAULT '' COMMENT 'Идентификатор заказа, для определения статуса заказа',
		  `ip` varchar(15) NOT NULL default '' COMMENT 'IP адрес заказчика',
		  `dateline` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата создания',
		  `deldate` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата удаления',
		  PRIMARY KEY  (`orderid`)
		)".$charset
	);

	$db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."eshp_orderitem (
		  `orderitemid` int(10) unsigned NOT NULL auto_increment COMMENT 'Идентификатор записи',
		  `orderid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Идентификатор заказа',
		  `productid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Идентификатор продукта',
		  `quantity` int(5) unsigned NOT NULL DEFAULT 0 COMMENT 'Кол-во единиц продукта',
		  `price` double(10,2) unsigned NOT NULL DEFAULT 0 COMMENT 'Цена за единицу',
		  PRIMARY KEY  (`orderitemid`),
		  KEY `userid` (`orderid`)
		)".$charset
	);
}

if ($updateManager->isUpdate('0.1.0.5')){
	
	$db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."eshp_delivery (
		  `deliveryid` int(10) unsigned NOT NULL auto_increment COMMENT 'Идентификатор записи',
		  `parentdeliveryid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Идентификатор родителя',
		  `title` varchar(250) NOT NULL DEFAULT '' COMMENT '',
		  `ord` int(3) unsigned NOT NULL DEFAULT 0 COMMENT 'Сортировка',
		  `disabled` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT 'Если 1, то отключен',
		  `price` double(10,2) unsigned NOT NULL DEFAULT 0 COMMENT 'Цена доставки',
		  `fromzero` double(10,2) unsigned NOT NULL DEFAULT 0 COMMENT 'Если заказ выше или равен этой сумме, то доставка бесплатна',
		  PRIMARY KEY  (`deliveryid`)
		)".$charset
	);
}

if ($updateManager->isUpdate('0.1.0.6')){
	$db->query_write("DROP TABLE IF EXISTS ".$pfx."eshp_ordercfg");
}

if ($updateManager->isUpdate('0.1.0.8')){
	$db->query_write("DROP TABLE IF EXISTS ".$pfx."eshp_payment");
	
	$db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."eshp_payment (
		  `paymentid` int(10) unsigned NOT NULL auto_increment COMMENT 'Идентификатор записи',
		  `title` varchar(250) NOT NULL DEFAULT '' COMMENT '',
		  `ord` int(3) unsigned NOT NULL DEFAULT 0 COMMENT 'Сортировка',
		  `disabled` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT 'Если 1, то отключен',
		  `def` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT 'По умолчанию',
		  `descript` TEXT NOT NULL COMMENT '',
		  `js` TEXT NOT NULL COMMENT '',
		  `php` TEXT NOT NULL COMMENT '',
		  PRIMARY KEY  (`paymentid`)
		)".$charset
	);
}

if ($updateManager->isUpdate('0.1.0.9') && !$updateManager->isInstall()){
	
	// декодирование поля param под новую версию каталога
	$rows = $db->query_read("
		SELECT * 
		FROM ".$pfx."ctg_eshp_eloption
	");
	
	while (($row = $db->fetch_array($rows))){
		$db->query_write("
			UPDATE ".$pfx."ctg_eshp_eloption
			SET param='".bkstr(urldecode($row['param']))."'
			WHERE eloptionid=".$row['eloptionid']."
		");
	}
}
if ($updateManager->isUpdate('0.1.0.11')){

	$db->query_write("DROP TABLE IF EXISTS ".$pfx."eshp_discount");
	$db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."eshp_discount (
		  `discountid` int(10) unsigned NOT NULL auto_increment COMMENT 'Идентификатор записи',
		  `dtype` int(1) unsigned NOT NULL DEFAULT 0 COMMENT 'Тип скидки: 0-разовая, 1-накопительная',
		  `disabled` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT 'Отключить',
  		  `title` varchar(250) NOT NULL DEFAULT '' COMMENT '',
		  `descript` TEXT NOT NULL COMMENT '',
  		  
		  `price` double(10,2) unsigned NOT NULL DEFAULT 0 COMMENT 'значение скидки',
		  `ispercent` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '1-значение в процентах, 0-абсолютная сумма',
		  
		  `fromsum` double(10,2) unsigned NOT NULL DEFAULT 0 COMMENT '',
		  `endsum` double(10,2) unsigned NOT NULL DEFAULT 0 COMMENT '',
		  `dateline` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата создания',
		  PRIMARY KEY  (`discountid`)
		)".$charset
	);
	
}

if ($updateManager->isUpdate('0.1.0.12')){

	$db->query_write("
		ALTER TABLE `".$pfx."eshp_order` ADD `deliveryid` int(10) unsigned NOT NULL DEFAULT 0 AFTER `userid`
	");
	$db->query_write("
		ALTER TABLE `".$pfx."eshp_order` ADD `paymentid` int(10) unsigned NOT NULL DEFAULT 0 AFTER `deliveryid`
	");
	
}
if ($updateManager->isUpdate('0.1.0.13')){

	$catalogManager->ElementOptionAppend(0, 0, 1, 'ord', 'Сортировка', '', 0, '{"cst":{"en":0,"inpen":0,"inp":"","onlden":0,"onld":""},"size":"10","def":"0"}');

}

if ($updateManager->isUpdate('0.2')){
	Abricos::GetModule('eshop')->permission->Install();
}

?>