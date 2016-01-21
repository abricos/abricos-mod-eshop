var Component = new Brick.Component();
Component.requires = {
    yui: ['base'],
    mod: [
        {name: 'sys', files: ['application.js', 'widget.js']}
    ]
};
Component.entryPoint = function(NS){

    var COMPONENT = this,
        SYS = Brick.mod.sys;

    NS.roles = new Brick.AppRoles('{C#MODNAME}', {
        isAdmin: 50,
        isModerator: 45,
        isOperator: 40,
        isWrite: 30,
        isView: 10
    });

    SYS.Application.build(COMPONENT, {}, {
        initializer: function(){
            NS.roles.load(function(){
                this.initCallbackFire();
            }, this);
        },
    }, [], {
        REQS: {},
        ATTRS: {
            isLoadAppStructure: {value: false},
        },
        URLS: {
            ws: "#app={C#MODNAMEURI}/wspace/ws/",
            catalogman: {
                view: function(){
                    return this.getURL('ws') + 'manager/ManagerWidget/'
                }
            },
            catalogman: function(catid){
                var link = this.getURL('ws') + 'catalog/CatalogManagerWidget/';
                if (catid && catid > 0){
                    link += catid + '/';
                }
                return link;
            },
            billing: function(){
                return this.getURL('ws') + 'billing/BillingWidget/';
            },
            catalogconfig: function(){
                return this.getURL('ws') + 'catalogconfig/CatalogConfigWidget/';
            },
            cartbilling: function(){
                return this.getURL('ws') + 'eshopcart/CartBillingWidget/';
            },
            cartconfig: function(){
                return this.getURL('ws') + 'eshopcart/CartConfigWidget/';
            },
        }
    });
};