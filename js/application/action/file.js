if(App.namespace){App.namespace('Action.File', function(App){
    /**
     * @namespace App.Action.File
     */
    var _ = {};

    _.date = function(){};


    _.clickUpload = function(){




        /*
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
        */
    };

    /**
     * @namespace App.Action.uploadFile
     * @param myFile
     * @param callback
     * @returns {boolean}
     */
    _.uploadFile = function (myFile, callback) {

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
    };



    return _;
})}