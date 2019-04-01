;(function($) {

    var defaults = {
        need_all_city: 0,
        only_operator: 0
    };

    function makeSubData(cityList) {
        var html = "";
        var subData = {};
        for(var i in cityList) {
            //html += '<button type="button" data-id="' + cityList[i]["short"] + '" class="btn btn-default city-node">' + cityList[i]["cn"] + '</button>';
            html += '<label class="city-label"><input type="checkbox" class="new_list" name="new_city[]" value="' + cityList[i]["short"] + '">' + cityList[i]["cn"] + '</label>';
            subData = cityList[i]["sub_data"];

            html += makeSubData(subData);
        }
        return html;
    }
    
    $(".city-submit").click(function () {
        var node = $(this);
        var cityShort = cityText = '';
        $(".new_list").each(function() {
        	var cityNew = $(this);
            if (this.checked) {
            	cityShort += ","+cityNew.val();
            	cityText += ","+cityNew.parent().text();
            }  
        });
        if(cityShort){
        	cityShort = cityShort.substr(1, cityShort.length);
        }
        if(cityText){
        	cityText = cityText.substr(1, cityText.length);
        }
        if($(".checkAll").is(':checked')){
        	cityText = '全部城市';
        	cityShort = "_all_";
        }
        $('.city-select').val(cityText);
        $('.city-select').attr("data-value", cityShort)

		$('#div-city-select').modal('hide');
    });

    $.fn.extend({
        citySelect: function (options) {
            var hasData = 0;
            var modal = $('#div-city-select').modal({show: false});
            modal.find('.modal-title').text("选择城市");

            var selecter = {};

            selecter.settings = $.extend({}, defaults, options);

            var url = "/region/getListForErp?only_list=1&has_short=1";
            if(selecter.settings.only_operator == 1) {
                url += "&only_operator=1";
            }

            var callback = options.callback || function (short) {
                };

            var el = this;

            var that = $(this);

            var init = function() {
                that.bind("click", function() {
                    if (hasData == 0) {
                        $.ajax({
                            url: url,
                            type: 'get',
                            success: function (data) {
                                if (data.code == 200) {
                                    var cityList = data.result;
                                    var html = '';

                                    if(selecter.settings.need_all_city == 1) {
                                    	html += '<label class="city-label"><input type="checkbox" class="checkAll"> 全部</label><br><br>';
                                    }

                                    var subData = {};
                                    if(cityList){
                                    	var subHtml = "";
                                    	var cnList = [];
                                    	var fnList = [];
	                                    for(var i in cityList) {
	                                        if(cityList[i]["flag"] > 0) {

                                                if(cityList[i]["country"] == "CN"){
                                                    var labelHtml = '<label class="city-label font-label"><input type="checkbox" class="new_list cn_city" name="new_city[]"  value="' + cityList[i]["short"] + '">' + cityList[i]["cn"] + '</label>';
                                                    cnList.push(labelHtml);
                                                }else{
                                                    var labelHtml = '<label class="city-label font-label"><input type="checkbox" class="new_list fn_city" name="new_city[]"  value="' + cityList[i]["short"] + '">' + cityList[i]["cn"] + '</label>';
                                                    fnList.push(labelHtml);
                                                }
	                                        }
	                                    }
	                                    if(cnList.length > 0){
                                            subHtml += '<label class="city-label"><input type="checkbox" class="cnCheckAll"> 国内全部城市</label><br>';
                                            subHtml += cnList.join('');
                                            subHtml += "<br><br>";
                                        }

                                        if(fnList.length > 0){
                                            subHtml += '<label class="city-label"><input type="checkbox" class="fnCheckAll"> 国外全部城市</label><br>';
                                            subHtml += fnList.join('');
                                        }

	                                    if(subHtml != ""){
	                                    	html += subHtml;
	                                    }else {
	                                    	html = '<span style="color:red;text-align:center;">没有城市可添加,修改请点击列表中的编辑。</span>';
	                                    	$(".city-submit").attr("disabled", true); 
	                                    }
                                    }else {
                                    	html = '<span style="color:red;text-align:center;">没有城市可添加,修改请点击列表中的编辑。</span>';
                                    	$(".city-submit").attr("disabled", true); 
                                    }

                                    $("#div-city-select .modal-body .row").html(html);

                                    $('.checkAll').click(function(){
	                                    var list = $("input.new_list");
	                                    if(list.length == 0) {
	                                        alert('没有城市可添加!');
	                                        this.checked = false;
	                                        return;
	                                    }
	                                    list.prop('checked', this.checked);
                                        $('.cnCheckAll').prop('checked', this.checked);
                                        $('.fnCheckAll').prop('checked', this.checked);
	                                });
                                    $('.cnCheckAll').click(function(){
                                        var list = $("input.cn_city");
                                        if(list.length == 0) {
                                            alert('没有城市可添加!');
                                            this.checked = false;
                                            return;
                                        }
                                        list.prop('checked', this.checked);

                                    });
                                    $('.fnCheckAll').click(function(){
                                        var list = $("input.fn_city");
                                        if(list.length == 0) {
                                            alert('没有城市可添加!');
                                            this.checked = false;
                                            return;
                                        }
                                        list.prop('checked', this.checked);

                                    });
	                                $("input.new_list").change(function(){
	                                    $(".checkAll").attr("checked", $("input.new_list:not(:checked)").length == 0);
	                                });
                                    $("input.cn_city").change(function(){
                                        $(".cnCheckAll").attr("checked", $("input.cn_city:not(:checked)").length == 0);
                                    });
                                    $("input.fn_city").change(function(){
                                        $(".fnCheckAll").attr("checked", $("input.fn_city:not(:checked)").length == 0);
                                    });

                                    modal.modal('show');

                                    hasData = 1;
                                } else {
                                    Modal.alert({
                                        msg: data.msg
                                    });
                                }
                            },
                            error: function () {
                                Modal.alert({
                                    msg: "请重新登录"
                                });
                            }
                        });
                    } else {
                        modal.modal('show');
                    }
                });

                selecter.initialized = true;
            };

            el.setDefaultValue = function() {
                that.val("选择城市");
                that.attr("data-value", "");
            };

            el.setValue = function(value, dataValue) {
                that.val(value);
                that.attr("data-value", dataValue);
            };

            el.rebind = function() {
                if(selecter.initialized) {
                    return;
                }

                init();
            };

            el.destroySelecter = function() {
                if(!selecter.initialized) {
                    return;
                }
                that.unbind("click");
                selecter.initialized = false;
            };

            init();

            return this;
        }
    });
})(jQuery);