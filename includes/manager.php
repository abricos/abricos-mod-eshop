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
	 * TODO: на удаление
	 * @deprecated
	 * @var EShopCatalogManager
	 */
	private $cManager;
	
	private $_isRoleDisabled = false;
	
	public function __construct(EShopModule $module){
		parent::__construct($module);
		
		EShopManager::$instance = $this;
		$this->config = new EShopConfig(Abricos::$config['module']['eshop']);
	}
	
	private $_cacheCatalogManager = array();
	
	/**
	 * Менеджер управления каталогом
	 * 
	 * @param integer $teamid
	 * @return EShopCatalogManager
	 */
	public function GetCatalogManager($teamid = 0){
		$teamid = intval($teamid);
		
		if ($teamid == 0){
			// возможно идет глобальный запрос облака
			$teamid = $this->GetCurrentTeamId();
		}
		
		if (!empty($this->_cacheCatalogManager[$teamid])){
			return $this->_cacheCatalogManager[$teamid];
		}
		$this->_cacheCatalogManager[$teamid] = new EShopCatalogManager($dbPrefix, $teamid);
		return $this->_cacheCatalogManager[$teamid];
	}
	
	public function GetCurrentTeamId(){
		return Abricos::CleanGPC('g', 'teamid', TYPE_INT);
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
	
	public function IsModeratorRole(){
		if ($this->IsAdminRole()){ return true; }
		return $this->IsRoleEnable(EShopAction::MODERATOR);
	}
	
	public function IsOperatorRole(){
		if ($this->IsAdminRole()){ return true; }
		return $this->IsRoleEnable(EShopAction::OPERATOR);
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
		
		$catManager = $this->GetCatalogManager($d->teamid);
		
		$ret = $catManager->AJAX($d);
		if (!empty($ret)){ return $ret; }
		
		return null;
	}
	
	private $_cacheElBrickParser;
	
	/**
	 * Получить парсер кирпичей элемента
	 * 
	 * @param EShopElement $el
	 * @return EShopElementBrickParser
	 */
	public function GetElementBrickParser($el){
		require_once 'elbrickparser.php';
		if (!array($this->_cacheElBrickParser)){
			$this->_cacheElBrickParser = array();
		}
		
		if (!empty($this->_cacheElBrickParser[$el->id])){
			return $this->_cacheElBrickParser[$el->id];
		}
		
		$parser = new EShopElementBrickParser($el);
		$this->_cacheElBrickParser[$el->id] = $parser;
		return $parser;
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
		
		$catManager = $this->GetCatalogManager();
		
		$catMain = $catManager->CatalogList()->Find($catid);
		$catList = $catMain->childs;
		
		for($i=0; $i<$catList->Count();$i++){
			$cat = $catList->GetByIndex($i);
			
			$cdir = new OfflineDir($dir, $cat->name);
			$this->BuildOfflineCatalog($cdir, $cat->id);
		}
		
		$cfg = new CatalogElementListConfig();
		$cfg->limit = 500;
		
		array_push($cfg->catids, $catid);
		
		$elList = $catManager->ProductList($cfg);
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
		$catList = $this->GetCatalogManager()->CatalogList();
		
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