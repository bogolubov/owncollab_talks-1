if(App.namespace){App.namespace('Action.Edit', function(App){
    /**
     * @namespace App.Action.Edit
     */
    var _ = {};

    /**
     * @namespace App.Action.Edit.init
     */
    _.init = function(){

        jQuery("textarea[name=message]").trumbowyg();

        _.checkSubscribersEvent();
        _.submitFormEvent();
    };

    _.submitFormEvent = function(){
        jQuery('form#begin-talk').submit(function(event){
            event.preventDefault();

            var vals = Util.formData(this, true);
            if(Util.isObj(vals) && vals['title'].length > 2 && vals['message'].length > 2 &&
                ( vals['nogroup[]'] || vals['nogroup_users[]'] || vals['groups[]'] || vals['users[]'] )) {
                this.submit();
            }else{
                App.Controller.Page.errorLine("Не все поля заполненны!");
            }
        });
    };

    /**
     *
     */
    _.submitFormReplyEvent = function(){
        jQuery('form#quick-reply').submit(function(event){
            var vals = Util.formData(this, true);

            App.Controller.Page.errorLineClose();
            event.preventDefault();

            jQuery('input[type=submit]', this).prop( "disabled", true );
            jQuery('textarea[name=message]', this).prop( "disabled", true );

            if(!Util.isEmpty(vals['hash']) && !Util.isEmpty(vals['message'])) {

                App.Action.Api.request('save_reply', function(response) {
                    if(!Util.isObj(response) || response['error'] ) {
                        App.Controller.Page.errorLine(response['errorinfo']?response['errorinfo']:"Server internal error");
                    }
                    else if(response['insert_id'] && response['parent_id']) {
                        jQuery("ul.listmenu>li[data-id="+response['parent_id']+"]").click();
                    }
                }, vals);
            }
            else {
                jQuery('input[type=submit]', this).prop( "disabled", false );
                jQuery('textarea[name=message]', this).prop( "disabled", false );
                App.Controller.Page.errorLine('Пустое сообщение не может быть отправленно, введите текст.');
            }
        });
    };



    /**
     * @namespace App.Action.Edit.checkSubscribersEvent
     */
    _.checkSubscribersEvent = function(){

        jQuery('.talk-subscribers input[type=checkbox]').click(function(event){

            var target = event.target;
            var name = target.name;
            var value = target.value;
            var email = target.getAttribute('data-email');
            var isChecked = event.target.checked;
            var isUser = !!target.getAttribute('data-group');

            if(isUser && email.length < 5 && isChecked){
                App.Controller.Page.errorLine("Этому пользователю письо не будет доставленно. В параметрах пользователя '"+value+"' не указан email адрес, или указан не верный email");
            }

            if(!isUser) {
                jQuery('.talk-subscribers input[type=checkbox][data-group='+value+']').each(function(index, item){
                    item.checked = isChecked;
                });
            }

        });
    };

    return _;
})}
