<?php 
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

require_once 'dbquery.php';

class EShopMenuItem extends SMMenuItem {
	
	/**
	 * @var Catalog
	 */
	public $cat;
	
	public function __construct(SMMenuItem $parent, Catalog $cat){
		parent::__construct(array(
			"id" => SMMenuItem::ToGlobalId("eshop", $cat->id),
			"pid" => $parent->id,
			"nm" => $cat->name,
			"tl" => $cat->title
		));
		
		$this->cat = $cat;
		
		$count = $cat->childs->Count();
		for ($i=0; $i<$count;$i++){
			$citem = new EShopMenuItem($this, $cat->childs->GetByIndex($i));
			$this->childs->Add($citem);
			$citem->level = $parent->level+1;
		}
	}
}

class EShopConfig {
	
	/**
	 * @var EShopConfig
	 */
	public static $instance;
	
	/**
	 * Количество товаров на странице
	 * @var integer
	 */
	public $productPageCount = 12;
	
	/**
	 * SEO оптимизация страниц
	 * @var boolean
	 */
	public $seo = false;
	
	public function __construct($cfg){
		EShopConfig::$instance = $this;
		
		if (empty($cfg)){ $cfg = array(); }
		
		if (isset($cfg['productpagecount'])){
			$this->productPageCount = intval($cfg['productpagecount']);
		}
		
		if (isset($cfg['seo'])){
			$this->seo = $cfg['seo'];
		}
	}
}

class EShopCatalogManager extends CatalogModuleManager {
	
	/**
	 * @var EShopManager
	 */
	public $manager;
	
	public function __construct(){
		$this->manager = EShopManager::$instance;

		parent::__construct("eshp");
	}
	
	public function IsAdminRole(){
		return $this->manager->IsAdminRole();
	}
	
	public function IsWriteRole(){
		return $this->manager->IsWriteRole();
	}
	
	public function IsViewRole(){
		return $this->manager->IsViewRole();
	}
}


?>