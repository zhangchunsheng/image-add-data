(function(LM) {
    LM.initUpload = function(callback) {
        var url = '/cloud/uploadimg';
        $('.upload_img').fileupload({
            url: url,
            dataType: 'json',
            done: function (e, data) {
                var result = data.result;
                if(result.code == 200) {
                    callback(result.result);
                } else {
                    Modal.alert({
                        msg: result.msg
                    });
                }
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);

                $(this).parent().parent().children(".progress").css(
                    'width',
                    progress + '%'
                );
            }
        });
        $('.upload_img').bind('fileuploadsubmit', function (e, data) {
            // The example input, doesn't have to be part of the upload form:
        });
    }
})(LM);