<?php
/**
 * Created by PhpStorm.
 * User: peterzhang
 * Date: 2019/4/1
 * Time: 11:38 AM
 */
namespace LM;

class TextToImage {
    /**
     *  Initializes TextToImage API instance generator
     *
     *  @return TextToImageApi
     */
    public static function init() {
        $generator = new TextToImageApi;
        return $generator;
    }
}

class TextToImageApi {
    /**
     *  Converts string to PNG
     *
     *  @param string $string The text to be converted
     *  @return string $url
     */
    public function makeImageFromString($string) {

        // Split string without breaking words
        $str = explode("\n", Util::utf8Wordwrap($string, 50, "\n"));
        $str = explode("\n", wordwrap($string, 50, "\n"));
        // Adjust height
        $adjustedHeight = count($str) * 20;

        // Generate Image
        $image = imagecreate(500, $adjustedHeight);
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $stringColor = imagecolorallocate($image, 0, 0, 0);
        $fontHeight = imagefontheight(20);
        imagesetthickness($image, 5);

        // Starting y position
        $y = 0;
        // Loop to break lines in image
        $font = PS_ROOT . "/fonts/SimHei.ttf";
        for ($x = 0; $x <= count($str) - 1; $x++) {
            //imagestring($image, 20, 30, $y, $str[$x], $stringColor);
            imagettftext($image, 20, 4, 30, $y, $stringColor, $font, $str[$x]);
            $y += 20;
        }

        // Save image to current directory
        $url = '/tmp/generated-image-' . uniqid() . '.png';

        // Return URL
        if (imagepng($image, $url)) {
            imagedestroy($image);
            return $url;
        }
        return false;
    }

    public function showImage($url) {
        header("Content-Type: image/png");

        $file = fopen($url, "r");
        $content = "";
        while(!feof($file)) {
            $content .= fgets($file);
        }
        fclose($file);

        echo $content;
        exit();
    }
}