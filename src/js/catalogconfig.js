/*
 @package Abricos
 @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

var Component = new Brick.Component();
Component.requires = {
    yui: ['aui-tabview'],
    mod: [
        {name: 'catalog', files: ['typemanager.js']},
        {name: '{C#MODNAME}', files: ['lib-manager.js']}
    ]
};
Component.entryPoint = function(NS){

    var L = YAHOO.lang,
        buildTemplate = this.buildTemplate;

    var NSCat = Brick.mod.catalog;

    var CatalogConfigWidget = function(container, cfg){
        CatalogConfigWidget.superclass.constructor.call(this, container, {
            'buildTemplate': buildTemplate, 'tnames': 'widget'
        }, cfg);
    };
    YAHOO.extend(CatalogConfigWidget, Brick.mod.widget.Widget, {
        init: function(cfg){
            this.wsMenuItem = 'config'; // использует wspace.js
            this.manager = null;
            this.cfg = cfg;
            this.viewWidget = null;
        },
        destroy: function(){
            if (!L.isNull(this.viewWidget)){
                this.viewWidget.destroy();
            }
            CatalogConfigWidget.superclass.destroy.call(this);
        },
        onLoad: function(cfg){
            var __self = this;
            NS.initManager(function(man){
                __self._onLoadManager(man);
            });
        },
        _onLoadManager: function(man){
            this.manager = man;
            this.elHide('loading');
            this.elShow('view');

            new Y.TabView({srcNode: this.gel('view')}).render();
            this.viewWidget = new NSCat.TypeManagerWidget(this.gel('typemanager'), man);
        }
    });
    NS.CatalogConfigWidget = CatalogConfigWidget;

};