<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

class EShopQuery {
	
	public static function OrderConfigList(Ab_Database $db){
		$sql = "
			SELECT 
				ordercfgid as id,
				ord,
				cfgtype as tp,
				title as tl,
				input as it,
				output as ot
			FROM ".$db->prefix."eshp_ordercfg
			ORDER BY ord
		";
		return $db->query_read($sql);
	}
	
	public static function OrderConfigAppend(Ab_Database $db, $d){
		$sql = "
			INSERT INTO ".$db->prefix."eshp_ordercfg
			(ord, cfgtype, title, input, output) VALUES
			(
				".bkint($d->ord).",
				".bkint($d->tp).",
				'".bkstr($d->tl)."',
				'".bkstr($d->it)."',
				'".bkstr($d->ot)."'
			)
		";
		$db->query_write($sql);
	}
	
	public static function OrderConfigUpdate(Ab_Database $db, $d){
		$sql = "
			UPDATE ".$db->prefix."eshp_ordercfg
			SET
				ord=".bkint($d->ord).",
				cfgtype=".bkint($d->tp).",
				title='".bkstr($d->tl)."',
				input='".bkstr($d->it)."',
				output='".bkstr($d->ot)."'
			WHERE ordercfgid=".bkint($d->id)."
			LIMIT 1
		";
		$db->query_write($sql);
	}
	
	public static function OrderConfigRemove(Ab_Database $db, $id){
		$sql = "
			DELETE FROM ".$db->prefix."eshp_ordercfg
			WHERE ordercfgid=".bkint($id)."
		";
		$db->query_write($sql);
	}
	
	public static function Order(Ab_Database $db, $orderid, $userid = -1){
		$sql = "
			SELECT 
				o.orderid as id,
				o.userid as uid,
				o.deliveryid as delid,
				o.paymentid as payid,
				o.ip as ip,
				o.firstname as fnm,
				o.lastname as lnm,
				o.phone as ph,
				o.adress as adress,
				o.extinfo as extinfo,
				o.status as st,
				sum(i.quantity) as qty,
				sum(i.quantity*i.price) as sm,
				o.dateline as dl
			FROM ".$db->prefix."eshp_order o
			LEFT JOIN ".$db->prefix."eshp_orderitem i ON o.orderid=i.orderid
			WHERE o.orderid=".bkint($orderid)." ".($userid>0?" AND o.userid=".intval($userid):"")."
			GROUP BY i.orderid
		";
		return $db->query_read($sql);
	}
		
	/**
	 * Получить список товаров конкретного заказа
	 */
	public static function OrderItemList(Ab_Database $db, $orderid, $userid = -1){
		$sql = "
			SELECT
				a.productid as id,
				SUM(a.quantity) as qty,
				a.price as pc,
				p.catalogid as catid,
				p.eltypeid as eltid,
				p.title as tl,
				p.name as nm
			FROM ".$db->prefix."eshp_orderitem a 
			INNER JOIN ".CatalogQuery::$PFX."element p ON a.productid = p.elementid
			INNER JOIN ".$db->prefix."eshp_order o ON o.orderid = a.orderid
			WHERE a.orderid=".bkint($orderid)." ".($userid>0?" AND o.userid=".intval($userid):"")."
			GROUP BY a.productid
		";
		return $db->query_read($sql);
	}
	
	public static function Orders(Ab_Database $db, $status, $page, $limit){
		$from = (($page-1)*$limit);
		
		// если $status=-1, то выбрать удаленные
		$where = $status < 0 ? "o.deldate>0" : "deldate < 1 AND o.status=".bkint($status); 
		$sql = "
			SELECT 
				o.orderid as id,
				o.firstname as fnm,
				o.lastname as lnm,
				o.userid as uid,
				o.adress as adr,
				o.ip as ip,
				sum(i.quantity*i.price) as sm,
				o.dateline as dl
			FROM ".$db->prefix."eshp_order o
			LEFT JOIN ".$db->prefix."eshp_orderitem i ON o.orderid=i.orderid
			WHERE ".$where." 
			GROUP BY i.orderid
			ORDER BY o.dateline DESC
			LIMIT ".intval($from).", ".intval($limit)."
		";
		return $db->query_read($sql);
	}
	
	public static function OrdersCount(Ab_Database $db, $status){
		// если $status=-1, то выбрать удаленные
		$where = $status < 0 ? "o.deldate>0" : "deldate < 1 AND o.status=".bkint($status); 
		$sql = "
			SELECT count(*) as cnt
			FROM ".$db->prefix."eshp_order o
			WHERE ".$where." 
			LIMIT 1
		";
		return $db->query_read($sql);
	}
	
	public static function OrderAppend(Ab_Database $db, $d){
		$sql = "
			INSERT INTO ".$db->prefix."eshp_order 
			(userid, deliveryid, paymentid, firstname, lastname, secondname, phone, adress, extinfo, ip, dateline) VALUES (
				".bkint($d->userid).",
				".bkint($d->deliveryid).",
				".bkint($d->paymentid).",
				'".bkstr($d->firstname)."',
				'".bkstr($d->lastname)."',
				'".bkstr($d->secondname)."',
				'".bkstr($d->phone)."',
				'".bkstr($d->adress)."',
				'".bkstr($d->extinfo)."',
				'".bkstr($d->ip)."',
				".TIMENOW."
			)
		";
		$db->query_write($sql);
		return $db->insert_id();
	}
	
