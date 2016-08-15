<?php
/**
 * @package Abricos
 * @subpackage EShop
 * @copyright 2012-2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/*
 * Кирпич product
 * 
 * Интернет-магазины очень разные, а перегрузка этого кирпича влечет
 * неприятные последствия сопровождения с выходом новых версий.
 * Поэтому разработан приближено к универсальному механизм сборки страницы 
 * товара, смысл которого в разбиение на блоки. Эти блоки уже проще
 * сопровождать в шаблонах, перегружая не весь кирпич, а только определенный
 * блок
 * 
 * Переменные:
 * includebrick - перечень подключаемых кирпичей
 *
 */

$brick = Brick::$builder->brick;

$mod = EShopModule::$instance;
EShopModule::$instance->GetManager();

$man = EShopModule::$instance->GetManager()->cManager;

$elementid = $mod->currentProductId;
$el = $man->Product($elementid);

require_once 'elbrickparser.php';
$builder = new EShopElementBrickBuilder($el, $brick);
$builder->Build();


// Вывод заголовка страницы.
if (!empty($el->detail->metaTitle) && $el->detail->metaTitle != "&nbsp;"){
    Brick::$builder->SetGlobalVar('meta_title', $el->detail->metaTitle);
} else if (!empty($el->title) && $el->title != "&nbsp;"){
    Brick::$builder->SetGlobalVar('meta_title', $el->title);
}
// Вывод ключевых слов
if (!empty($el->detail->metaKeys) && $el->detail->metaKeys != "&nbsp;"){
    Brick::$builder->SetGlobalVar('meta_keys', $el->detail->metaKeys);
}
// Вывод описания
if (!empty($el->detail->metaDesc) && $el->detail->metaDesc != "&nbsp;"){
    Brick::$builder->SetGlobalVar('meta_desc', $el->detail->metaDesc);
}
