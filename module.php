<?php 
/**
 * Модуль "Интернет магазин"
 * 
 * @version $Id$
 * @package Abricos 
 * @subpackage News
 * @copyright Copyright (C) 2008 Abricos All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin (roosit@abricos.org)
 */

class EShopModule extends Ab_Module {
	
	private $menuData = null;
	/**
	 * @var SitemapMenuItem
	 */
	public $menu = null;
	
	public $currentCatalogItem = null;
	
	/**
	 * Текущий продукт
	 * 
	 * @var integer
	 */
	public $currentProductId = 0;
	
	public $currentProduct = null;
	
	private $catalog = null;
	
	public $catinfo = array();
	
	private $_manager = null;
	private $_catalogManager = null;
	
	/**
	 * @var EShopModule
	 */
	public static $instance = null;
	
	public function EShopModule(){
		$this->version = "0.1.3";
		$this->name = "eshop";
		$this->takelink = "eshop";
		$this->catinfo['dbprefix'] = "eshp";
		EShopModule::$instance = $this;
	}
	
	/**
	 * Получить менеджер
	 *
	 * @return EShopManager
	 */
	public function GetManager(){
		if (is_null($this->_manager)){
			require_once 'includes/manager.php';
			$this->_manager = new EShopManager($this);
		}
		return $this->_manager;
	}
	
	/**
	 * Получить менеджер каталога
	 * 
	 * @return CatalogManager
	 */
	public function GetCatalogManager(){
		if (is_null($this->_catalogManager)){
			$this->_catalogManager = Abricos::GetModule('catalog')->GetManager();
		}
		CatalogQuery::PrefixSet($this->registry->db, $this->catinfo['dbprefix']);
		return $this->_catalogManager;
	}
	
	public function GetContentName(){
		$adress = $this->registry->adress;
		
		if ($adress->level >= 2){
			switch ($adress->dir[1]){
				case 'cart':
				case 'action':
				case 'new':
				case 'hits':
					return $adress->dir[1];
			}
		}
		
		$lastitem = $adress->dir[count($adress->dir)-1];
		
		if (preg_match("/^product_[0-9]+/", $lastitem)){
			
			$arr = explode("_", $lastitem);
			
			// $db = $this->registry->db;
			$catManager = $this->GetCatalogManager();
			
			$this->currentProductId = intval($arr[1]);
			$this->currentProduct = $catManager->Element($this->currentProductId, true); 
			return "product";
		}
		
		// перегрузить кирпич-контент если таков есть исходя из адреса в урле
		// т.е. если идет запрос http://domain.ltd/eshop/mycat/ и в шаблоне есть файл
		// /tt/имя_шаблона/override/eshop/content/products-eshop-mycat.html, то он будет 
		// принят парсером для обработки
		// соответственно, если необходимо перегрузить только корень каталога продукции, то
		// необходимо создать файл products-eshop.html
		$newarr = $adress->dir;
		if (!empty($newarr) && count($newarr) > 0){
			$fname = "products-".implode("-", $newarr);
		}else{
			$fname = "products-eshop";
		}
		return array($fname, "products");
	}
	
	public function &GetCatalog(){
		if (!is_null($this->catalog)){
			return $this->catalog;
		}

		$db = $this->registry->db;
		
		$rows = $this->GetCatalogManager()->CatalogList();
		$catalog = array();
		while (($row = $db->fetch_array($rows))){
			$row["id"] = intval($row["id"]);
			$row["pid"] = intval($row["pid"]);
			$row["lvl"] = intval($row["lvl"]);
			$row["ord"] = intval($row["ord"]);
			$catalog[$row["id"]] = $row;
		}
		$this->catalog = $catalog;
		return $this->catalog;
	}
	
	public function BuildMenu($smMenu, $full){
		$adress = $this->registry->adress;
		
		foreach ($smMenu->menu->child as $mmenu){
			if ($mmenu->link == "/".$this->takelink."/"){
				$this->menu = $mmenu;
				break;
			}
		}
		if (is_null($this->menu)){ return; }
		$delta = 1000000;
		
		$catalog = $this->GetCatalog();
		$menuData = array();
		foreach($catalog as $id => &$row){
			array_push($menuData, array(
				'source' => &$row,
				"id"=> $row['id']+$delta,
				"pid"=> $row['pid']*1 == 0 ? $this->menu->id : $row['pid']+$delta,
				"nm"=> $row['nm'],
				"tl"=> $row['tl'],
				"dsc"=> $row['dsc'],
				"lvl"=> $row['lvl'],
				"ord"=> $row['ord']
			));
		}
		$smMenu->Build($this->menu, $menuData, 1, $full);
		$product = $this->currentProduct;
		if (!is_null($product)){
			array_push($smMenu->menuLine, new SitemapMenuItem(null, 1, 0, 0, 'root', $product['fld_name'], '', count($smMenu->menuLine)));
		}
		$this->catalog = &$catalog;
	}
	
	public function GetFullSubCatalogId(SitemapMenuItem $item){
		$arr = array($item->source['id']);
		foreach ($item->child as $child){
			$chids = $this->GetFullSubCatalogId($child);
			foreach ($chids as $cid){
				array_push($arr, $cid);
			}
		}
		return $arr;
	}
}

class EShopAction {
	const VIEW = 10;
	const WRITE = 30;
	const ADMIN = 50;
}

$modCatalog = Abricos::GetModule('catalog');
if (empty($modCatalog)){ return; }

$modSitemap = Abricos::GetModule('sitemap');

$modEShop = new EShopModule();

$modCatalog->Register($modEShop);
Abricos::ModuleRegister($modEShop);

?>