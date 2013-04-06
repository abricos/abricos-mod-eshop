<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

Abricos::GetModule('catalog')->GetManager();

require_once 'classes.php';

class EShopManager extends Ab_ModuleManager {

	/**
	 * @var EShopManager
	 */
	public static $instance;
	
	/**
	 * @var EShopConfig
	 */
	public $config;
	
	/**
	 * @var EShopCatalogManager
	 */
	public $cManager;
	
	public function __construct(EShopModule $module){
		parent::__construct($module);
		
		EShopManager::$instance = $this;
		$this->config = new EShopConfig(Abricos::$config['module']['eshop']);
		
		$this->cManager = new EShopCatalogManager();
		
		// TODO: на удаление
		$this->catalog = Abricos::GetModule('catalog');
		$this->catalogManager = $module->GetCatalogManager();
		
		$this->userSession = $this->user->session->key;
	}
	
	public function IsAdminRole(){
		return $this->IsRoleEnable(EShopAction::ADMIN);
	}
	
	public function IsWriteRole(){
		if ($this->IsAdminRole()){ return true; }
		return $this->IsRoleEnable(EShopAction::WRITE);
	}
	
	public function IsViewRole(){
		if ($this->IsWriteRole()){ return true; }
		return $this->IsRoleEnable(EShopAction::VIEW);
	}
	
	public function AJAX($d){
		
		$ret = $this->cManager->AJAX($d);
		if (!empty($ret)){ return $ret; }
		
		// TODO: на удаление/переделку
		switch($d->do){
			case "prodtocart":
				return $this->CartAppend($d->productid, $d->quantity);
			case "cartinfo":
				return $this->CartInfo();
			case "auth":
				return $this->Auth($d->login, $d->password);
			case "checkreg":
				return $this->CheckUserRegInfo($d->login, $d->email);
			
			case "orderbuild":
				return $this->OrderBuild($d);
			case "order-remove":
				return $this->OrderRemove($d->orderid);
			case "order-accept":
				return $this->OrderAccept($d->orderid);
			case "order-close":
				return $this->OrderClose($d->orderid);
				
			case "brick-productlist":
				Abricos::$adress = new Ab_URI($d->uri); 
				$smMenu = Abricos::GetModule('sitemap')->GetManager()->GetMenu();
				$this->module->BuildMenu($smMenu, true);
				
				$brick = Brick::$builder->LoadBrickS('eshop', 'product_list', Brick::$builder->brick, array("p" => array(
					"page" => $d->page,
					"display" => 'none'
				)));
				print($brick->content);
				exit;
		}
		return null;
	}
	
	private function BuildOfflineCatalog(OfflineDir $dir, $catid){
		$offMan = OfflineManager::$instance;
		
		$brick = Brick::$builder->LoadBrickS("eshop", "offline_catalog_list", null, array(
			"p" => array(
				"dir" => $dir,
				"catid" => $catid
			)
		));
		
		$offMan->WritePage($dir, "index", $brick->content);
		
		$catMain = $this->cManager->CatalogList()->Find($catid);
		$catList = $catMain->childs;
		
		for($i=0; $i<$catList->Count();$i++){
			$cat = $catList->GetByIndex($i);
			
			$cdir = new OfflineDir($dir, $cat->name);
			$this->BuildOfflineCatalog($cdir, $cat->id);
		}
	}
	
	/**
	 * Выгрузка оффлайн каталога товаров
	 */
	public function Offline_Build(OfflineDir $dir){
		$this->BuildOfflineCatalog($dir, 0);
	}
	
	/**
	 * Использует модуль Sitemap для построения меню товаров
	 * 
	 * @param SMMenuItem $menuItem
	 */
	public function Sitemap_MenuBuild(SMMenuItem $mItem){
		$catList = $this->cManager->CatalogList();
		
		require_once 'smclasses.php';
		
		$rootCat = $catList->GetByIndex(0);
		$count = $rootCat->childs->Count();
		for ($i=0; $i<$count; $i++){
			$cmItem = new EShopMenuItem($mItem, $rootCat->childs->GetByIndex($i));
			$mItem->childs->Add($cmItem);
		}
	}
	
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * TODO: Старая версия методов - на удаление
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	
	/**
	 * @var EShopModule
	 */
	public $module;
	
