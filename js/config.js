/**
* @version $Id$
* @package Abricos
* @copyright Copyright (C) 2010 Abricos. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	yahoo: ['tabview','resize'],
	mod:[
	     {name: 'sys', files: ['data.js', 'container.js', 'wait.js', 'form.js']},
	     {name: 'eshop', files: ['delivery.js', 'payment.js', 'discount.js']}
	]
};
Component.entryPoint = function(){
	var Dom = YAHOO.util.Dom,
		E = YAHOO.util.Event,
		L = YAHOO.lang;
	
	var NS = this.namespace,
		API = NS.API,
		TMG = this.template;
	
	NS['config'] = NS['config'] || {}; 
	
	if (!NS.data){
		NS.data = new Brick.util.data.byid.DataSet('eshop');
	}
	var DATA = NS.data;
	
	var LW = Brick.widget.LayWait;

	var buildTemplate = function(widget, templates){
		var TM = TMG.build(templates), T = TM.data, TId = TM.idManager;
		widget._TM = TM; 
		widget._T = T; 
		widget._TId = TId;
	};

	var Manager = function(container){
		this.init(container);
	};
	
	Manager.prototype = {
		init: function(container){
			buildTemplate(this, 'widget');
			container.innerHTML = this._T['widget'];
			
			var __self = this;
			E.on(container, 'click', function(e){
				if (__self.onClick(E.getTarget(e))){ E.stopEvent(e); }
			});
			
			var TM = this._TM;
			
			var tabView = new YAHOO.widget.TabView(TM.getElId('widget.id'));
			
			this.page = {
				'pay': new NS.payment.ManagerWidget(TM.getEl('widget.pay')),
				'delivery': new NS.delivery.ManagerWidget(TM.getEl('widget.delivery')),
				'discount': new NS.discount.ManagerWidget(TM.getEl('widget.discount')),
				'over': new MainConigWidget(TM.getEl('widget.over'))
			};
		},
		onClick: function(el){
			for (var nn in this.page){
				if (this.page[nn].onClick(el)){ return true; }
			}
			return false;
		}
	};
	NS.config.Manager = Manager;
	
	API.showConfigManagerWidget = function(container){
		new Manager(container);
		DATA.request();
	};

	var MainConigWidget = function(container){
		this.init(container);
	};
	MainConigWidget.prototype = {
		init: function(container){
			var TM = TMG.build('overconfig'), T = TM.data, TId = TM.idManager;
			this._TM = TM; this._T = T; this._TId = TId;
	
			container.innerHTML = T['overconfig'];
			
			var tables = {
				'config': DATA.get('config', true)
			};
			DATA.onStart.subscribe(this.dsComplete, this, true);
			DATA.onComplete.subscribe(this.dsComplete, this, true);
			if (DATA.isFill(this.tables)){
				this.render();
			}else{
				this.renderWait();
			}
		},
		dsComplete: function(type, args){
			if (!args[0].checkWithParam('config', {})){ return; }
			if (type == 'onComplete'){
				this.render(); 
			}else{
				this.renderWait();
			}
		},
		destroy: function(){
			DATA.onComplete.unsubscribe(this.dsComplete);
			DATA.onStart.unsubscribe(this.dsComplete);
		},
		renderWait: function(){ },
		render: function(){
			var TM = this._TM, T = this._T;
			var cfg = DATA.get('config').getRows().getByIndex(0).cell;
			TM.getEl('overconfig.adm_emails').value = cfg['adm_emails'];
			TM.getEl('overconfig.adm_notify_subj').value = cfg['adm_notify_subj'];
			TM.getEl('overconfig.adm_notify').value = cfg['adm_notify'];
		},
		onClick: function(el){
			if (el.id == this._TId['overconfig']['bsave']){
				this.save();
				return true;
			}
			return false;
		},
		save: function(){
			var table = DATA.get('config');
			table.getRows().getByIndex(0).update({
				'adm_emails': this._TM.getEl('overconfig.adm_emails').value
			});
			table.applyChanges();
			DATA.request();
		}
	};
	
	NS.config.MainConigWidget = MainConigWidget;
};