	/**
	 * Принять заказ на исполнение
	 * @param Ab_Database $db
	 * @param integer $orderid
	 */
	public static function OrderAccept(Ab_Database $db, $orderid){
		$sql = "
			UPDATE ".$db->prefix."eshp_order 
			SET status=".EShopManager::ORDER_STATUS_EXEC."
			WHERE orderid=".bkstr($orderid)."
		";
		$db->query_write($sql);
	}
	
	/**
	 * Выполнить заказ (закрытие)
	 * @param Ab_Database $db
	 * @param integer $orderid
	 */
	public static function OrderClose(Ab_Database $db, $orderid){
		$sql = "
			UPDATE ".$db->prefix."eshp_order 
			SET status=".EShopManager::ORDER_STATUS_ARHIVE."
			WHERE orderid=".bkstr($orderid)."
		";
		$db->query_write($sql);
	}
	
	/**
	 * Удалить заказ в корзину
	 * 
	 * @param Ab_Database $db
	 * @param integer $orderid
	 */
	public static function OrderRemove(Ab_Database $db, $orderid){
		$sql = "
			UPDATE ".$db->prefix."eshp_order 
			SET deldate=".TIMENOW."
			WHERE orderid=".bkstr($orderid)."
		";
		$db->query_write($sql);
	}
	
	public static function OrderItemAppend(Ab_Database $db, $orderid, $productid, $quantity, $price){
		$sql = "
			INSERT INTO ".$db->prefix."eshp_orderitem 
			(orderid, productid, quantity, price) VALUES (
				".bkint($orderid).",
				".bkint($productid).",
				".bkint($quantity).",
				".doubleval($price)."
			)
		";
		$db->query_write($sql);
		return $db->insert_id();
	}
	
	/**
	 * Добавить продукт в корзину
	 * 
	 * @param Ab_Database $db
	 * @param integer $userid идентификатор пользователя, если авторизован
	 * @param string $session сессия пользователя, если гость
	 * @param integer $productid идентификатор товара
	 * @param integer $quantity кол-во
	 * @param double $price цена
	 */
	public static function CartAppend(Ab_Database $db, $userid, $session, $productid, $quantity, $price){
		
		$session = $userid > 0 ? '' : $session;
		
		$sql = "
			INSERT INTO ".$db->prefix."eshp_cart 
			(userid, session, productid, quantity, price, dateline) VALUES (
				".bkint($userid).",
				'".bkstr($session)."',
				".bkint($productid).",
				".intval($quantity).",
				".doubleval($price).",
				".TIMENOW."
			)
		";
		$db->query_write($sql);
		return $db->insert_id();
	}
	
	/**
	 * Если пользователь зарегистрирован, необходимо перенести товар в
	 * корзине набранный будучи гостем
	 * 
	 * @param Ab_Database $db
	 * @param integer $userid идентификатор пользователя, если авторизован
	 * @param string $session сессия пользователя, если гость
	 */
	public static function CartUserSessionFixed(Ab_Database $db, $userid, $session){
		if ($userid < 1){ return; }
		$sql = "
			UPDATE ".$db->prefix."eshp_cart 
			SET userid=".bkint($userid).", session=''
			WHERE userid=0 AND session='".bkstr($session)."'
		";
		$db->query_write($sql);
	}
	
	/**
	 * Получить информацию по корзине
	 * 
	 * @param Ab_Database $db
	 * @param integer $userid идентификатор пользователя
	 * @param string $session сессия пользователя
	 */
	public static function CartInfo(Ab_Database $db, $userid, $session){
		EShopQuery::CartUserSessionFixed($db, $userid, $session);
		$sql = "
			SELECT 
				SUM(quantity) as qty,
				SUM(quantity*price) as sm
			FROM ".$db->prefix."eshp_cart
			WHERE ".($userid > 0 ? "userid=".bkint($userid) : "session='".bkstr($session)."'")."
		";
		
		return $db->query_first($sql);
	}
	
	/**
	 * Получить полное содержание корзины
	 * 
	 * @param Ab_Database $db
	 * @param integer $userid идентификатор пользователя
	 * @param string $session сессия пользователя
	 * @param integer $productid если указан, то возврат только этого продукта
	 */
	public static function Cart(Ab_Database $db, $userid, $session, $productid = 0){
		$productid = bkint($productid);
		$sql = "
			SELECT
				a.productid as id,
				SUM(a.quantity) as qty,
				a.price as pc,
				p.catalogid as catid,
				p.eltypeid as eltid,
				p.title as tl,
				p.name as nm
			FROM ".$db->prefix."eshp_cart a 
			INNER JOIN ".CatalogQuery::$PFX."element p ON a.productid = p.elementid
			WHERE ".($userid > 0 ? "a.userid=".bkint($userid) : "a.session='".bkstr($session)."'")."
			".($productid > 0 ? " AND a.productid=".$productid : "")."
			GROUP BY a.productid
		";
		return $db->query_read($sql);
	}
	
	public static function CartClear(Ab_Database $db, $userid, $sessionid){
		$userid = intval($userid);
		$sql = "
			DELETE FROM ".$db->prefix."eshp_cart 
			WHERE ".($userid > 0 ? "userid=".bkint($userid) : "session='".bkstr($sessionid)."'")."
		";
		$db->query_write($sql); 
	}
	
	public static function CartRemove(Ab_Database $db, $productid = 0){
		$sql = "
			DELETE FROM ".$db->prefix."eshp_cart
			WHERE productid=".bkint($productid)."
		";
		return $db->query_write($sql);
	}
}

?>