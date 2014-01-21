<?php 
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
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
		for ($i=0; $i<$count;$i++){
			$citem = new EShopMenuItem($this, $cat->childs->GetByIndex($i));
			$this->childs->Add($citem);
		}
	}
}

?>