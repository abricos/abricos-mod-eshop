<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @copyright 2012-2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class EShopMenuItem
 */
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
        for ($i = 0; $i < $count; $i++){
            $citem = new EShopMenuItem($this, $cat->childs->GetByIndex($i));
            $this->childs->Add($citem);
        }
    }
}

class EShopRootMenuItem extends SMMenuItem {

    public function __construct(SMMenuItemList $menuItemList){
        parent::__construct(array(
            "id" => SMMenuItem::ToGlobalId("eshop", 0),
            "pid" => 0,
            "nm" => "eshop",
            "tl" => "eshop",
            "off" => 1
        ));
    }
}
