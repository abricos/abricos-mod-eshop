/**
 * @version $Id$
 * @package Abricos
 * @copyright Copyright (C) 2010 Abricos. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

var Component = new Brick.Component();
Component.requires = {
	mod:[{name: 'eshop', files: ['cart.js']}]
};
Component.entryPoint = function(NS){
	var Dom = YAHOO.util.Dom,
		E = YAHOO.util.Event,
		L = YAHOO.lang;
	
	var buildTemplate = this.buildTemplate;
	var TMG = this.template;
	
	if (!NS.data){
		NS.data = new Brick.util.data.byid.DataSet('eshop');
	}
	var DATA = NS.data;
	
	var LW = Brick.widget.LayWait;

	NS.API.showOrderPanel = function(){
		if (!L.isNull(NS.OrderPanel.instance)){ return; }
		var panel = new NS.OrderPanel();
		DATA.request();
		return panel;
	};

	var BaseWidget = function(owner, name){
		this.init(owner, name);
	};
	BaseWidget.prototype = {
		init: function(owner, name){
			this.owner = owner;
			this.name = name;
			this._isDestroy = false;
		},
		getEl: function(id){
			return this._TM.getEl(this.name+'.'+id);
		},
		getVal: function(id){
			var el = this.getEl(id);
			if (Dom.hasClass(el, 'node')){
				return '';
			}
			return el.value + '';
		},
		renderElements: function(){},
		renderWait: function(){},
		showErrorFileds: function(name){
			var el = this.getEl('lbl'+name);
			if (L.isNull(el)){ return; }
			var i = 0,
				j = false,
				__self = this;

			var saveCLR = Dom.getStyle(el, 'color');

			Dom.setStyle(el, 'color', 'red');
			
			var thread = null;
			thread = setInterval(function(){
				if (i++ > 20 || __self._isDestroy){
					clearInterval(thread);
					Dom.setStyle(el, 'color', saveCLR);
					return;
				}
				Dom.setStyle(el, 'color', j ? 'red' : saveCLR);
				j = !j;
			}, 100);
		},
		checkErrorFields: function(names){
			var arr = names.split(','),
				isError = false;
			for (var i=0;i<arr.length;i++){
				if (this.getVal(arr[i]).length < 1){
					this.showErrorFileds(arr[i]);
					isError = true;
				}
			}
			return isError;
		},
		display: function(id, show){
			this.getEl(id).style.display = show ? '' : 'none';
		},
		initTemplate: function(overtpl){
			var TM = buildTemplate(this, this.name + (overtpl ? ','+overtpl : ''));
			
			return TM.replace(this.name);
		},
		onLoad: function(){
			this.element = this._TM.getEl(this.name+'.id');
		},
		destroy: function(){
			this._isDestroy = true;
		},
		onClick: function(el){ return false; },
		onShow: function(){},
		last: function(){},
		next: function(){},
		getData: function(){ return {}; },
		print: function(){ return ''; }
	};

	var AuthWidget = function(owner){
		AuthWidget.superclass.constructor.call(this, owner, 'auth');
	};
	YAHOO.extend(AuthWidget, BaseWidget, {
		init: function(owner, name){
			AuthWidget.superclass.init.call(this, owner, name);
			this.type = 'reg';
			
			// пользователь произвел авторизацию в этой форме
			this.isAuthorize = false;

			// пользователь решил зарегистрироваться и прошел проверку логина и мыла
			this.isNewUser = false;

			// является ли пользователь зарегистрированным
			this.isRegister = Brick.env.user.isRegister();
		},
		onClick: function(el){
			var tp = this._TId[this.name];
			switch(el.id){
			case tp['bnewuser']: this.setType('reg'); return false;
			case tp['bauth']: this.setType('auth'); return false;
			case tp['guest']: this.setType('guest'); return false;
			case tp['bnonauth']: this.setType(''); return false;
			}
			return false;
		},
		setType: function(type){
			this.type = type;
			switch(type){
			case 'reg':
				this.display('reg', true);
				this.display('auth', false);
				this.display('guest', false);
				break;
			case 'auth':
				this.display('reg', false);
				this.display('auth', true);
				this.display('guest', false);
				break;
			case 'guest':
				this.display('reg', false);
				this.display('auth', false);
				this.display('guest', true);
				break;
			default:
				this.display('reg', false);
				this.display('auth', false);
				this.display('guest', false);
			}
		},
		last: function(){
			this.owner.close();
		},
		next: function(){
			var __self = this;
			var empty = function(el){
				return L.isString(el) ? el.length < 1 : el.value.length < 1;
			};
			if (this.type == 'auth'){
				var elLogin = this.getEl('authlogin'),
					elPass = this.getEl('authpass');
				__self.owner.waitStart();
				Brick.ajax('eshop',{
					'data': {
						'do': 'auth',
						'login': elLogin.value,
						'password': elPass.value
					},
					'event': function(request){
						__self.owner.waitStop();
						var err = 999;
						try{
							err = request.data.error; 
						}catch(ex){}
						if (err > 0){
							alert(__self.getEl('autherr'+err).innerHTML);
							elLogin.value = '';
							elPass.value = '';
							return;
						}
						__self.isAuthorize = true;
						__self.isRegister = true;
						__self.owner.orderInfo = request.data.orderinfo;
						__self.owner.showPage('deli');
					}
				});

			}else if(this.type == 'reg' && !this.isNewUser){
				
				var showErr = function(err){
					alert(__self.getEl('rerr'+err).innerHTML);
				};

				var elEmail = this.getEl('regemail'),
					elLogin = this.getEl('reglogin'),
					elPass = this.getEl('regpass'),
					elPassConf = this.getEl('regpassconf');
				
				var sEmail = elEmail.value,
					sLogin = elLogin.value,
					sPass = elPass.value,
					sPassConf = elPassConf.value;
				
				if (empty(elEmail) || empty(elLogin) || empty(elPass) || empty(elPassConf)){
					showErr('c1');
					return;
				}
				if (sPass != sPassConf){
					showErr('c2');
					return;
				}
				
				__self.owner.waitStart();
				Brick.ajax('eshop', {
					'data': {
						'do': 'checkreg',
						'email': sEmail,
						'login': sLogin
					},
					'event': function(request){
						__self.owner.waitStop();
						
						var err = 999;
						try { err = request.data.error; } catch(ex){}
						
						if (err > 0){
							showErr(err);
							// elEmail.value = elLogin.value = elPass.value = elPassConf.value = '';
							return;
						}
						elEmail.disabled = elLogin.disabled = elPass.disabled = elPassConf.disabled = 'disabled';
						__self.isNewUser = true;
						__self.owner.showPage('deli');
					}
				});
				
			} else {
				this.owner.showPage('deli');
			}
		},
		getData: function(){
			if (this.isNewUser){
				return {
					'type': 'reg',
					'email': this.getVal('regemail'),
					'login': this.getVal('reglogin'),
					'pass': this.getVal('regpass')
				};
			}
			return {};
		},
		print: function(){
			if (!this.isNewUser){ return ""; }

			var d = this.getData();
			return this._TM.replace('authprint', d);
		}
		
	});
	

	var DeliWidget = function(owner){
		DeliWidget.superclass.constructor.call(this, owner, 'deli');
	};
	YAHOO.extend(DeliWidget, BaseWidget, {
		init: function(owner, name){
			DeliWidget.superclass.init.call(this, owner, name);
			this.deliveryid = null;
		},
		renderWait: function(){
			this._TM.getEl('deli.table').innerHTML = this._TM.replace('delitable', {'rows': this._T['delirowwait']});
		},
		renderElements: function(){
			var TM = this._TM, T = this._T, TId = this._TId,
				rows = DATA.get('delivery').getRows(),
				apath = {};
			
			var isDelivery = rows.count() > 0;
			if (!isDelivery){
				this.owner._TM.getEl('panel.bk-deli').style.display = 'none';
				TM.getEl('deli.deliselect').style.display = 'none';
			}

			var buildNode = function(pid, level, path){
				var lst = "";
				rows.filter({'pid': pid}).foreach(function(row){
					var di = row.cell, id = di['id'];
					var npath = path + '-' + id;
					apath[id] = npath; 
					lst += TM.replace('delirow', {
						'tl': di['tl'],
						'id': di['id'],
						'path': npath,
						'level': level,
						'child': buildNode(di['id'], level+1, npath)
					});
				});
				return lst.length > 0 ? TM.replace(pid > 0 ? 'delitablesub' : 'delitable', {
					'pid': pid,
					'level': level,
					'rows': lst
				}) : '';
			};
			
			TM.getEl('deli.table').innerHTML = buildNode(0, 0, 0);

			var nodes = {};
			nodes['0'] = {
				'path': ['0', '0'], 
				'el': Dom.get(TId['delitable']['node']),
				'child': null,
				'rt': Dom.get(TId['delitable']['mydeli'])
			};
			rows.foreach(function(row){
				var id = row.cell['id'];
				nodes[id] = {
					'path': apath[id].split('-'), 
					'el': Dom.get(TId['delirow']['node']+'-'+id),
					'child': Dom.get(TId['delitablesub']['id']+'-'+id),
					'rt': Dom.get(TId['delirow']['id']+'-'+id)
				};
			});
			this.nodes = nodes;

			if (rows.count()>0){
				this.setDelivery(rows.getByIndex(0).cell['id']);				
			}
		},
		onClick: function(el){
			var TId = this._TId;
			if (el.id == TId['delitable']['mydeli']){
				this.setDelivery(0);
				return false;
			}
			
			var prefix = el.id.replace(/([0-9]+$)/, '');
			var numid = el.id.replace(prefix, "");
			
			if (prefix == TId['delirow']['id']+'-'){
				this.setDelivery(numid);
				return false;
			}
			return false;
		},
		setDelivery: function(deliid){
			deliid = deliid * 1;
			this.deliveryid = deliid;
			
			this.display('adressrow', deliid > 0);
			
			var enids = this.nodes[deliid]['path']; 

			var checkid = function(path, sub){
				var plength = path.length,
					pcount = Math.max(plength, 0),
					ecount = enids.length,
					count = Math.min(path.length, enids.length);
				
				for (var i=0;i<count;i++){
					if (enids[i] != path[i]){ return false; }
				}
				if (pcount > ecount){ return false; }
				return true;
			};
			var nd, ck;
			for (var id in this.nodes){
				nd = this.nodes[id];
				ck = checkid(nd['path'], false);
				if (!L.isNull(nd['child'])){
					nd['child'].style.display = ck ? '' : 'none';
				}
				nd['rt'].checked = !ck ? '' : 'checked';
			}
		},
		last: function(){
			if (this.owner.authWidget.isRegister){
				this.owner.close();
			}else{
				this.owner.showPage('auth');
			}
		},
		next: function(){
			
			if (this.checkErrorFields('fam,im,phone')){ return; }
			if (this.deliveryid > 0){
				if (this.checkErrorFields('adress')){ return; }
			}
			
			this.owner.showPage('pay');
		},
		getData: function(){
			return {
				'deliveryid': this.deliveryid,
				'lastname': this.getVal('fam'),
				'firstname': this.getVal('im'),
				'phone': this.getVal('phone'),
				'adress': this.getVal('adress'),
				'extinfo': this.getVal('extinfo')
			};
		},
		print: function(){
			var d = this.getData(),
				deliid = d['deliveryid']*1;

			d['hideadr'] = deliid > 0 ? '' : 'none';
			
			var row = DATA.get('delivery').getRows().getById(deliid);
			d['delitl'] = L.isNull(row) ? '' : row.cell['tl'];
			
			return this._TM.replace('deliprint', d);
		}
	});


	var PayWidget = function(owner){
		PayWidget.superclass.constructor.call(this, owner, 'pay');
	};
	YAHOO.extend(PayWidget, BaseWidget, {
		init: function(owner, name){
			PayWidget.superclass.init.call(this, owner, name);
			this.paymentid = null;
		},
		renderWait: function(){
			this._TM.getEl('pay.table').innerHTML = this._TM.replace('paytable', {'rows': this._T['payrowwait']});
		},
		renderElements: function(){
			var __self = this, lst = "", TM = this._TM, T = this._T;
			
			var rows = DATA.get('payment').getRows(); 

			var current = 0;
			rows.foreach(function(row){
				var di = row.cell,
					checked = di['def']*1 > 0;
				if (checked){
					current = di['id'];
				}
				lst += TM.replace('payrow', {
					'tl': di['tl'],
					'id': di['id'],
					'checked': checked ? 'checked="checked"' : ''
				});
			});
			TM.getEl('pay.table').innerHTML = TM.replace('paytable', {'rows': lst});
			if (current > 0){
				this.setPayment(current);
			}
		},
		onClick: function(el){
			var prefix = el.id.replace(/([0-9]+$)/, '');
			var numid = el.id.replace(prefix, "");
			
			if (prefix == this._TId['payrow']['id']+'-'){
				this.setPayment(numid);
			}
			return false;
		},
		last: function(){
			this.owner.showPage('deli');
		},
		next: function(){
			this.owner.showPage('conf');
		},
		setPayment: function(paymentid){
			this.paymentid = paymentid = paymentid*1; 
			var row = DATA.get('payment').getRows().getById(paymentid);
			this.getEl('dsc').innerHTML = L.isNull(row) ? '' : row.cell['dsc'];
		},
		getData: function(){
			return {
				'paymentid': this.paymentid
			};
		},
		print: function(){
			var row = DATA.get('payment').getRows().getById(this.paymentid);
			if (L.isNull(row)){ return ''; }
			return this._TM.replace('payprint', {
				'tl': row.cell['tl'],
				'dsc': row.cell['dsc']
			});
		}
	});

	var ConfirmWidget = function(owner){
		ConfirmWidget.superclass.constructor.call(this, owner, 'conf');
	};
	YAHOO.extend(ConfirmWidget, BaseWidget, {
		initTemplate: function(over){
			ConfirmWidget.superclass.initTemplate.call(this, over);
			
			this.cart = new NS.CartWidget(true);
			return this._TM.replace('conf', {
				'cart': this.cart.initTemplate()
			});
		},
		onLoad: function(){
			ConfirmWidget.superclass.onLoad.call(this);
			this.cart.onLoad();
		},
		destroy: function(){
			ConfirmWidget.superclass.destroy.call(this);
			this.cart.destroy();
		},
		onClick: function(el){
			return false;
		},
		last: function(){
			this.owner.showPage('pay');
		},
		next: function(){
			this.owner.order();
		},
		onShow: function(){
			var own = this.owner;
			var print = 
				own.authWidget.print() +
				own.deliWidget.print() +
				own.payWidget.print();
			this._TM.getEl('conf.print').innerHTML = print;
		}
	});
	

	
	var OrderPanel = function(){
		OrderPanel.superclass.constructor.call(this, {
			width: '800px', zindex: '9999', 
			resize: true
		});
	};
	OrderPanel.instance = null;
	
	YAHOO.extend(OrderPanel, Brick.widget.Dialog, {
		initTemplate: function(){
			OrderPanel.instance = this;

			var TM = buildTemplate(this, 'panel');
			
			this._lw = null;
			
			this.authWidget = new AuthWidget(this);
			this.deliWidget = new DeliWidget(this);
			this.payWidget = new PayWidget(this);
			this.confWidget = new ConfirmWidget(this);
			
			this.widgets = {
				'auth': this.authWidget,
				'deli': this.deliWidget,
				'pay': this.payWidget,
				'conf': this.confWidget
			};
			
			return TM.replace('panel', {
				'auth': this.authWidget.initTemplate('authprint'),
				'deli': this.deliWidget.initTemplate('delitable,delitablesub,delirow,delirowwait,deliprint'),
				'pay': this.payWidget.initTemplate('paytable,payrow,payrowwait,payprint'),
				'conf': this.confWidget.initTemplate()
			});
		},
		onLoad: function(){
			var TM = this._TM;
			this._currentPage = null;
			this._accepted = false;
			
			for (var n in this.widgets){
				this.widgets[n].onLoad();
			}
			if (!this.authWidget.isRegister){
				this.showPage('auth');
			}else{
				this.showPage('deli');
				TM.getEl('panel.bk-auth').style.display = 'none';
			}
			
			var elCartSum = TM.getEl('panel.cartsum');
			elCartSum.innerHTML = '---';
			NS.API.getCartInfo(function(info){
				elCartSum.innerHTML = NS.API.formatPrice(info['sum']);
			});
			
			var tables = {
				'payment': DATA.get('payment', true),
				'delivery': DATA.get('delivery', true),
				'cart': DATA.get('cart', true)
			};
			DATA.onStart.subscribe(this.dsEvent, this, true);
			DATA.onComplete.subscribe(this.dsEvent, this, true);
			if (DATA.isFill(this.tables)){
				this.renderElements();
			}else{
				this.renderWait();
			}
		},
		dsEvent: function(type, args){
			if (
					args[0].checkWithParam('payment', {}) ||
					args[0].checkWithParam('delivery', {}) ||
					args[0].checkWithParam('cart', {})
				){
				if (type == 'onComplete'){
					this.renderElements(); 
				}else{
					this.renderWait();
				}
			}
		},
		destroy: function(){
			DATA.onComplete.unsubscribe(this.dsEvent);
			DATA.onStart.unsubscribe(this.dsEvent);

			OrderPanel.superclass.destroy.call(this);
			OrderPanel.instance = null;
			if (this.authWidget.isAuthorize){
				// в процессе оформления товара была произведена авторизация
				// убегаем на перезагрузку страницы
				new LW(document.body, true);
				Brick.Page.reload();
			}
		},
		renderElements: function(){
			for (var n in this.widgets){
				if (this.widgets[n].renderElements()){ return true;}
			}
		},
		renderWait: function(){
			for (var n in this.widgets){
				if (this.widgets[n].renderWait()){ return true;}
			}
		},
		onClick: function(el){
			var tp = this._TId['panel'];
			switch(el.id){
			case tp['blast']: this._currentPage.last(); return true;
			case tp['bnext']: this._currentPage.next(); return true;
			}
			
			for (var n in this.widgets){
				if (this.widgets[n].onClick(el)){ return true;}
			}
			
			return false;
		},
		showPage: function(page){
			if (this._currentPage == this.widgets[page]){
				return;
			}
			if (!L.isNull(this._currentPage)){
				this._currentPage.element.style.display = 'none';
			}
			this._currentPage = this.widgets[page];
			this._currentPage.onShow();
			this._currentPage.element.style.display = '';
		},
		waitStart: function(){
			this._lw = new LW(this._TM.getEl('panel.blast').parentNode, true);
		}, 
		waitStop: function(){
			if (L.isNull(this._lw)){
				return;
			}
			this._lw.hide();
			this._lw = null;
		},
		animateAccepted: function(callback){
			
			if (this._accepted){ return; }
			this._accepted = true;
			
			var TM = this._TM;
			
			// запустить установку печати
			var elImg = TM.getEl('panel.imgaccepted'),
				el = TM.getEl('panel.accepted'),
				sBG = Dom.getStyle(el, 'background'),
				x1 = 500, y1 = 0,
				step = 20;
			
			Dom.setX(elImg, x1);
			Dom.setY(elImg, y1);

			var skoef = 2,
				rg1 = Dom.getRegion(elImg),
				rg2 = Dom.getRegion(el),
				w = rg1.width, h = rg1.height, 
				w1 = w * skoef,
				h1 = h * skoef,
				
				dw = (w1-w)/step,
				dh = (h1-h)/step,
				dx = (rg2.left-x1)/step,
				dy = (rg2.top-y1)/step;
			
			Dom.setStyle(elImg, 'width', w1+'px');
			Dom.setStyle(elImg, 'height', h1+'px');
			
			var thread = setInterval(function(){
				if (step < 1){
					clearInterval(thread);
					elImg.style.display = 'none';
					Dom.addClass(el, 'accepted');
					Dom.removeClass(el, 'accepted-n');
					callback();
					return;
				}
				w1 -= dw; h1 -= dh;
				x1 += dx; y1 += dy;

				Dom.setX(elImg, x1);
				Dom.setY(elImg, y1);

				Dom.setStyle(elImg, 'width', w1+'px');
				Dom.setStyle(elImg, 'height', h1+'px');
				step--;

			}, 30);
		},
		order: function(){
			var data = {
				'do': 'orderbuild',
				'auth': this.authWidget.getData(),
				'deli': this.deliWidget.getData(),
				'pay': this.payWidget.getData()
			};
			this.animateAccepted(function(){
				Brick.ajax('eshop',{
					'data': data,
					'event': function(request){
						alert('Спасибо! Ваш заказ принят!');
						window.location.href="/";
					}
				});
			});
		}
	});
	NS.OrderPanel = OrderPanel;
	
	var OrderViewWidget = function(container, orderid){
		this.init(container, orderid);
	};
	OrderViewWidget.prototype = {
		init: function(container, orderid){
			this.orderid = orderid;
			
			var TM = buildTemplate(this, 'viewwidget,deliprint,delitldef,payprint');
			
			this.cart = new NS.CartWidget(true, orderid);
			container.innerHTML = TM.replace('viewwidget', {
				'cart': this.cart.initTemplate()
			});
			this.cart.onLoad();
			
			var tables = {
				'payment': DATA.get('payment', true),
				'delivery': DATA.get('delivery', true),
				'order': DATA.get('order', true)
			};
			tables['order'].getRows({'orderid': orderid});
			
			DATA.onStart.subscribe(this.dsEvent, this, true);
			DATA.onComplete.subscribe(this.dsEvent, this, true);
			if (DATA.isFill(this.tables)){
				this.renderElements();
			}else{
				this.renderWait();
			}
		},
		dsEvent: function(type, args){
			if (args[0].checkWithParam('order', {'orderid': this.orderid})){
				if (type == 'onComplete'){
					this.renderElements(); 
				}else{
					this.renderWait();
				}
			}
		},
		destroy: function(){
			this.cart.destroy(); 
			DATA.onComplete.unsubscribe(this.dsEvent);
			DATA.onStart.unsubscribe(this.dsEvent);
		},
		renderElements: function(){
			var TM = this._TM, T = this._T, TId = this._TId;
			var order = DATA.get('order').getRows({'orderid': this.orderid}).getByIndex(0).cell;

			var deliRow = DATA.get('delivery').getRows().getById(order['delid']);
			var deli = L.isNull(deliRow) ? null : deliRow.cell;
			
			var payRow = DATA.get('payment').getRows().getById(order['payid']);
			var pay = L.isNull(payRow) ? null : payRow.cell;

			TM.getEl('viewwidget.id').innerHTML = 
				TM.replace('deliprint', {
					'hideadr': L.isNull(deli) ? 'none' : '',
					'delitl': L.isNull(deli) ? T['delitldef'] : deli['tl'],
					'lastname': order['lnm'],
					'firstname': order['fnm'],
					'phone': order['ph'],
					'adress': order['adress'],
					'extinfo': order['extinfo']
				}) +
				TM.replace('payprint', {
					'tl': L.isNull(pay) ? '' : pay['tl'],
					'dsc': L.isNull(pay) ? '' : pay['dsc']
				}); 
		},
		renderWait: function(){
		}
	};
	NS.OrderViewWidget = OrderViewWidget;
};