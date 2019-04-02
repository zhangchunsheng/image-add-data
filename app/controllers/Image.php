<?php
/**
 * Created by PhpStorm.
 * User: peterzhang
 * Date: 2019/4/2
 * Time: 2:28 PM
 */

class ImageController extends ApplicationController {
    // protected $layout = 'frontend';

    public function indexAction() {
        $textToImage = \LM\TextToImage::init();

        $url = $textToImage->makeImageFromString("test");

        $textToImage->showImage($url);
    }
}