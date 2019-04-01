;(function($) {

    var defaults = {
        need_all_cartype: 0
    };

    $.fn.extend({
        carTypeSelect: function (options) {
            var hasData = 0;
            var modal = $('#div-cartype-select').modal({show: false});

            var url = "/cartype/getsimplelist?status=1";

            var selecter = {};

            selecter.settings = $.extend({}, defaults, options);

            var callback = options.callback || function (short) {
                };

            var el = this;

            var that = $(this);

            var init = function () {
                that.bind("click", function() {
                    if (hasData == 0) {
                        $.ajax({
                            url: url,
                            type: 'get',
                            success: function (data) {
                                if (data.code == 200) {
                                    var carTypeList = data.result;
                                    //var html = $("#cartype_select_template").html();

                                    var html = '';
                                    for(var i in carTypeList) {
                                        html += '<div class="col-md-3">';
                                        html += '<button type="button" data-id="' + carTypeList[i]["car_type_id"] + '" class="btn btn-default cartype-node">' + carTypeList[i]["name"] + '</button>';
                                        html += '</div>';
                                    }

                                    $("#div-cartype-select .modal-body .row").html(html);

                                    $(".cartype-node").click(function () {
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
                that.val("选择车型");
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