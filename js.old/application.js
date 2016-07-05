
var app = app || {

        /*application name and folder*/
        name: 'owncollab_talks',

        /*url address to current application*/
        url: OC.generateUrl('/apps/owncollab_talks'),

        /*user is admin*/
        isAdmin: null,

        /*public oc_requesttoken*/
        requesttoken: oc_requesttoken ? encodeURIComponent(oc_requesttoken) : null,

        /*current user*/
        uid: oc_current_user ? encodeURIComponent(oc_current_user) : null,

        /*current project*/
        pid: null,

        /*DOM Elements*/
        dom: {},

        /*dependent controllers*/
        controller: {
            main: {}
        },

        /*dependent modules*/
        module: {
            util: {}
        },

        /*dependent actions*/
        action: {
            buttons: {},
            error: {}
        },

        /*db project data*/
        data: {
            access:null,
            errorinfo:null,
            uid:null,
        },

        /*alias for app.module.util object*/
        u:{},

    };

(function ($, OC, app) {

    var inc = new Inc(),
        path = '/apps/' + app.name;

    inc.require(path+'/js/app/controller/main.js');
    inc.require(path+'/js/app/action/error.js');
    inc.require(path+'/js/app/module/util.js');
    inc.require(path+'/js/app/action/buttons.js');
    inc.onerror = onError;
    inc.onload = onLoaded;
    inc.init();

    /**
     * Executed if any errors occur while loading scripts
     *
     * @param error
     */
    function onError(error) {
        console.error('Error on loading script. Message: ' + error);
        app.action.error.page('Error on loading script');
    }

    /**
     * Running when all scripts loaded is successfully
     */
    function onLoaded() {
        
        console.log('application loaded...');

        /**
         * Set application options
         */
        app.uid = OC.currentUser == app.uid ? app.uid : null;


        /**
         * Start controller handler
         */
        app.controller.main.construct();
        app.action.buttons.construct();
    }

    /*app methods*/

    /**
     * The method requests to the server. The application should use this method for asynchronous requests
     *
     * @param key  Its execute method on server
     * @param func After request, a run function
     * @param args Arguments to key method
     */
    app.api = function (key, func, args){
        $.ajax({
            url: app.url + '/api',
            data: {key:key, uid:app.uid, pid:app.pid, data:args},
            type: 'POST',
            timeout: 5000,
            headers: {requesttoken: app.requesttoken},
            success: function(response){
                if(typeof func === 'function')
                    func.call(app, response);
            },
            error: function(error){
                console.log("API request error to the key: [" + key + "] Error message: ", error);
                app.action.error.inline("API request error to the key: [" + key + "] Error message: " + error);
            },
            complete: function (jqXHR, status) {
                if(status == 'timeout'){
                    app.action.error.inline("You have exceeded the request time. possible problems with the Internet, or an error on the server server");
                }
            }
        });
    };

    app.storageSetItem = function(name, value){
        return window.localStorage.setItem(name, value);
    };
    app.storageGetItem = function(name, orValue){
        var value = window.localStorage.getItem(name);
        return (value === undefined) ? orValue : value;
    };
    app.storageRemoveItem = function(name){
        return window.localStorage.removeItem(name);
    };
    app.storageKey = function(key){
        return window.localStorage.key(key);
    };

})(jQuery, OC, app);