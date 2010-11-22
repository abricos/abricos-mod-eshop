/**
* @version $Id$
* @package Abricos
* @copyright Copyright (C) 2010 Abricos. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	mod:[{name: 'sys', files: ['data.js', 'container.js', 'wait.js', 'form.js']}]
};
Component.entryPoint = function(){
	var Dom = YAHOO.util.Dom,
		E = YAHOO.util.Event,
		L = YAHOO.lang;
	
	var NS = this.namespace,
		API = NS.API,
		TMG = this.template;

	NS['discount'] = NS['discount'] || {}; 

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
	
	var ManagerWidget = function(container){
		this.init(container);
	};
	
	ManagerWidget.prototype = {
		init: function(container){
			buildTemplate(this, 'manwidget,table,row,rowwait');
			
			container.innerHTML = this._T['manwidget'];
			
			var tables = {
				'discount': DATA.get('discount', true)
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
			if (args[0].checkWithParam('discount', {})){
				if (type == 'onComplete'){
					this.render(); 
				}else{
					this.renderWait();
				}
			}
		},
		destroy: function(){
			DATA.onComplete.unsubscribe(this.dsComplete);
			DATA.onStart.unsubscribe(this.dsComplete);
		},
		renderWait: function(){
			var TM = this._TM, T = this._T;
			TM.getEl('manwidget.table').innerHTML = TM.replace('table', {'rows': T['rowwait']});
		},
		render: function(){
			var __self = this, lst = "", TM = this._TM, T = this._T;
			DATA.get('discount').getRows().foreach(function(row){
				var di = row.cell;
				var sel = 'selected="selected"';
				lst += TM.replace('row', {
					'tl': di['tl'],
					'seltp0': di['tp']*1 < 1 ? sel : '',
					'seltp1': di['tp']*1 > 0 ? sel : '',
					'pc': di['pc'],
					'selprc0': di['prc']*1 < 1 ? sel : '',
					'selprc1': di['prc']*1 > 0 ? sel : '',
					'fsm': di['fsm'],
					'esm': di['esm'],
					'id': di['id']
				});
			});

			this._TM.getEl('manwidget.table').innerHTML = TM.replace('table', {'rows': lst});
		},
		onClick: function(el){
			var TId = this._TId;
			if (el.id == TId['manwidget']['bappend']){
				this.edit(0);
				return true;
			}
			
			var prefix = el.id.replace(/([0-9]+$)/, '');
			var numid = el.id.replace(prefix, "");
			
			switch(prefix){
			case (this._TId['row']['edit']+'-'):
				this.edit(numid);
				return true;
			case (this._TId['row']['remove']+'-'):
				this.remove(numid);
				return true;
			}
			
			return false;
		},
		edit: function(id){
			
			id = id*1 || 0;
			var table = DATA.get('discount'),
				rows = table.getRows(),
				row = id == 0 ? table.newRow() : rows.getById(id);
			
			new EditorPanel(row, function(){
				if (id == 0){ rows.add(row); }
				table.applyChanges();
				DATA.request();
			});
		},
		remove: function(id){
			var table = DATA.get('discount'),
				rows = table.getRows(),
				row = rows.getById(id);
			
			new RemovePanel(row, function(){
				row.remove();
				table.applyChanges();
				DATA.request();
			});
		}
	};
	NS.discount.ManagerWidget = ManagerWidget;
	
	API.showDiscountManagerWidget = function(container){
		var widget = new ManagerWidget(container);
		DATA.request();
		return widget;
	};
	
	var ManagerPanel = function(){
		ManagerPanel.superclass.constructor.call(this, {
			fixedcenter: true, resize: true, 
			width: '600px', height: '400px'
		});
	};
	YAHOO.extend(ManagerPanel, Brick.widget.Panel, {
		initTemplate: function(){
			buildTemplate(this, 'manpanel');
			return this._T['manpanel'];
		},
		onLoad: function(){
			this.widget = new ManagerWidget(this._TM.getEl('manpanel.widget'));
		},
		onClick: function(el){
			return this.widget.onClick(el);
		}
	});
	NS.discount.ManagerPanel = ManagerPanel;
	
	API.showDiscountManagerPanel = function(){
		var panel = new ManagerPanel();
		DATA.request();
		return panel;
	};
	
	var EditorPanel = function(row, callback){
		this.row = row;
		this.callback = callback;
		EditorPanel.superclass.constructor.call(this, {
			modal: true,
			fixedcenter: true, width: '600px', resize: true
		});
	};
	
	YAHOO.extend(EditorPanel, Brick.widget.Panel, {
		el: function(name){ return Dom.get(this._TId['editorpanel'][name]); },
		elv: function(name){ return Brick.util.Form.getValue(this.el(name)); },
		setelv: function(name, value){ Brick.util.Form.setValue(this.el(name), value); },
		initTemplate: function(){
			buildTemplate(this, 'editorpanel');
			return this._T['editorpanel'];
		},
		onLoad: function(){
			var di = this.row.cell;
			this.setelv('tl', di['tl']);
			this.setelv('dsc', di['dsc']);
			this.setelv('tp', di['tp']);
			this.setelv('pc', di['pc']);
			this.setelv('prc', di['prc']);
			this.setelv('fsm', di['fsm']);
			this.setelv('esm', di['esm']);
		},
		onClick: function(el){
			var tp = this._TId['editorpanel'];
			switch(el.id){
			case tp['bcancel']: this.close(); return true;
			case tp['bsave']: this.save(); return true;
			}
			return false;
		},
		save: function(){
			
			this.row.update({
				'tl': this.elv('tl'),
				'dsc': this.elv('dsc'),
				'tp': this.elv('tp'),
				'pc': this.elv('pc'),
				'prc': this.elv('prc'),
				'fsm': this.elv('fsm'),
				'esm': this.elv('esm')
			});
			
			this.callback();
			this.close();
		}
	});
	
	var RemovePanel = function(row, callback){
		this.row = row;
		this.callback = callback;
		RemovePanel.superclass.constructor.call(this, {
			modal: true, resize: false,
			fixedcenter: true, width: '400px'
		});
	};
	YAHOO.extend(RemovePanel, Brick.widget.Panel, {
		initTemplate: function(){
			buildTemplate(this, 'removepanel');
			return this._TM.replace('removepanel', {
				'tl': this.row.cell['tl']
			});
		},
		onClick: function(el){
			var tp = this._TId['removepanel'];
			switch(el.id){
			case tp['bcancel']: this.close(); return true;
			case tp['bremove']: this.remove(); return true;
			}
			return false;
		},
		remove: function(){
			this.callback();
			this.close();
		}
	});

};
