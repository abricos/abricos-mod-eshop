<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @copyright 2012-2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$title = Abricos::GetModule('eshop')->GetPhrases()->Get('hits_meta_title', "Хиты продаж");

// Вывод заголовка страницы
if (!empty($title)){
    Brick::$builder->SetGlobalVar('meta_title', $title);
}
