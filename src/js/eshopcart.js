/*
@package Abricos
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	mod:[
		{name: '{C#MODNAME}', files: ['lib.js']}
	]
};
Component.entryPoint = function(NS){
	
	var L = YAHOO.lang,
		buildTemplate = this.buildTemplate;
	
	var CartBillingWidget = function(container){
		CartBillingWidget.superclass.constructor.call(this, container, {
			'buildTemplate': buildTemplate, 'tnames': 'billing' 
		});
	};
	YAHOO.extend(CartBillingWidget, Brick.mod.widget.Widget, {
		init: function(){
			this.wsMenuItem = 'cartbilling'; // использует wspace.js
			this.viewWidget = null;
		},
		destroy: function(){
			if (L.isValue(this.viewWidget)){
				this.viewWidget.destroy();
			}
			CartBillingWidget.superclass.destroy.call(this);
		},
		onLoad: function(){
			var __self = this;
			Brick.ff('eshopcart', 'billing', function(){
				__self._onLoadWidget();
			});
		},
		_onLoadWidget: function(){
			this.elHide('loading');
			this.viewWidget = new Brick.mod.eshopcart.BillingWidget(this.gel('view'));
		}
	});
	NS.CartBillingWidget = CartBillingWidget;
	
	
	var CartConfigWidget = function(container){
		CartConfigWidget.superclass.constructor.call(this, container, {
			'buildTemplate': buildTemplate, 'tnames': 'config' 
		});
	};
	YAHOO.extend(CartConfigWidget, Brick.mod.widget.Widget, {
		init: function(){
			this.wsMenuItem = 'cartconfig'; // использует wspace.js
			this.viewWidget = null;
		},
		destroy: function(){
			if (L.isValue(this.viewWidget)){
				this.viewWidget.destroy();
			}
			CartConfigWidget.superclass.destroy.call(this);
		},
		onLoad: function(){
			var __self = this;
			Brick.ff('eshopcart', 'config', function(){
				__self._onLoadWidget();
			});
		},
		_onLoadWidget: function(){
			this.elHide('loading');
			this.viewWidget = new Brick.mod.eshopcart.ConfigWidget(this.gel('view'));
		}
	});
	NS.CartConfigWidget = CartConfigWidget;
	
	
};