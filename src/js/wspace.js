var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: '{C#MODNAME}', files: ['lib.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        COMPONENT = this,
        SYS = Brick.mod.sys;

    NS.WorkspaceWidget = Y.Base.create('workspaceWidget', SYS.AppWidget, [
        SYS.AppWorkspace
    ], {
        onInitAppWorkspace: function(err, appInstance){
            var tp = this.template;

            if (Brick.componentExists('eshopcart', 'lib')){
                tp.show('mcartconfig');
            }
        },
        onShowWorkspacePage: function(page, widget){
            var tp = this.template;
            tp.removeClass('mcatalogman,mcartbilling,mcatalogconfig,mcartconfig', 'active');

            if (page.component === 'catalog' && page.widget === 'CatalogManagerWidget'){
                tp.addClass('mcatalogman', 'active');
            } else if (page.component === 'eshopcart' && page.widget === 'CartBillingWidget'){
                tp.addClass('mcartbilling', 'active');
            } else if (page.component === 'eshopcart' && page.widget === 'CartConfigWidget'){
                tp.addClass('mcartconfig', 'active');
            } else if (page.component === 'catalogconfig' && page.widget === 'CatalogConfigWidget'){
                tp.addClass('mcatalogconfig', 'active');
            }
        }
    }, {
        ATTRS: {
            component: {value: COMPONENT},
            templateBlockName: {value: 'widget'},
            defaultPage: {
                value: {
                    component: 'catalog',
                    widget: 'CatalogManagerWidget'
                }
            }
        }
    });

    NS.ws = SYS.AppWorkspace.build('{C#MODNAME}', NS.WorkspaceWidget);
};