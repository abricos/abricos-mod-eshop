/*
* @version $Id$
* @copyright Copyright (C) 2008 Abricos All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	mod:[
	     {name: 'sys', files: ['wait.js']},
	     {name: 'sitemap', files: ['paginator.js']}
	]
};
Component.entryPoint = function(NS){
	
	var Dom = YAHOO.util.Dom,
		E = YAHOO.util.Event,
		L = YAHOO.lang;
	
	var API = NS.API;
	
	var LW = Brick.widget.LayWait;

	API.initProductListPaginator = function(){
		return new PageListManager();
	};
	
	var PageListManager = function(){
		this.init();
	};
	PageListManager.prototype = {
		init: function(){
			var pgs = new Brick.mod.sitemap.API.initPaginatorByClassName('paginator');

			if (pgs.length < 1){ return; }
			var pg = pgs[0];
			pg.onPageChange.subscribe(this.onPageChange, this, true);
			
			this.paginator = pg;
			this.page = pg.page*1;
			this.element = Dom.get('product-list-wpage');
			
			this.pageCount = 1;
			this.pages = {};
			var el = this.pages[this.page] = Dom.get('product-list-page-'+pg.page);
			
			this.region = Dom.getRegion(el);

			this.loaderPageTemplate = Dom.get('pr_loader_template').innerHTML;
		},
		onPageChange: function(type, args){
			
			var pg = this.paginator, 
				page = args[0]*1, 
				lastPage = (args[1] || pg.page)*1;
			
			if (this.pages[page]){
				this.showPage(page);
				return;
			}

			var __self = this;

			this.setPageSource(
				page, 
				Brick.util.Template.setPropertyArray(this.loaderPageTemplate, {'numpage': page}), 
				function(pPage){
					Brick.ajax('eshop',{
						'type': 'html',
						'data': {
							'do': 'brick-productlist',
							'page': page,
							'uri': window.location.href 
						},
						'event': function(response){
							__self.setPageSource(page, response.responseText);
						}
					});
				}
			);
		},
		setPageSource: function(page, html, callback){
			page = page*1;
			var el = Dom.get('product-list-page-'+page);

			if (!L.isNull(el)){
				el.parentNode.removeChild(el);
				delete this.pages[nn];
			}else{
				this.pageCount++;
			}
			var pCount = this.pageCount;
			
			var w = this.region.width;
			Dom.setStyle(this.element, 'width', (w * pCount+10)+'px');
			
			var div = document.createElement('div');
			div.innerHTML = html;
			var elPage = div.childNodes[0];
			div.removeChild(elPage);
			
			var apnd = false;
			var sarr = [];
			sarr[sarr.length] = page;
			
			for (var nn in this.pages){
				var cel = this.pages[nn];
				
				sarr[sarr.length] = nn*1;
				if (page < nn && !apnd){
					Dom.insertBefore(elPage, cel);
					apnd = true;
				}
			}
			if (!apnd){
				this.element.appendChild(elPage);
			}
			this.pages[page] = elPage;
			var sarr = sarr.sort(function(a,b){return a - b; });
			var npages = {};
			for (var i=0;i<sarr.length;i++){
				var np = sarr[i];
				npages[np] = this.pages[np];
				
				var cel = npages[np];
				cel.style.display = '';
				Dom.setStyle(cel, 'float', 'left');
				Dom.setStyle(cel, 'width', w+'px');
			}
			this.pages = npages;
			
			var cc = this._getLeftCountPage(this.page);
			Dom.setStyle(this.element, 'left', (-w*cc)+'px');
			
			this.showPage(page, callback);
		},
		_getLeftCountPage: function(page){
			page = page*1;
			var dc = 0;
			for (var nn in this.pages){
				if (nn*1 >= page){
					return dc;
				}
				dc++;
			}
		},
		showPage: function(page, callback){
			if (page == this.page){ 
				return; 
			}
			var w = this.region.width,
				xc = (w*this._getLeftCountPage(this.page)),
				xn = (w*this._getLeftCountPage(page)),
				dx = (xc-xn)/25,
				x = xc, 
				el = this.element;
			
			var __self = this;
			var stop = function(){
				Dom.setStyle(el, 'left', (-xn)+'px');
				__self.page = page;
				__self.paginator.setPage(page);
				if (L.isFunction(callback)){
					callback(page);
				}
			};
			
			var thread = setInterval(function(){
				if (
						(xc > xn && xn > x) || // движение вперед 
						(xc < xn && xn < x) // движение назад
					){
					clearInterval(thread);
					stop();
					return;
				}
				x -= dx;
				Dom.setStyle(el, 'left', (-x)+'px');
			}, 10);
		}
	};
};
