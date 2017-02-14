if(App.namespace) { App.namespace('Action.Api', function(App) {

    /**
     * @namespace App.Action.Api
     * @type {*}
     */
    var _ = {};

    /**
     * @namespace App.Action.Api.request
     * @param key
     * @param callback
     * @param args
     * @param timeout_ms
     */
    _.request = function(key, callback, args, timeout_ms) {
        jQuery.ajax({
            url: App.url + '/api',
            data: {key: key, uid: App.uid, data: args},
            type: 'POST',
            timeout: timeout_ms || 36000,
            cache: false,
            headers: {requesttoken: App.requesttoken},
            success: function (response) {
                if (typeof callback === 'function') {
                    callback.call(App, response);
                }
            },
            error: function (error) {
                console.error("API request error to the key: [" + key + "] Error message: ", error);
            },
            complete: function (jqXHR, status) {
                //console.log(status, jqXHR.getAllResponseHeaders());
                if (status == 'timeout') {
                    console.error("You have exceeded the request time. possible problems with the Internet, or an error on the server");
                }
            }
        });
    };



    return _;

})}