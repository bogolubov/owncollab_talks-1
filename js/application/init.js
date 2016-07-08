

var App = new NamespaceApplication({

    debug: true,
    constructsType: false,
    name: 'owncollab_talks',
    url: OC.generateUrl('/apps/owncollab_talks'),
    urlBase: OC.getProtocol() + '://' + OC.getHost(),
    getUrl: function(link){
        link = link ? '/' + link : '';
        return OC.getProtocol() + '://' + OC.getHost() + OC.generateUrl('/apps/owncollab_talks') + link;
    },
    urlScript: '/apps/owncollab_talks/js/',
    host: OC.getHost(),
    locale: OC.getLocale(),
    protocol: OC.getProtocol(),
    isAdmin: null,
    corpotoken: null,
    requesttoken: oc_requesttoken ? encodeURIComponent(oc_requesttoken) : null,
    uid: oc_current_user ? encodeURIComponent(oc_current_user) : null
});




App.require('libs', [
    App.urlScript + 'libs/util.js',
    App.urlScript + 'libs/timer.js',
    App.urlScript + 'libs/linker.js'
], initLibrary, initError);


App.require('dependence', [
    App.urlScript + 'application/extension/tool.js',
    App.urlScript + 'application/action/api.js',
    App.urlScript + 'application/action/edit.js',
    App.urlScript + 'application/action/files.js',
    App.urlScript + 'application/action/listmenu.js',
    App.urlScript + 'application/controller/page.js'

], initDependence, initError);



App.requireStart('libs');



function initError(error){
    console.error('initError' , error);
}



function initLibrary(list){
    App.requireStart('dependence');
}


// start
function initDependence(list){
    console.log('Application start!');

    App.Controller.Page.construct();
}


