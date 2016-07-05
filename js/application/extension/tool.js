App.namespace('Tool', function(App, mod){

    /**
     * @namespace App.Tool
     * @type {*}
     */
    var _ = {};

    /**
     * @namespace App.Tool.line
     */
    _.line = function(){};

    /**
     * @namespace App.Tool.page
     */
    _.page = function(){};


    App.node = function(key, value){
        if(typeof key === 'string' && value === undefined) {
            return App.store('node.' + key);
        } else
            return App.store('node.' + key, value);
    };

    /**
     *
     * @returns {string}
     */
    App.uriPath = function(){
        var _app_name_pos = location.pathname.lastIndexOf(App.name);
        var path = location.pathname.substr(_app_name_pos + App.name.length);
        return (path.slice(0,1) == '/') ? path : '/' + path;
    };

    /**
     *
     * @returns {string}
     */
    App.template = function(urlView, data, callback){
        callback = typeof callback === 'function' ? callback : function(data) {

        };
        jQuery.get( urlView, callback);
    };


    return _;
});
