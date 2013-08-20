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
	 * @var EShopModule
	 */
	public $module;
	
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
	
	private $_isRoleDisabled = false;
	
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
	
	/**
	 * Отключить проверку ролей
	 * 
	 * Всем действиям, в том числе и админским - зеленый свет.
	 * Используется в основном в процессе инсталяции, например, когда необходимо 
	 * заполнить модуль товаром, как в модуле eshopportal
	 */
	public function RoleDisable(){
		$this->_isRoleDisabled = true;
	}
	
	public function IsAdminRole(){
		if ($this->_isRoleDisabled){ return true; }
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
		
		$cfg = new CatalogElementListConfig();
		$cfg->limit = 500;
		
		array_push($cfg->catids, $catid);
		
		$elList = $this->cManager->ProductList($cfg);
		if (empty($elList)){
			return;
		}
		
		for ($i=0; $i<$elList->Count(); $i++){
			$product = $elList->GetByIndex($i);
			$brick = Brick::$builder->LoadBrickS("eshop", "offline_product", null, array(
				"p" => array(
					"dir" => $dir,
					"productid" => $product->id
				)
			));
			$offMan->WritePage($dir, "product".$product->id, $brick->content);
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

	
}

?>