$(function() {
	getMsgCount();
	//全选
    $(".cball").change(function() {
        $(".cb").prop('checked', $(this).is(':checked'));
    });
});

function getMsgCount() {
	$.ajax({
        url : "/admin/message/getcount",
        dataType : "JSON",
        type : "GET"
    }).done(function(data) {
        if(data.code==200 && data.result > 0) {
        	$(".home-msg").html(data.result);
            $(".home-msg").show();
        } else {
        	$(".home-msg").html('');
        	$(".home-msg").hide();
        }
    });
	setTimeout("getMsgCount()", 10000);
}
