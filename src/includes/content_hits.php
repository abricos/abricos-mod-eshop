<?php

// $brick = Brick::$builder->brick;

$title = Brick::$builder->phrase->Get('eshop', 'hits_meta_title', "Хиты продаж");

// Вывод заголовка страницы
if (!empty($title)) {
    Brick::$builder->SetGlobalVar('meta_title', $title);
}

?>