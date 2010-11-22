/*
@version $Id: app.js 694 2010-08-26 07:31:16Z roosit $
@copyright Copyright (C) 2008 Abricos All rights reserved.
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = { 
	mod:[{name: 'webos', files: ['os.js']}]
};
Component.entryPoint = function(){
	var os = Brick.mod.webos, app;
	
	// Ярлыки для оператора
	if (Brick.Permission.check('catalog', '30') > 0){
		app = new os.Application(this.moduleName, 'operator');
		app.icon = '/modules/eshop/images/app_icon_operator.gif';
		app.titleId = 'mod.eshop.app.title.operator';
		app.entryComponent = 'manager';
		app.entryPoint = 'Brick.mod.eshop.API.showCatalogManagerPanel';
		
		os.ApplicationManager.register(app);
	}
	
	// Ярлыки для админа
	if (Brick.Permission.check('catalog', '50') > 0){
		app = new os.Application(this.moduleName, 'admin');
		app.icon = '/modules/eshop/images/app_icon_admin.gif';
		app.titleId = 'mod.eshop.app.title.admin';
		app.entryComponent = 'manager';
		app.entryPoint = 'Brick.mod.eshop.API.showManagerPanel';
		
		os.ApplicationManager.register(app);
	}
	
};
