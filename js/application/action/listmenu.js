if(App.namespace){App.namespace('Action.Listmenu', function(App) {

    /**
     * @namespace App.Action.Listmenu
     */
    var _ = {
        currentParentId: null,
        timerPeriod: 5000,
        timerUpdate: null
    };

    /**
     * @namespace App.Action.Listmenu.init
     */
    _.init = function () {
        var listmenu = jQuery('.listmenu li');
        var r_messages = jQuery('#r_messages');
        var goto = Util.Cookie('goto_message');

        Util.Cookie('goto_message', false);

        listmenu.click(function(event) {

            var id, menu = event.target;

            r_messages[0].style.display = 'block';
            listmenu.each(function(i,item){item.style.fontWeight='normal'});
            menu.style.fontWeight='bold';

            if(id = menu.getAttribute('data-id')) {

                // Get messages from server side
                _.postChildrenMessage(id);

                // Auto Update messages
                if(!_.timerUpdate)
                    _.autoUpdateMessages(id);
            }
        });

        if(Util.isNum(goto))
            jQuery("ul.listmenu>li[data-id="+goto+"]").click();
        else
            jQuery(listmenu[0]).click();
    };

    /**
     * @namespace App.Action.Listmenu.getChildrenMessage
     * @param parent_id
     */
    _.postChildrenMessage = function(parent_id) {
        var sendData = {
            parent_id: parent_id
        };

        // show loader ico
        App.query('.loader_min').style.display = 'block';

        App.Action.Api.request('message_children',function(response) {
            if(Util.isObj(response) && response.requesttoken) {

                App.requesttoken = response.requesttoken;
                if(response.error){
                    return;
                }

                if(response['messageslist']) {

                    App.inject('#r_messages', response['messageslist']);
                    App.Action.Edit.submitFormReplyEvent();

                    // hide loader ico
                    App.query('.loader_min').style.display = 'none';
                }
            }

        }, sendData);

    };


    _.autoUpdateMessages = function (id) {
        if (_.timerUpdate)
            _.timerUpdate.abort();

        _.timerUpdate = new Timer(parseInt(_.timerPeriod), 0);

        _.timerUpdate.addEventListener(Timer.PROGRESS, function (progress) {
            jQuery("ul.listmenu>li[data-id=" + id + "]").click();
        });

        if (id)
            _.timerUpdate.start();
    };


    _.get = function(){

    };

    return _;
})}