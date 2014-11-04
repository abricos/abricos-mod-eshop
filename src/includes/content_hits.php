<?php

$title = Abricos::GetModule('eshop')->GetPhrases()->Get('hits_meta_title', "Хиты продаж");

// Вывод заголовка страницы
if (!empty($title)) {
    Brick::$builder->SetGlobalVar('meta_title', $title);
}

?>