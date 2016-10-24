var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: 'catalog', files: ['lib.js']}
    ]
};
Component.entryPoint = function(NS){

    var L = YAHOO.lang,
        R = NS.roles;

    var SYS = Brick.mod.sys,
        LNG = this.language,
        CAT = Brick.mod.catalog;

    this.buildTemplate({}, '');

    var CatalogItem = function(manager, d){
        CatalogItem.superclass.constructor.call(this, manager, d);
    };
    YAHOO.extend(CatalogItem, CAT.CatalogItem, {
        update: function(d){
            this._urlCache = null;
            CatalogItem.superclass.update.call(this, d);
        },
        url: function(){
            if (!L.isNull(this._urlCache)){
                return this._urlCache;
            }
            var url = "/eshop/", pline = this.getPathLine();
            for (var i = 1; i < pline.length; i++){
                url += pline[i].name + '/';
            }

            this._urlCache = url;
            return url;
        }
    });
    NS.CatalogItem = CatalogItem;

    var Element = function(manager, d){
        Element.superclass.constructor.call(this, manager, d);
    };
    YAHOO.extend(Element, CAT.Element, {
        update: function(d){
            this._urlCache = null;
            Element.superclass.update.call(this, d);
        },
        url: function(){
            if (!L.isNull(this._urlCache)){
                return this._urlCache;
            }

            var cat = this.manager.catalogList.find(this.catid);

            this._urlCache = cat.url() + 'product_' + this.id;

            return this._urlCache;
        }
    });
    NS.Element = Element;

    NS.manager = null;

    NS.initManager = function(callback){
        CAT.initManager('{C#MODNAME}', callback, {
            'roles': R,
            'CatalogItemClass': NS.CatalogItem,
            'ElementClass': NS.Element,
            'language': LNG
        });
    };
};