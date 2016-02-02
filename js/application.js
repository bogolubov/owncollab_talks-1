

var app = app || {

        uid: null,
        pid: null,
        name: 'owncollab_talks',
		url: OC.generateUrl('/apps/owncollab_talks'),

		/*dependent controllers*/
		controller: {
			main: {},
			settings: {}
		},

		/*dependent modules*/
		module: {
			db: {},
			cookie: {}
		},

		/*dependent actions*/
		action: {
            lightbox: {},
            sidebar: {}
		},

        /*options*/
        option: {},

        /*db date*/
        data: {}
	};

(function ($, OC, app) {

	var inc = new Inc(),
        path = '/apps/'+app.name,
        hash = window.location.hash.slice(1);

	inc.require(path+'/js/app/controller/main.js');
	inc.require(path+'/js/app/controller/settings.js');

	inc.require(path+'/js/app/action/sidebar.js');
	inc.require(path+'/js/app/action/lightbox.js');

	inc.require(path+'/js/app/module/db.js');

	inc.onerror = onError;
	inc.onload = onLoaded;

	inc.init();

	function onError(error) {
		console.error('Error on loading script', error);
	}

	function onLoaded() {
		console.log('application loaded...');
        /**
         * Set application options
         */
        app.uid = OC.currentUser;

        if(hash.length > 0)
		    app.controller.main.construct();
	}

	/*app methods*/

    app.api = function (key, func, args){
        $.ajax({
            url: app.url + '/api',
            data: {key:key, uid:app.uid, pid:app.pid, data:args},
            type: 'POST',
            success: function(response){
                if(typeof func === 'function')
                    func.call(app, response);
            }
        });
    };

})(jQuery, OC, app);