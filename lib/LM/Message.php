<?php
namespace LM;

class Message {
    static public function error($msg, $url = '/') {
        $view = new \Yaf\View\Simple(__DIR__ . '/../../app/views/');
        $view->msg = $msg;
        $view->ssoUrl = Auth::getSSOUrl();
        $view->assign('operatorId', Auth::getUserId());
        $view->assign('operator', Auth::getUserName());
        echo $view->render('message/error.phtml');
        die;
    }

    static public function jump($url, $msg = '恭喜您，操作成功!', $time='') {
        $view = new \Yaf\View\Simple(__DIR__ . '/../../app/views/');
        $view->url = $url;
        $view->msg = $msg;
        $view->ssoUrl = Auth::getSSOUrl();
        $view->assign('operatorId', Auth::getUserId());
        $view->assign('operator', Auth::getUserName());
        $view->time = $time;
        echo $view->render('message/jump.phtml');
        die;
    }

    static public function showMsg($msg = '恭喜您，操作成功!', $url) {
        $view = new \Yaf\View\Simple(__DIR__ . '/../../app/views/');
        if($url == 'back') {
            $view->url = '#';
            $view->title = "<a href='javascript:history.back(-1)'>{$msg}</a> ";
        } else {
            $view->title = "<a href='$url' target='_blank'>{$msg}</a> ";
            $view->url = $url;
        }
        $view->ssoUrl = Auth::getSSOUrl();
        $view->assign('operatorId', Auth::getUserId());
        $view->assign('operator', Auth::getUserName());

        echo $view->render('message/showmsg.phtml');
        die;
    }
}