	/**
	 * Статус заказа - Новый
	 * @var integer
	 */
	const ORDER_STATUS_NEW = 0;
	
	/**
	 * Статус заказа - Принятый на исполнение
	 * @var integer
	 */
	const ORDER_STATUS_EXEC = 1;
	
	/**
	 * Статус заказа - Закрытый
	 * @var integer
	 */
	const ORDER_STATUS_ARHIVE = 2;
	
	
	/**
	 * Модуль каталога
	 * @var CatalogModule
	 */
	public $catalog = null;
	
	/**
	 * Менеджер каталога
	 * @var CatalogManager
	 */
	public $catalogManager = null;
	
	/**
	 * @return CatalogManager
	 */
	public function GetCatalogManager(){
		return $this->catalogManager;
	}
	
	public function DSProcess($name, $rows){
		$p = $rows->p;
		$db = $this->db;
		
		switch ($name){
			case 'cart':
				foreach ($rows->r as $r){
					if ($r->f == 'd'){ $this->CartRemove($r->d->id); }
					if ($r->f == 'u'){ $this->CartUpdate($r->d); }
				}
				return;
			case 'ordercfg':
				foreach ($rows->r as $r){
					if ($r->f == 'u'){ $this->OrderConfigUpdate($r->d); }
					if ($r->f == 'a'){ $this->OrderConfigAppend($r->d); }
					if ($r->f == 'd'){ $this->OrderConfigRemove($r->d->id); }
				}
				return;
			case 'delivery':
				foreach ($rows->r as $r){
					if ($r->f == 'u'){ $this->DeliveryUpdate($r->d); }
					if ($r->f == 'a'){ $this->DeliveryAppend($r->d); }
					if ($r->f == 'd'){ $this->DeliveryRemove($r->d->id); }
				}
				return;
			case 'payment':
				foreach ($rows->r as $r){
					if ($r->f == 'u'){ $this->PaymentUpdate($r->d); }
					if ($r->f == 'a'){ $this->PaymentAppend($r->d); }
					if ($r->f == 'd'){ $this->PaymentRemove($r->d->id); }
				}
				return;
			case 'discount':
				foreach ($rows->r as $r){
					if ($r->f == 'u'){ $this->DiscountUpdate($r->d); }
					if ($r->f == 'a'){ $this->DiscountAppend($r->d); }
					if ($r->f == 'd'){ $this->DiscountRemove($r->d->id); }
				}
				return;
			case 'config':
				foreach ($rows->r as $r){
					if ($r->f == 'u'){ $this->ConfigSave($r->d); }
				}
				return;
		}
	}
	
	public function DSGetData($name, $rows){
		$p = $rows->p;

		switch ($name){
			case 'cart': return $this->Cart($p->orderid);
			
			case 'order': return $this->Order($p->orderid);
			case 'orderitem': return $this->OrderItemList($p->orderid);
			
			case 'orders-new':  return $this->Orders('new', $p->page, $p->limit); 
			case 'orders-exec':  return $this->Orders('exec', $p->page, $p->limit); 
			case 'orders-arhive':  return $this->Orders('arhive', $p->page, $p->limit); 
			case 'orders-recycle':  return $this->Orders('recycle', $p->page, $p->limit); 
			case 'orderscnt-new':  return $this->OrdersCount('new'); 
			case 'orderscnt-exec':  return $this->OrdersCount('exec'); 
			case 'orderscnt-arhive':  return $this->OrdersCount('arhive'); 
			case 'orderscnt-recycle':  return $this->OrdersCount('recycle'); 
			
			case 'ordercfg':  return $this->OrderConfigList(); 
			
			case 'delivery':  return $this->DeliveryList();
			case 'payment':  return $this->PaymentList();
			case 'discount':  return $this->DiscountList();
			case 'config':  return array($this->Config()); 
		}

		return null;
	}
	
	
	/**
	 * Получить данные для работы кирпичей по сборке списка продуктов
	 */
	
