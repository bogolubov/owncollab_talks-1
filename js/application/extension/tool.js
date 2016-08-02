App.namespace('Tool', function(App, mod){

    /**
     * @namespace App.Tool
     * @type {*}
     */
    var _ = {};

    /**
     * @namespace App.node
     */
    App.node = function(key, value){
        if(typeof key === 'string' && value === undefined) {
            return App.store('node.' + key);
        } else
            return App.store('node.' + key, value);
    };

    /**
     * @namespace App.uriPath
     * @returns {string}
     */
    App.uriPath = function(){
        var _app_name_pos = location.pathname.lastIndexOf(App.name);
        var path = location.pathname.substr(_app_name_pos + App.name.length);
        return (path.slice(0,1) == '/') ? path : '/' + path;
    };

    /**
     * @namespace App.template
     * @returns {string}
     */
    App.template = function(urlView, data, callback){
        callback = typeof callback === 'function' ? callback : function(data) {

        };
        jQuery.get( urlView, callback);
    };



    /**
     * @namespace App.Tool.getDateDuration
     * @param startDate
     * @returns {{hour: number, minute: number, second: number}}
     */
    _.getDateDuration = function(startDate){
        var currDate = new Date();
        var duration = new Date(currDate - startDate);
        return {
            days: duration.getDayOfYear(),
            hour: duration.getHours(),
            minute: duration.getMinutes(),
            second: duration.getSeconds()
        };
    };

    return _;
});
