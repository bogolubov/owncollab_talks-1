/**
 * Created by olexiy on 19.02.16.
 */

(function($, OC, app){

    // using depending on the base application
    var o = app.action.buttons;

    o.construct = function() {
        /**
         * First we need to select all the elements necessary for work.
         * But after the DOM is loaded
         */
        var path = window.location.pathname.split( '/' );
        var lastItem = path[4];

        switch (lastItem) {
            case 'begin':
            case 'reply':
                $(document).ready(fileUploadInit);
                $(document).ready(editorInit);
                $(document).ready(linksInit);
                $(document).ready(checkboxInit);
                break;
            case 'mytalks':
                $(document).ready(linksInit);
                break;
            default:
                $(document).ready(menuInit);
                $(document).ready(linksInit);
                $(document).ready(checkboxInit);
                $(document).ready(buttonsInit);
                break;
        }
    }

    function fileUploadInit() {
        document.getElementById("uploadBtn").onchange = function () {
            document.getElementById("uploadFile").value = this.value;
            var file = document.getElementById('uploadBtn').files[0];
            $('#uploadimg').show();
            uploadFile(file, function(response){
                try {
                    var r = JSON.parse(response);
                    var file = r[0];
                    $(".uploadedfiles ul").append('<li><div class="thumbnail" style="background-image: url('+file.icon+')"></div><div class="name">'+file.name+'</div><div class="size">'+sizeRoundedString(file.size)+'</div><div class="clear"></div><input type="hidden" name="upload-files[]" value="'+file.id+'"></li>');

                }catch (e){}
            });
            $('#uploadimg').hide();
        };

        $(".fileUpload").hover(
            function(){
                var fileUploadSpan = document.getElementById("fileUploadSpan");
                fileUploadSpan.className = "hovered";
            },
            function(){
                var fileUploadSpan = document.getElementById("fileUploadSpan");
                fileUploadSpan.className = "";
            }
        );
    }

    function menuInit() {
        //alert("menu");
        $(".mark-talk-as").hover(
            function(){
                $(this).find('#mark-talk-as').fadeIn("slow");
            },
            function(){
                $(this).find('#mark-talk-as').fadeOut("fast");
            }
        );
    }

    function linksInit() {
        $("li.title").click(
            function(event) {
                var activerow = $('.messagelist .activetalk')[0];
                activerow.className = 'title';
                this.className = "activetalk";

                var talkid = $(this).find('#messageid').attr('value');

                app.api('getTalk', function (response) {
                    if (response.requesttoken) {
                        app.requesttoken = response.requesttoken;

                        $("#talk-body").html("");
                        $("#talk-body").append(response.view);
                    }
                }, talkid);

                app.api('getTalkFiles', function (response) {
                    console.log(response);
                    if (response.requesttoken) {
                        app.requesttoken = response.requesttoken;

                        $("#talk-files").html("");
                        $("#talk-files").append(response.view);
                    }
                }, talkid);
            }
        );

        $("body").on('submit', 'form#newanswer',
            function(event) {
                event.preventDefault();
                var text = $(this).find("input[name=answertext]").val();
                var talkid = $(this).find("input[name=messageid]").val();

                app.api('answerTalk', function (response) {
                    if (response.requesttoken) {
                        app.requesttoken = response.requesttoken;

                        $("#talk-answers").append(response.view);
                    }
                }, {'talkid' : talkid, 'text' : text});
            }
        );

        var filesresult = 0;
        $("#ajax-showfiles").click(
            function(event) {
                event.preventDefault();

                if (!filesresult) {
                    $('#loadimg').show();
                    app.api('getuserfiles', function (response) {
                        if (response.requesttoken) {
                            app.requesttoken = response.requesttoken;

                            $("#attach-files").append(response.view);
                        }
                    });
                    filesresult = 1;
                    $('#loadimg').hide();
                }
                else {
                    $("#attach-files").html("");
                    filesresult = 0;
                }
            }
        );

        var folderresult = 0;
        'use strict';
        $(document.body).on('click', ".ajax-openfolder", function(event) {
            event.preventDefault();
            var cell = $(this).parents('table').find("#folder-files-"+this.id.substring(7));

            if (!folderresult) {
                app.api('getfolderfiles', function (response) {
                    if (response.requesttoken) {
                        app.requesttoken = response.requesttoken;

                        cell.append(response.view);
                    }
                    console.log(response);
                }, this.id);
                folderresult = 1;
            }
            else {
                cell.html("");
                folderresult = 0;
            }
        })
     }

    function checkboxInit() {
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
    }

    function buttonsInit() {
        //alert("buttons");
        var buttons = document.querySelector('.message-buttons');

        console.log(buttons);
        if(buttons)
            buttons.addEventListener('click', onClickButtons, true);
        return;
    }

    function editorInit() {
        $("#message-body").trumbowyg();
    }

    function onClickButtons(event) {
        var button = event.target;
        var action = button.getAttribute('data-link');

        switch (action) {
            case 'reply':
                var messid = $("#messageId").attr('value');
                window.location = '/index.php/apps/owncollab_talks/reply/'+messid;
                break;
            case 'no-reply':
                break;
            case 'delete-confirm':
                if (confirm("Are You sure You don't want to take part in this Talk any more?")) {
                    var talkid = $("#messageId").attr('value');
                    var userid = $("#userId").attr('value');
                    window.location = '/index.php/apps/owncollab_talks/removeuser/' + talkid + '/' + userid;
                }
                break;
            case 'mark':
                document.getElementById('mark-talk-as').fadeIn("slow");
                break;
            case 'read':
            case 'unread':
            case 'finished':
                var messid = $("#messageId").attr('value');
                window.location = '/index.php/apps/owncollab_talks/mark/'+messid+'/'+action;
                break;
            case 'add-subscribers':
                break;
            default:
                break;
        }

    }

    function menuSelect(select) {
        var menuitems = ['MainMenu-begin', 'MainMenu-subscribers', 'MainMenu-mytalks', 'MainMenu-attachments', 'MainMenu-all'];
        menuitems.forEach(function(item) {
            if (item == select) {
                //var m = $(item);
                var m = document.getElementById(item);
                if (!(m.className == 'active')) {
                    m.className = 'active';
                }
            }
            else {
                var m = document.getElementById(item);
                if (m.className == 'active') {
                    m.className = '';
                }
            }
        })
    }

    function uploadFile(myFile, callback) {
        var fd = new FormData();
        var success = false;
        fd.append('files[]', myFile);
        fd.append('requesttoken', $('head').attr('data-requesttoken'));
        fd.append('dir', '/');
        fd.append('file_directory', 'Talks');

        $.ajax({
            url: "/index.php/apps/files/ajax/upload.php",
            type: "POST",
            data: fd,
            processData: false,
            contentType: false,
            success: function(response) {
                callback.call({}, response);
                //success = response;
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage); // Optional
            }
        });
        return success;
    }

    function sizeRoundedString(size) {
        if (size < 1) { return '0 bytes'; }

        var a = { 1099511628648 : 'TB', 1073741824 : 'GB', 1048576 : 'MB', 1024 : 'kB', 1 : "B" };

        var r = 0;
        var k = pk = 0;
        for(var key in a){
            k = key;
            var d = size / key;
            if (r > 0 && d < r && d >= 1 && d < 1024) {
                r = Math.round(d*100)/100;
                break;
            }
            else if (r > 0 && d < 1) {
                d = size / pk;
                r = Math.round(d*100)/100;
                k = pk;
                break;
            }
            else {
                pk = k;
                r = d;
            }
        }
        return r+' '+a[k];
    }

})(jQuery, OC, app);