	private $_productListData = null;
	public function GetProductListData(){
		if (!is_null($this->_productListData)){
			return $this->_productListData;
		}
		
		$smMenu = Abricos::GetModule('sitemap')->GetManager()->GetMenu();
		$catItemMenu = $smMenu->menuLine[count($smMenu->menuLine)-1];
		
		// если на конце uri есть запись /pageN/, где N - число, значит запрос страницы
		$listPage = 1;
		
		$adress = Abricos::$adress;
		
		$tag = $adress->dir[$adress->level-1];
		if (substr($tag, 0, 4) == 'page'){
			$listPage = intval(substr($tag, 4, strlen($tag)-4));
		}
		
		$this->_productListData = array(
			"listPage" => $listPage,
			"catids" => $this->module->GetFullSubCatalogId($catItemMenu)
		);
		return $this->_productListData;
	}
	
	/**
	 * Проверить данные для регистрации нового пользователя
	 *
	 * 0 - ошибки нет,
	 * 1 - пользователь с таким логином уже зарегистрирован, 
	 * 2 - пользователь с таким email уже зарегистрирован
	 * 3 - ошибка в имени пользователя,
	 * 4 - ошибка в email
	 * 
	 * @param string $email
	 * @param string $login
	 */
	public function CheckUserRegInfo($login, $email){
		$ret = new stdClass();
		$ret->error = Abricos::$user->GetManager()->RegisterCheck($login, $email);
		if ($ret->error > 0){
			sleep(1);
		}
		return $ret;
	}
	
	/**
	 * Авторизация пользователя в процессе оформления товара.
	 * Если пользователь успешно авторизован, то возвращает
	 * все необходимые данные для оформления товара.
	 *
	 * @param string $login
	 * @param string $password
	 */
	public function Auth($login, $password){
		$ret = new stdClass();
		$ret->error = Abricos::$user->GetManager()->Login($login, $password);
		if ($error > 0){
			sleep(1);
			return $ret;
		}
		$ret->userid = Abricos::$user->session->Get('userid'); 
		$ret->orderinfo = $this->OrderLastInfo(); 
		return $ret;
	}
	
	/**
	 * Сформировать заказ клиента
	 *
	 */
	public function OrderBuild($data){
		$userid = $this->userid;
		$db = $this->db;
		if ($this->user->id == 0 && $data->auth->type == 'reg'){
			// пользователь решил заодно и зарегистрироваться
			$login = $data->auth->login;
			$email = $data->auth->email;
			$pass = $data->auth->pass;
			$err = $this->user->GetManager()->Register($login, $pass, $email, true);
			if ($err == 0){
				$user = UserQuery::UserByName($db, $login);
				$userid = $user['userid'];
			}
		}
		
		$od = new stdClass();
		$od->deliveryid = $data->deli->deliveryid;
		$od->paymentid = $data->pay->paymentid;
		
		$deli = $data->deli;
		$od->userid = $userid;
		$od->firstname = $deli->firstname;
		$od->lastname = $deli->lastname;
		$od->phone = $deli->phone;
		$od->adress = $deli->adress;
		$od->extinfo = $deli->extinfo;
		$od->ip = $_SERVER['REMOTE_ADDR'];
		
		$orderid = EShopQuery::OrderAppend($db, $od);
		
		EShopQuery::CartUserSessionFixed($db, $userid, $this->userSession);
		
		$rows = $this->CartByUserId($userid);
		while (($row = $db->fetch_array($rows))){
			EShopQuery::OrderItemAppend($db, $orderid, $row['id'], $row['qty'], $row['pc']);
		}
		EShopQuery::CartClear($db, $userid, $this->userSession);
		
		$order = $this->db->fetch_array(EShopQuery::Order($this->db, $orderid));
		
		// отправить уведомление на емайл админам
		$config = $this->Config(false);
		$emails = $config['adm_emails'];
		$arr = explode(',', $emails);
		$subject = $config['adm_notify_subj'];
		$body = Brick::ReplaceVarByData($config['adm_notify'], array(
			'orderid'=>$orderid,
			'summ' => $order['sm'],
			'qty' => $order['qty'],
		
			'fnm' => $order['fnm'],
			'lnm' => $order['lnm'],
			'phone' => $order['ph'],
			'adress' => $order['adress'],
			'extinfo' => $order['extinfo']
		));
		$body = nl2br($body);
		
		foreach ($arr as $email){
			$email = trim($email);
			if (empty($email)){ continue; }
			
			Abricos::Notify()->SendMail($email, $subject, $body);
		}
	}
	
