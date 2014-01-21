/*
@package Abricos
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	mod:[{name: 'user', files: ['cpanel.js']}]
};
Component.entryPoint = function(){
	/*
	var cp = Brick.mod.user.cp;
	
	// Меню для оператора
	if (Brick.Permission.check('catalog', '30') > 0){
		var menuItem = new cp.MenuItem(this.moduleName, 'operator');
		menuItem.icon = '/modules/sys/images/cp_icon.gif';
		menuItem.titleId = 'mod.eshop.cp.title.operator';
		menuItem.entryComponent = 'manager';
		menuItem.entryPoint = 'Brick.mod.eshop.API.showCatalogManagerWidget';
		cp.MenuManager.add(menuItem);
	}
	
	if (Brick.Permission.check('catalog', '50') > 0){
		// заказы в магазине
		var menuItem = new cp.MenuItem(this.moduleName, 'billing');
		menuItem.icon = '/modules/sys/images/cp_icon.gif';
		menuItem.titleId = 'mod.eshop.cp.title.billing';
		menuItem.entryComponent = 'billing';
		menuItem.entryPoint = 'Brick.mod.eshop.API.showBillingManagerWidget';
		cp.MenuManager.add(menuItem);

		// настройка магазина
		var menuItem = new cp.MenuItem(this.moduleName, 'admin');
		menuItem.icon = '/modules/sys/images/cp_icon.gif';
		menuItem.titleId = 'mod.eshop.cp.title.admin';
		menuItem.entryComponent = 'manager';
		menuItem.entryPoint = 'Brick.mod.eshop.API.showConfigWidget';
		cp.MenuManager.add(menuItem);
	}
/**/
};
