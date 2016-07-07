if(App.namespace){App.namespace('Action.Listmenu', function(App) {

    /**
     * @namespace App.Action.Listmenu
     */
    var _ = {};

    /**
     * @namespace App.Action.Listmenu.init
     */
    _.init = function(){
        var listmenu = jQuery('.listmenu li');
        var r_messages = jQuery('#r_messages');
        var goto = Util.Cookie('goto_message');

        Util.Cookie('goto_message', false);

        listmenu.click(function(event){
            var id, menu = event.target;

            r_messages[0].style.display = 'block';
            listmenu.each(function(i,item){item.style.fontWeight='normal'});
            menu.style.fontWeight='bold';

            if(id = menu.getAttribute('data-id'))
                _.postChildrenMessage(id);
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
    _.postChildrenMessage = function(parent_id){

        var sendData = {
            parent_id: parent_id
        };

        App.Action.Api.request('message_children',function(response){
            if(Util.isObj(response) && response.requesttoken) {
                App.requesttoken = response.requesttoken;
                if(response.error){
                    return;
                }

                if(response['messageslist']) {
                    App.inject('#r_messages', response['messageslist']);
                    App.Action.Edit.submitFormReplyEvent();
                }
            }

        }, sendData);

    };


/*    _.createListItem = function(id, title, author_time, message){
        var url = App.url + '/read/' + id;
        var source = '<a href="'+url+'" class="msg_tit">'+title+'</a><div class="msg_desc">'+author_time+'</div><div>'+message.substr(0,100)+'</div>';
        return source
    };*/


    _.get = function(){

    };

    return _;
})}