	/**
	 * Принять заказ на исполнение 
	 */
	public function OrderAccept($orderid){
		if (!$this->user->IsAdminMode()){ return null; }
		
		$order = $this->Order($orderid);
		if (empty($order)){ return; }
		EShopQuery::OrderAccept($this->db, $orderid);
	}

	/**
	 * Исполнить заказ (закрыть) 
	 */
	public function OrderClose($orderid){
		if (!$this->user->IsAdminMode()){ return null; }
		
		$order = $this->Order($orderid);
		if (empty($order)){ return; }
		EShopQuery::OrderClose($this->db, $orderid);
	}
	
	/**
	 * Удалить заказ в корзину
	 * 
	 * @param integer $orderid идентификатор заказа
	 */
	public function OrderRemove($orderid){
		if (!$this->user->IsAdminMode()){ return null; }
		
		$order = $this->Order($orderid);
		if (empty($order)){ return; }
		EShopQuery::OrderRemove($this->db, $orderid);
		return $orderid;
	}
	
	/**
	 * Получить информацию для полей заказа товара 
	 *
	 */
	public function OrderLastInfo(){
		return array(
			"fam"=>"Ivanov",
			"im"=>"Ivan",
			"otch"=>"Ivanovich"
		);
	}
	
	public function CartUpdate($product){
		$pcart = $this->CartItem($product->id);
		if (empty($pcart)){ // Hacker???
			return;
		}
		$newQty = bkint($product->qty);
		EShopQuery::CartRemove($this->db, $product->id);
		if ($newQty < 1){
			return;
		}
		return $this->CartAppend($product->id, $newQty);
	}

	/**
	 * Положить товар в корзину текущего пользователя
	 * @return вернуть информацию по корзине
	 */
	public function CartAppend($productid, $quantity){
		$quantity = bkint($quantity);
		if ($quantity < 1){ return; }
		$db = $this->db;
		
		$product = $this->module->GetCatalogManager()->Element($productid, true);
		if (empty($product)){
			// попытка добавить несуществующий продукт???
			return null;
		}
		
		$cartid = EShopQuery::CartAppend($this->db, $this->userid, $this->userSession, $productid, $quantity, $product['fld_price']);
		
		return $this->CartInfo(); 
	}
	
	public function CartItem($productid){
		return $this->db->fetch_array(EShopQuery::Cart($this->db, $this->userid, $this->userSession, $productid));
	}
	
	public function CartRemove($productid){
		$pcart = $this->CartItem($productid);
		if (empty($pcart)){ // Hacker???
			return;
		}
		EShopQuery::CartRemove($this->db, $productid);
	}

	/**
	 * Получить информацию по корзине текущего пользователя
	 */
	public function CartInfo(){
		$info = EShopQuery::CartInfo($this->db, $this->userid, $this->userSession);
		
		return array(
			'qty' => intval($info['qty']),
			'sum' => doubleval($info['sm'])
		);
	}
	
	public function Cart($orderid){
		$orderid = intval($orderid);
		if ($orderid > 0){
			return $this->OrderItemList($orderid);
		}
		return EShopQuery::Cart($this->db, $this->userid, $this->userSession);
	}
	
	public function CartByUserId($userid){
		return EShopQuery::Cart($this->db, $userid, $this->userSession);
	}
	
	public function OrderTypeToStatus($type){
		switch($type){
			case 'new': return EShopManager::ORDER_STATUS_NEW;
			case 'exec': return EShopManager::ORDER_STATUS_EXEC;
			case 'arhive': return EShopManager::ORDER_STATUS_ARHIVE;
			case 'recycle': return -1;
		}
		return 999;
	}
	
	/**
	 * Получить список заказов 
	 *
	 */
	public function Orders($type, $page, $limit){
		if (!$this->user->IsAdminMode()){ return null; }
		$status = $this->OrderTypeToStatus($type);
		return EShopQuery::Orders($this->db, $status, $page, $limit);
	}
	
