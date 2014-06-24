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
     * TODO: на удаление
     *
     * @var CatalogModule
     */
    private $catalog = null;

    /**
     * Менеджер каталога
     * TODO: на удаление
     *
     * @var CatalogManager
     */
    private $catalogManager = null;

    /**
     * @return EShopCatalogManager
     */
    public function GetCatalogManager() {
        return $this->cManager;
    }

    private $_isRoleDisabled = false;

    public function __construct(EShopModule $module) {
        parent::__construct($module);

        EShopManager::$instance = $this;
        $this->config = new EShopConfig(Abricos::$config['module']['eshop']);

        $this->cManager = new EShopCatalogManager();

        $this->userSession = $this->user->session->key;
    }

    /**
     * Отключить проверку ролей
     *
     * Всем действиям, в том числе и админским - зеленый свет.
     * Используется в основном в процессе инсталяции, например, когда необходимо
     * заполнить модуль товаром, как в модуле eshopportal
     */
    public function RoleDisable() {
        $this->_isRoleDisabled = true;
    }

    public function IsAdminRole() {
        if ($this->_isRoleDisabled) {
            return true;
        }
        return $this->IsRoleEnable(EShopAction::ADMIN);
    }

    public function IsModeratorRole() {
        if ($this->IsAdminRole()) {
            return true;
        }
        return $this->IsRoleEnable(EShopAction::MODERATOR);
    }

    public function IsOperatorRole() {
        if ($this->IsAdminRole()) {
            return true;
        }
        return $this->IsRoleEnable(EShopAction::OPERATOR);
    }

    public function IsWriteRole() {
        if ($this->IsAdminRole()) {
            return true;
        }
        return $this->IsRoleEnable(EShopAction::WRITE);
    }

    public function IsViewRole() {
        if ($this->IsWriteRole()) {
            return true;
        }
        return $this->IsRoleEnable(EShopAction::VIEW);
    }

    public function AJAX($d) {

        $ret = $this->cManager->AJAX($d);
        if (!empty($ret)) {
            return $ret;
        }

        return null;
    }

    private $_cacheElBrickParser;

    /**
     * Получить парсер кирпичей элемента
     *
     * @param EShopElement $el
     * @return EShopElementBrickParser
     */
    public function GetElementBrickParser($el) {
        require_once 'elbrickparser.php';
        if (!array($this->_cacheElBrickParser)) {
            $this->_cacheElBrickParser = array();
        }

        if (!empty($this->_cacheElBrickParser[$el->id])) {
            return $this->_cacheElBrickParser[$el->id];
        }

        $parser = new EShopElementBrickParser($el);
        $this->_cacheElBrickParser[$el->id] = $parser;
        return $parser;
    }

    private function BuildOfflineCatalog(OfflineDir $dir, $catid) {
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

        for ($i = 0; $i < $catList->Count(); $i++) {
            $cat = $catList->GetByIndex($i);

            $cdir = new OfflineDir($dir, $cat->name);
            $this->BuildOfflineCatalog($cdir, $cat->id);
        }

        $cfg = new CatalogElementListConfig();
        $cfg->limit = 500;

        array_push($cfg->catids, $catid);

        $elList = $this->cManager->ProductList($cfg);
        if (empty($elList)) {
            return;
        }

        for ($i = 0; $i < $elList->Count(); $i++) {
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
    public function Offline_Build(OfflineDir $dir) {
        $this->BuildOfflineCatalog($dir, 0);
    }

    /**
     * Использует модуль Sitemap для построения меню товаров
     *
     * @param SMMenuItem $menuItem
     */
    public function Sitemap_MenuBuild(SMMenuItem $mItem) {
        $catList = $this->cManager->CatalogList();

        require_once 'smclasses.php';

        $rootCat = $catList->GetByIndex(0);
        $count = $rootCat->childs->Count();
        for ($i = 0; $i < $count; $i++) {
            $cmItem = new EShopMenuItem($mItem, $rootCat->childs->GetByIndex($i));
            $mItem->childs->Add($cmItem);
        }
    }

    public function Bos_MenuData(){
        $lng = $this->module->lang;
        return [
            [
                "name" => "eshop",
                "title" => $lng['bosmenu']['eshop'],
                "role" => EShopAction::ADMIN,
                "icon" => "/modules/eshop/images/logo-48x48.png",
                "url" => "eshop/wspace/ws"
            ]
        ];
    }

}

?>