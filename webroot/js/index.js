/**
 * AdminLTE Menu
 * ------------------
 * You should not use this file in production.
 * This file is for demo purposes only.
 */
(function ($, AdminLTE) {

    "use strict";

    setup();

    /**
     * Toggles layout classes
     *
     * @param String cls the layout class to toggle
     * @returns void
     */
    function change_layout(cls) {
        $("body").toggleClass(cls);
        AdminLTE.layout.fixSidebar();
        //Fix the problem with right sidebar and layout boxed
        if (cls == "layout-boxed")
            AdminLTE.controlSidebar._fix($(".control-sidebar-bg"));
        if ($('body').hasClass('fixed') && cls == 'fixed') {
            AdminLTE.pushMenu.expandOnHover();
            AdminLTE.layout.activate();
        }
        AdminLTE.controlSidebar._fix($(".control-sidebar-bg"));
        AdminLTE.controlSidebar._fix($(".control-sidebar"));
    }

    /**
     * Replaces the old skin with the new skin
     * @param String cls the new skin class
     * @returns Boolean false to prevent link's default action
     */
    function change_skin(cls) {
        $("body").addClass(cls);
        store('skin', cls);
        return false;
    }

    /**
     * Store a new settings in the browser
     *
     * @param String name Name of the setting
     * @param String val Value of the setting
     * @returns void
     */
    function store(name, val) {
        if (typeof (Storage) !== "undefined") {
            localStorage.setItem(name, val);
        } else {
            window.alert('Please use a modern browser to properly view this template!');
        }
    }

    /**
     * Get a prestored setting
     *
     * @param String name Name of of the setting
     * @returns String The value of the setting | null
     */
    function get(name) {
        if (typeof (Storage) !== "undefined") {
            return localStorage.getItem(name);
        } else {
            window.alert('Please use a modern browser to properly view this template!');
        }
    }

    /**
     * Retrieve default settings and apply them to the template
     *
     * @returns void
     */
    function setup() {
        change_skin("skin-purple-light");

        //Add the layout manager
        $("[data-layout]").on('click', function () {
            change_layout($(this).data('layout'));
        });

        $("[data-controlsidebar]").on('click', function () {
            change_layout($(this).data('controlsidebar'));
            var slide = !AdminLTE.options.controlSidebarOptions.slide;
            AdminLTE.options.controlSidebarOptions.slide = slide;
            if (!slide)
                $('.control-sidebar').removeClass('control-sidebar-open');
        });

        $("[data-sidebarskin='toggle']").on('click', function () {
            var sidebar = $(".control-sidebar");
            if (sidebar.hasClass("control-sidebar-dark")) {
                sidebar.removeClass("control-sidebar-dark")
                sidebar.addClass("control-sidebar-light")
            } else {
                sidebar.removeClass("control-sidebar-light")
                sidebar.addClass("control-sidebar-dark")
            }
        });

        $("[data-enable='expandOnHover']").on('click', function () {
            $(this).attr('disabled', true);
            AdminLTE.pushMenu.expandOnHover();
            if (!$('body').hasClass('sidebar-collapse'))
                $("[data-layout='sidebar-collapse']").click();
        });

        // Reset options
        if ($('body').hasClass('fixed')) {
            $("[data-layout='fixed']").attr('checked', 'checked');
        }
        if ($('body').hasClass('layout-boxed')) {
            $("[data-layout='layout-boxed']").attr('checked', 'checked');
        }
        if ($('body').hasClass('sidebar-collapse')) {
            $("[data-layout='sidebar-collapse']").attr('checked', 'checked');
        }

    }
})(jQuery, $.AdminLTE);

$(function () {
    window.Modal = function () {
        var reg = new RegExp("\\[([^\\[\\]]*?)\\]", 'igm');
        var alr = $("#lm-alert");
        var ahtml = alr.html();

        //关闭时恢复 modal html 原样，供下次调用时 replace 用
        //var _init = function () {
        //	alr.on("hidden.bs.modal", function (e) {
        //		$(this).html(ahtml);
        //	});
        //}();

        /* html 复原不在 _init() 里面做了，重复调用时会有问题，直接在 _alert/_confirm 里面做 */

        var _alert = function (options) {
            alr.html(ahtml);	// 复原
            alr.find('.ok').removeClass('btn-success').addClass('btn-primary');
            alr.find('.cancel').hide();
            _dialog(options);

            return {
                on: function (callback) {
                    if (callback && callback instanceof Function) {
                        alr.find('.ok').click(function () { callback(true) });
                    }
                }
            };
        };

        var _confirm = function (options) {
            alr.html(ahtml); // 复原
            alr.find('.ok').removeClass('btn-primary').addClass('btn-success');
            alr.find('.cancel').show();
            _dialog(options);

            return {
                on: function (callback) {
                    if (callback && callback instanceof Function) {
                        alr.find('.ok').click(function () { callback(true) });
                        alr.find('.cancel').click(function () { callback(false) });
                    }
                }
            };
        };

        var _dialog = function (options) {
            var ops = {
                msg: "提示内容",
                title: "操作提示",
                btnok: "确定",
                btncl: "取消"
            };

            $.extend(ops, options);

            var html = alr.html().replace(reg, function (node, key) {
                return {
                    Title: ops.title,
                    Message: ops.msg,
                    BtnOk: ops.btnok,
                    BtnCancel: ops.btncl
                }[key];
            });

            alr.html(html);
            alr.modal({
                width: 500,
                backdrop: 'static'
            });
        };

        return {
            alert: _alert,
            confirm: _confirm
        }
    }();
});

