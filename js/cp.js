/*
@version $Id$
@package Abricos
@copyright Copyright (C) 2010 Abricos. All rights reserved.
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	mod:[{name: 'user', files: ['cpanel.js']}]
};
Component.entryPoint = function(){
	
	var cp = Brick.mod.user.cp;
	
	// Меню для оператора
	if (Brick.Permission.check('catalog', '30') > 0){
		var menuItem = new cp.MenuItem(this.moduleName, 'operator');
		menuItem.titleId = 'mod.eshop.cp.title.operator';
		menuItem.entryComponent = 'manager';
		menuItem.entryPoint = 'Brick.mod.eshop.API.showCatalogManagerWidget';
		cp.MenuManager.add(menuItem);
	}
	
	// Меню для админа
	if (Brick.Permission.check('catalog', '50') > 0){
		var menuItem = new cp.MenuItem(this.moduleName, 'admin');
		menuItem.titleId = 'mod.eshop.cp.title.admin';
		menuItem.entryComponent = 'manager';
		menuItem.entryPoint = 'Brick.mod.eshop.API.showManagerWidget';
		cp.MenuManager.add(menuItem);
	}

	/*
	if (!Brick.env.user.isAdmin()){ return; }
	
	var cp = Brick.mod.user.cp;
	
	var menuItem = new cp.MenuItem(this.moduleName, 'products');
	menuItem.titleId = 'mod.eshop.cp.title.eshop';
	menuItem.entryComponent = 'manager';
	menuItem.entryPoint = 'Brick.mod.eshop.API.showManagerWidget';
	
	cp.MenuManager.add(menuItem);
	
	var menuItem = new cp.MenuItem(this.moduleName, 'billing');
	menuItem.titleId = 'mod.eshop.cp.title.billing';
	menuItem.entryComponent = 'billing';
	menuItem.entryPoint = 'Brick.mod.eshop.API.showBillingManagerWidget';
	
	// cp.MenuManager.add(menuItem);
	/**/
};