	public function OrdersCount($type){
		if (!$this->user->IsAdminMode()){ return null; }
		$status = $this->OrderTypeToStatus($type);
		return EShopQuery::OrdersCount($this->db, $status);
	}
	
	/**
	 * Получить информацию о заказе 
	 */
	public function Order($orderid){
		if ($this->user->IsAdminMode()){
			return EShopQuery::Order($this->db, $orderid); 
		}else if ($this->userid > 0){
			return EShopQuery::Order($this->db, $orderid, $this->userid);
		}
		return null;
	}
	
	/**
	 * Получить список продукции конкретного заказа 
	 */
	public function OrderItemList($orderid){
		if ($this->user->IsAdminMode()){
			return EShopQuery::OrderItemList($this->db, $orderid); 
		}else if ($this->userid > 0){
			return EShopQuery::OrderItemList($this->db, $orderid, $this->userid);
		}
		return null;
	}
	
	public function OrderConfigList(){
		return EShopQuery::OrderConfigList($this->db);
	}
	
	public function OrderConfigAppend($d){
		if (!$this->IsAdminRole()){ return null; }
		EShopQuery::OrderConfigAppend($this->db, $d);
	}
	
	public function OrderConfigUpdate($d){
		if (!$this->IsAdminRole()){ return null; }
		EShopQuery::OrderConfigUpdate($this->db, $d);
	}
	
	public function OrderConfigRemove($ordercfgid){
		if (!$this->IsAdminRole()){ return null; }
		EShopQuery::OrderConfigRemove($this->db, $ordercfgid);
	}
	
	// Оплата
	
	public function PaymentList(){
		return EShopQuery::PaymentList($this->db, $this->IsAdminRole());
	}
	
	public function PaymentAppend($d){
		if (!$this->IsAdminRole()){ return null; }
		EShopQuery::PaymentAppend($this->db, $d);
	}
	
	public function PaymentUpdate($d){
		if (!$this->IsAdminRole()){ return null; }
		EShopQuery::PaymentUpdate($this->db, $d);
	}
	
	public function PaymentRemove($id){
		if (!$this->IsAdminRole()){ return null; }
		EShopQuery::PaymentRemove($this->db, $id);
	}

	// Доставка
	
	public function DeliveryList(){
		return EShopQuery::DeliveryList($this->db);
	}
	
	public function DeliveryAppend($d){
		if (!$this->IsAdminRole()){ return null; }
		EShopQuery::DeliveryAppend($this->db, $d);
	}
	
	public function DeliveryUpdate($d){
		if (!$this->IsAdminRole()){ return null; }
		EShopQuery::DeliveryUpdate($this->db, $d);
	}
	
	public function DeliveryRemove($id){
		if (!$this->IsAdminRole()){ return null; }
		EShopQuery::DeliveryRemove($this->db, $id);
	}


	// Скидки
	
	public function DiscountList(){
		return EShopQuery::DiscountList($this->db);
	}
	
	public function DiscountAppend($d){
		if (!$this->IsAdminRole()){ return null; }
		EShopQuery::DiscountAppend($this->db, $d);
	}
	
	public function DiscountUpdate($d){
		if (!$this->IsAdminRole()){ return null; }
		EShopQuery::DiscountUpdate($this->db, $d);
	}
	
	public function DiscountRemove($id){
		if (!$this->IsAdminRole()){ return null; }
		EShopQuery::DiscountRemove($this->db, $id);
	}
	
	public function Config($checkAdmin = true){
		if ($checkAdmin && !$this->IsAdminRole()){ return array(); }
		$config = array();
		$config['id'] = 1;
		$config['adm_emails'] = Brick::$builder->phrase->Get('eshop', 'adm_emails');

		$brick = Brick::$builder->LoadBrickS('eshop', 'templates', null, null);
		$tpl = &$brick->param->var;
		$config['adm_notify_subj'] = $tpl['adm_notify_subj'];
		$config['adm_notify'] = $tpl['adm_notify'];
		
		return $config;
	}
	
	public function ConfigSave($cfg){
		if (!$this->IsAdminRole()){ return null; }
		Brick::$builder->phrase->Set('eshop', 'adm_emails', $cfg->adm_emails);
		Brick::$builder->phrase->Save();
	}
}

?>