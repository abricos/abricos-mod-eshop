/*
@package Abricos
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	yahoo: ['tabview','dragdrop'],
    mod:[
         {name: 'catalog', files: ['catalog.js','eltype.js']},
         {name: 'eshop', files: ['billing.js']}
    ]
};
Component.entryPoint = function(NS){
	var Dom = YAHOO.util.Dom,
		E = YAHOO.util.Event,
		L = YAHOO.lang;

	var buildTemplate = this.buildTemplate;
	
	var ConfigWidget = function(container){
		this.init(container);
	};
	ConfigWidget.prototype = {
		init: function(container){
		
			var TM = buildTemplate(this, 'widget');
			container.innerHTML = TM.replace('widget');
			
			new Brick.mod.catalog.API.showElementTypeManagerWidget({
				'container': TM.getEl('widget.widget'),
				'mmPrefix': 'eshop' 
			});
			NS.data.request();
		}
	};
	NS.ConfigWidget = ConfigWidget;
	
	NS.API.showConfigWidget = function(container){
		var widget = new NS.ConfigWidget(container);
		return widget;
	};

	NS.API.showCatalogManagerWidget = function(container){
		new Brick.mod.catalog.API.showManagerWidget({
			'container': container, 
			'mmPrefix': 'eshop'
		});
	}
	
};
