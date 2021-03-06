<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @copyright 2012-2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Интернет-магазин
 */
class EShopModule extends Ab_Module {

    /**
     * Текущий продукт
     *
     * @var integer
     */
    public $currentProductId = 0;

    private $_manager = null;
    private $_catalogManager = null;

    /**
     * @var EShopModule
     */
    public static $instance = null;

    public $catinfo = array(
        "dbprefix" => 'eshp'
    );

    public function __construct(){
        $this->version = "0.2.4";
        $this->name = "eshop";
        $this->takelink = "eshop";
        EShopModule::$instance = $this;

        $this->permission = new EShopPermission($this);
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
     * TODO: на удаление
     *
     * @return CatalogManager
     */
    private function GetCatalogManager(){
        if (is_null($this->_catalogManager)){
            $this->_catalogManager = Abricos::GetModule('catalog')->GetManager();
        }
        return $this->_catalogManager;
    }

    public function GetContentName(){
        $adress = Abricos::$adress;

        if ($adress->level >= 2){
            switch ($adress->dir[1]){
                case 'cart':
                case 'action':
                case 'new':
                case 'hits':
                case 'search':
                    return $adress->dir[1];
            }
        }

        $lastitem = $adress->dir[count($adress->dir) - 1];

        if (preg_match("/^product_[0-9]+/", $lastitem)){

            $arr = explode("_", $lastitem);

            // $db = Abricos::$db;
            $catManager = $this->GetCatalogManager();

            $this->currentProductId = intval($arr[1]);

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
        } else {
            $fname = "products-eshop";
        }
        return array(
            $fname,
            "products"
        );
    }

    /**
     * Этот модуль осуществляет оффлайн выгрузку
     */
    public function Offline_IsBuild(){
        return true;
    }

    /**
     * Этот модуль добавляет пункты меню в главное меню
     */
    public function Sitemap_IsMenuBuild(){
        return true;
    }

    /**
     * Этот модуль добавляет элементы меню в Bos
     *
     * @return bool
     */
    public function Bos_IsMenu(){
        return true;
    }

    public function Bos_IsSummary(){
        return true;
    }
}

class EShopAction {
    const VIEW = 10;
    const WRITE = 30;
    const OPERATOR = 40;
    const MODERATOR = 45;
    const ADMIN = 50;
}

class EShopPermission extends Ab_UserPermission {

    public function __construct(EShopModule $module){
        $defRoles = array(
            new Ab_UserRole(EShopAction::VIEW, Ab_UserGroup::GUEST),
            new Ab_UserRole(EShopAction::VIEW, Ab_UserGroup::REGISTERED),
            new Ab_UserRole(EShopAction::VIEW, Ab_UserGroup::ADMIN),

            new Ab_UserRole(EShopAction::WRITE, Ab_UserGroup::ADMIN),
            new Ab_UserRole(EShopAction::OPERATOR, Ab_UserGroup::ADMIN),
            new Ab_UserRole(EShopAction::MODERATOR, Ab_UserGroup::ADMIN),

            new Ab_UserRole(EShopAction::ADMIN, Ab_UserGroup::ADMIN),
        );
        parent::__construct($module, $defRoles);
    }

    public function GetRoles(){
        return array(
            EShopAction::VIEW => $this->CheckAction(EShopAction::VIEW),
            EShopAction::WRITE => $this->CheckAction(EShopAction::WRITE),
            EShopAction::OPERATOR => $this->CheckAction(EShopAction::OPERATOR),
            EShopAction::MODERATOR => $this->CheckAction(EShopAction::MODERATOR),
            EShopAction::ADMIN => $this->CheckAction(EShopAction::ADMIN)
        );
    }
}

$modCatalog = Abricos::GetModule('catalog');
if (empty($modCatalog)){
    return;
}

$modSitemap = Abricos::GetModule('sitemap');

$modEShop = new EShopModule();

$modCatalog->Register($modEShop);
Abricos::ModuleRegister($modEShop);
