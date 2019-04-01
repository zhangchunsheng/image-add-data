<?php 

namespace LM;

class Page
{
    private $_totalRecord;
    private $_sizePerPage;
    private $_currentPage;
    private $_totalPage;
    private $_url;
    //每页记录数,当前页,总记录数,url,额外参数
    public function __construct($sizePerPage = 1, $currentPage = 1, 
                    $totalRecord = 1, $parameters = array(), $url = '', $pageName = 'page'){
        $params = array();
        if(!is_array($parameters)) {
            parse_str($parameters, $params);
            $parameters = $params;
        }
        if($url == ''){
            $url = $_SERVER['REQUEST_URI'];
        }
        $urlInfo = parse_url($url);
        if(isset($urlInfo['query'])) {
            parse_str($urlInfo['query'], $params);
            $parameters = array_merge($params, $parameters);
        }
        $path = isset($urlInfo['path']) ? $urlInfo['path'] : '';
        if(isset($parameters[$pageName])) {
            unset($parameters[$pageName]);
        }

        $url = $path . '?' . http_build_query($parameters) . '&' . $pageName . '=';
        $this->_url = $url;
        $this->_currentPage = $currentPage;
        $this->_totalPage = ceil($totalRecord / $sizePerPage);
        $this->_sizePerPage = $sizePerPage;
        $this->_totalRecord = $totalRecord;
    }

    public function getHtml(){
        $to = $this->_totalPage;
        $cur = $this->_currentPage;

        $pre = $cur - 1;
        $next = $cur + 1;
        $url = $this->_url;

        $html = <<<EOF
<style>
.pagelist{margin-right:85px;float:right;}
.pagelist li{display:inline-block; line-height: 20px; min-width:2em;float:left;}
.pagelist a{display:inline-block;width:3em;text-align:center;float:left;}
.pagelist .cur a{text-decoration:none;color:red;font-weight:bold;}
</style>
EOF;

        $html .= "<ul class='pagelist'><li>共 {$this->_totalRecord} 条，共 {$to} 页，当前第 {$cur} 页</li><li>&nbsp;</li>";

        if($pre >= 1) {
            $html .= "<li><a href='{$url}{$pre}'>上一页</a></li>";
        }

        for($i = 1; $i <= $to; $i ++) {
            if($i == $cur) {
                $html .= "<li class='cur'><a href='{$url}{$i}'>{$i}</a></li>";
            } else if($i <= 2 || $i >= ($to - 1) || ($i >= ($cur - 2) && $i <= ($cur + 2))) {
                $html .= "<li><a href='{$url}{$i}'> {$i} </a></li>";
            } else if($i > 2 && $i < $cur - 2) {
                $html .= '<li>...</li>'; 
                $i = $cur - 3;
            } else if($i > $cur + 2 && $i <= $to - 2) {
                $html .= '<li>...</li>';
                $i = $to - 2;
            }
        }

        if($next <= $to) {
            $html .= "<li><a href='{$url}{$next}'>下一页</a></li>";
        }
        $html .= "</ul>";
        return $html;
    }
}
