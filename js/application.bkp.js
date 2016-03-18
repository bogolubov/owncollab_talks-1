

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

        app.controller.main.construct();

		/* app.api('met', function(res){

		}, {id:app.uid}) */


		var buttons = document.querySelector('.message-buttons');

		console.log(buttons);

		buttons.addEventListener('click', onClickButtons, true);
		function onClickButtons(event){
			var button = event.target;
			var action = button.getAttribute('data-link');

			switch action {
				case
			}

		}


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

//Open a message when its title is clicked
$(function(){
	var messid = null;
	$(".messagerow").click(
		function(){
			messid = $(this).find('#messageid').attr('value');
			window.location = '/index.php/apps/owncollab_talks/read/'+messid;
		}
	);
});

//Reply to a message when Reply butoon is clicked
$(function(){
	$("#reply").click(
		function(){
			var messid = $("#messageId").attr('value');
			window.location = '/index.php/apps/owncollab_talks/reply/'+messid;
		}
	);
});

//Remove my from the conversation
$(function(){
	$("#delete-confirm").click(
		function () {
			if (confirm("Are You sure You don't want to take part in this Talk any more?")) {
				var talkid = $("#messageId").attr('value');
				var userid = $("#userId").attr('value');
				window.location = '/index.php/apps/owncollab_talks/removeuser/' + talkid + '/' + userid;
			}
		}
	);
});

//Mark message as read
$(function(){



	$("#mark-talk-as").click(
		function(eve){
			var action = $(this).attr('value');
			var messid = $("#messageId").attr('value');
			console.log(this);
			console.log(this.value);
			//alert(messid + ' ' + action);
			window.location = '/index.php/apps/owncollab_talks/mark/'+messid+'/'+action;
		}, true
	);
});
//Mark message as unread
$(function(){
	$("#markAsUnread").click(
		function(){
			var messid = $("#messageId").attr('value');
			window.location = '/index.php/apps/owncollab_talks/mark/'+messid+'/unread';
		}
	);
});
//Mark talk as finished
$(function(){
	$("#markAsFinished").click(
		function(){
			var messid = $("#messageId").attr('value');
			window.location = '/index.php/apps/owncollab_talks/mark/'+messid+'/unread';
		}
	);
});

//Select all users in group
$(function(){
	var checked = null;
	$("input.groupname").click(
		function(){
			var fieldset = $(this).parent().parent();
			if ($(this).is(':checked')) {
				fieldset.find('.group-user input:checkbox').attr('checked', true);
			}
			else {
				fieldset.parent().find('.group-user input:checkbox').attr('checked', false);
			};
		}
	);
});

//Select the same user in different groups
$(function(){
	$(".group-user input").click(
		function(){
			var uid = $(this)[0].value;
			var allusers = $(this).parents('fieldset').parent();
			//alert(uid);
			if ($(this).is(':checked')) {
				allusers.find('#'+uid).attr('checked', true);
			}
			else {
				allusers.find('#'+uid).attr('checked', false);
			};
		}
	);
});

//Show the Mark as menu
$(function(){
	$(".mark-talk-as").hover(
		function(){
			$(this).find('#mark-talk-as').fadeIn("slow");
		},
		function(){
			$(this).find('#mark-talk-as').fadeOut("fast");
		}
	);
});

