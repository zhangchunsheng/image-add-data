;(function($) {

    var defaults = {
        need_all_city: 0,
        only_operator: 0
    };

    function makeSubData(cityList) {
        var html = "";
        var subData = {};
        for(var i in cityList) {
            html += '<button type="button" data-id="' + cityList[i]["short"] + '" class="btn btn-default city-node">' + cityList[i]["cn"] + '</button>';
            subData = cityList[i]["sub_data"];

            html += makeSubData(subData);
        }
        return html;
    }

    $.fn.extend({
        citySelect: function (options) {
            var hasData = 0;
            var modal = $('#div-city-select').modal({show: false});
            modal.find('.modal-title').text("选择城市");

            var selecter = {};

            selecter.settings = $.extend({}, defaults, options);

            var url = "/region/getListForErp";
            if(selecter.settings.only_operator == 1) {
                url += "?only_operator=1";
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
                                    //var html = $("#city_select_template").html();

                                    var html = '';

                                    if(selecter.settings.need_all_city == 1) {
                                        html += '<div class="col-md-6">' +
                                        '<button type="button" data-id="" class="btn btn-default city-node">全部城市</button>' +
                                        '</div>';
                                    }

                                    var subData = {};
                                    for(var i in cityList) {
                                        html += '<div class="col-md-6">';
                                        if(cityList[i]["flag"] > 0) {
                                            html += '<h6><button type="button" data-id="' + cityList[i]["short"] + '" class="btn btn-default city-node">' + cityList[i]["cn"] + '</button></h6>';
                                        } else {
                                            html += '<h6>' + cityList[i]["cn"] + '</h6>';
                                        }

                                        subData = cityList[i]["sub_data"];
                                        html += makeSubData(subData);
                                        html += '</div>';
                                    }

                                    $("#div-city-select .modal-body .row").html(html);

                                    $(".city-node").click(function () {
                                        var node = $(this);
                                        that.attr("data-value", node.attr("data-id"));
                                        that.val(node.text());

                                        callback(node.attr("data-id"));

                                        modal.modal('hide');
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