/**
 * set menu active
 */
$(function () {
    var pathname = window.location.pathname;

    $("ul.sidebar-menu > li.treeview").each(function(ele) {
        if($(this).children().attr("href") == "#") {
            var that = this;
            var submenus = $(this).children()[1];
            $(submenus).children().each(function(subEle) {
                if($(this).children().attr("href") == pathname) {
                    $(submenus).show();
                    $(this).addClass("active");
                    $(that).addClass("active");
                }
            });
        } else if($(this).children().attr("href") == pathname) {
            $(this).addClass("active");
        }
    });

    $('input[name="dict_code"]').keydown(function() {
        if($(this).val().length == 11) {
            return false;
        }
    });
});

LM = {};

(function(LM) {
    LM.blueCellStyle = function(value, row, index, field) {
        return {
            classes: '',
            css: {"background-color": "#46C7C7"}
        };
    };

    LM.timeCellStyle = function(value, row, index, field) {
        return {
            classes: '',
            css: {"font-size": "10px"}
        };
    };

    LM.descCellStyle = function(value, row, index, field) {
        return {
            classes: '',
            css: {
                "word-break": "break-all",
                "text-overflow": "ellipsis",
                "overflow": "hidden"
            }
        };
    };

    LM.statusFormatter = function(value, row, index) {
        if(value == 1) {
            return "正常";
        } else if(value == -1) {
            return "删除";
        } else {
            return "未知";
        }
    };

    LM.descFormatter = function(value, row, index) {
        return '<div class="td_desc" data-toggle="tooltip" title="' + value + '">' + value + '</div>';
    };

    LM.desc400Formatter = function(value, row, index) {
        return '<div class="td400_desc" data-toggle="tooltip" title="' + value + '">' + value + '</div>';
    };

    LM.imgFormatter = function(value, row, index) {
        return '<img src="' + value + '" style="width:26px;height:16px"></img>';
    };

    LM.latLngFormatter = function(value, row, index) {
        return '<label class="map-select" data-lng="' + row.out_coordinates.baidu.lng + '" data-lat="' + row.out_coordinates.baidu.lat + '" data-coord-type="baidu">' + value + '</label>';
    };

    // update and delete events
    LM.descEvent = {
        'mouseover .td_desc': function (e, value, row) {

        }
    };

    LM.showTips = function() {
        Modal.alert({
            msg: "改功能已下线，请联系王晶咨询"
        });
    };

    LM.getQueryString = function(name) {
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if(r != null)
            return r[2];
        return "";
    };

    /*$('.modal').on('hidden.bs.modal', function( event ) {
        $(this).removeClass( 'fv-modal-stack' );
        $('body').data( 'fv_open_modals', $('body').data( 'fv_open_modals' ) - 1 );
    });

    $( '.modal' ).on( 'shown.bs.modal', function ( event ) {
        // keep track of the number of open modals

        if ( typeof( $('body').data( 'fv_open_modals' ) ) == 'undefined' ) {
            $('body').data( 'fv_open_modals', 0 );
        }

        // if the z-index of this modal has been set, ignore.

        if ( $(this).hasClass( 'fv-modal-stack' ) ) {
            return;
        }

        $(this).addClass( 'fv-modal-stack' );

        $('body').data( 'fv_open_modals', $('body').data( 'fv_open_modals' ) + 1 );

        $(this).css('z-index', 1040 + (10 * $('body').data( 'fv_open_modals' )));

        $( '.modal-backdrop' ).not( '.fv-modal-stack' )
            .css( 'z-index', 1039 + (10 * $('body').data( 'fv_open_modals' )));

        $( '.modal-backdrop' ).not( 'fv-modal-stack' )
            .addClass( 'fv-modal-stack' );
    });*/
})(LM);
