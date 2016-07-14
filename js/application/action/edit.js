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


        App.style('/apps/owncollab_talks/css/uploadfile.css');

        App.require('jq_uploadfile', [
            App.urlScript + 'libs/jquery.form.js',
            App.urlScript + 'libs/jquery.uploadfile.js'
        ], function(){
            _.submitFileUploadEvent();
            _.attachUserFileList();
        }).requireStart('jq_uploadfile');

    };

    _.submitFormEvent = function(){

        jQuery('form#begin-talk').submit(function(event){

            event.preventDefault();

            var form = this;
            var formValues = Util.formData(form, true);
            var shareElements = App.query('#share_list_elements');

            // clear share elements
            shareElements.textContent = '';

            if(Util.isObj(formValues) && formValues['title'].length > 2 && formValues['message'].length > 2 &&
                ( formValues['nogroup[]'] || formValues['nogroup_users[]'] || formValues['groups[]'] || formValues['users[]'] )) {

                // added files for sharing in server side
                for (var key in App.Action.File.selectFilesData) {
                    var hideInput = Util.createElement('input', {
                        type:'text',
                        name:'share['+key+']',
                        value: JSON.stringify(App.Action.File.selectFilesData[key]),
                        hidden:'hidden'
                    });
                    shareElements.appendChild(hideInput);
                }

                //console.log(Util.formData(form, true));
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
        jQuery('form#quick-reply').submit(function(event) {
            var formValues = Util.formData(this, true);
            App.Controller.Page.errorLineClose();
            event.preventDefault();

            jQuery('input[type=submit]', this).prop( "disabled", true );
            jQuery('textarea[name=message]', this).prop( "disabled", true );

            if(!Util.isEmpty(formValues['hash']) && !Util.isEmpty(formValues['message'])) {

                App.Action.Api.request('save_reply', function(response) {
                    if(!Util.isObj(response) || response['error'] ) {
                        App.Controller.Page.errorLine(response['errorinfo']?response['errorinfo']:"Server internal error");
                    }
                    else if(response['insert_id'] && response['parent_id']) {
                        // from read page
                        if(App.uriPath.search(/\/read\/\d+/i)!==-1) {
                            var rid = App.query('input[name=rid]').value;
                            Util.Cookie.set('goto_message', rid, {path:'/'});
                            window.history.back();
                            // from listmenu
                        }else
                            jQuery("ul.listmenu>li[data-id="+response['parent_id']+"]").click();
                    }
                }, formValues);
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
    _._last_uploads_result = [];

    /**
     * @namespace App.Action.Edit.submitFileUploadEvent
     * Uses jquery plugin - http://hayageek.com/docs/jquery-upload-file.php
     */
    _.submitFileUploadEvent = function () {
        var
            i = 0,
            fileList,
            uploadConfig = {
                url: App.url + "/api/uploadfirst",
                fileName:"file"
            };

        uploadConfig.onSelect = function (files) {fileList = files};
        uploadConfig.onSuccess = function (files, response, xhr, pd) {
            if(typeof fileList === 'object' && fileList.length > 0) {
                // all files upload
                for(i = 0; i < fileList.length; i ++) {
                    App.Action.File.uploadFile(fileList[i], function (response) {
                        console.log('File upload complete! Response:', response);
                        _._last_uploads_result[i] = response;

                        //http://owncloud.loc/index.php/apps/files/ajax/getstoragestats.php
                    });
                }
            }
        };
        jQuery("#uploadfile_plugin").uploadFile(uploadConfig);
    };

    /**
     * @namespace App.Action.Edit.attachUserFileList
     * Uses jquery plugin - http://hayageek.com/docs/jquery-upload-file.php
     */
    _.attachUserFileList = function () {

        var loadIcon = Util.createElement('div', {'class':'loader_files'}, '<div class="loader_min"></div>');

        jQuery("#attach_files_btn").click(function(){

            App.inject("#attach_files", loadIcon);

            App.Action.Api.request('getuserfiles', function (response) {

                console.log('getuserfiles:', response);

                if (response.requesttoken) {
                    App.requesttoken = response.requesttoken;

                    App.inject("#attach_files", response.view);
                    jQuery('#attach_files').css('border', '1px solid #ddd');

                    App.Action.File.fileListSourceData = response['file_list'] ? response['file_list'] : [];
                    App.Action.File.selectShareFiles("#attach_files");
                }
            });
        });

    };

    return _;
})}
