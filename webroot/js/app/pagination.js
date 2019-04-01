(function(LM) {
    LM.Pagination = function(table) {
        this.offset = 0;
        this.numberPerPage = 10;
        this.currentPage = 1;
        this.table = table;

        var that = this;

        this.updatePageInfo = function() {
            var currentPage = 1;

            this.offset = 0;
            //this.numberPerPage = 10;
            this.currentPage = 1;

            this.drawPageHtml(currentPage);
        };

        this.setPageInfo = function(gotoPage) {
            var offset = (gotoPage - 1) * this.numberPerPage;

            this.currentPage = gotoPage;
            this.offset = offset;
        };

        this.initPage = function() {
            var currentPage = this.currentPage;
            this.drawPageHtml(currentPage);

            var html = "";
            var number = 10;
            for(var i = 0 ; i < 10 ; i++) {
                number = 10 * (i + 1);
                html += '<option value="' + number + '">' + number + '</option>';
            }
            $(".number_per_page").html(html);

            $(".number_per_page").bind("change", function(e) {
                that.numberPerPage = $(this).val();
                that.updatePageInfo();
                that.table.bootstrapTable('refresh');
            });
        };

        this.drawPageHtml = function(currentPage) {
            var maxNum = 6;
            var startPage = 0;
            var endPage = 0;

            startPage = Math.floor((currentPage - 1) / maxNum) * maxNum + 1;
            endPage = startPage + maxNum;

            if($("ul.pagination").children().length > 0) {
                var liStartPage = $("li.page-first").children().html();
                if(startPage == liStartPage) {
                    //return;
                }
            }

            $(".current_page").html(currentPage);

            var html = '<li class="page-pre"><a href="javascript:void(0)">‹</a></li>';
            var page_class = "";
            for(var i = startPage ; i < endPage ; i++) {
                if(i == startPage) {
                    page_class = "page-first";
                } else if(i == endPage - 1) {
                    page_class = "page-last";
                } else {
                    page_class = "page-number";
                }
                if(currentPage == i) {
                    page_class += " active active_page";
                }
                html += '<li class="' + page_class + '"><a href="javascript:void(0)">' + i + '</a></li>';
            }
            html += '<li class="page-next"><a href="javascript:void(0)">›</a></li>';

            $("ul.pagination").html(html);

            this.bindPageEvent();
        };

        this.bindPageEvent = function() {
            $("ul.pagination > li").click(function(e) {
                var className = $(this).attr("class");
                var gotoPage = 0;
                if(className == "page-pre") {
                    var currentPage = $("li.active_page").children().html();
                    gotoPage = parseInt(currentPage) - 1;
                    if(gotoPage <= 0) {
                        return;
                    }
                    $("li.active_page").removeClass("active");
                    $("li.active_page").removeClass("active_page");
                } else if(className == "page-next") {
                    var currentPage = $("li.active_page").children().html();
                    gotoPage = parseInt(currentPage) + 1;

                    $("li.active_page").removeClass("active");
                    $("li.active_page").removeClass("active_page");
                } else {
                    gotoPage = $(this).children().html();

                    $("li.active_page").removeClass("active");
                    $("li.active_page").removeClass("active_page");
                    $(this).addClass("active");
                    $(this).addClass("active_page");
                }

                var offset = (gotoPage - 1) * that.numberPerPage;
                that.currentPage = gotoPage;
                that.offset = offset;

                that.table.bootstrapTable('refresh');

                that.drawPageHtml(gotoPage);
            });
        };

        this.bindGotoEvent = function() {
            $(".go-to-page").click(function(e) {
                var gotoPage = $("#page_number").val();

                if(gotoPage == "" || parseInt(gotoPage) <= 0) {
                    Modal.alert({
                        msg: "请输入页码"
                    });
                    return;
                }
                $("li.active_page").removeClass("active");
                $("li.active_page").removeClass("active_page");

                that.setPageInfo(gotoPage);

                that.table.bootstrapTable('refresh');

                that.drawPageHtml(gotoPage);
            });
        };

        this.addButton = function() {
            var html = '<button type="button" class="btn btn-primary tutorial">使用说明</button>';
            $(".page-go-to").append(html);
        };

        this.initPage();
        this.bindGotoEvent();
    };
})(LM);