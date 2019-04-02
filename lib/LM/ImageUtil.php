<?php
/**
 * Created by PhpStorm.
 * User: peterzhang
 * Date: 2019/4/1
 * Time: 3:28 PM
 */
namespace LM;

class ImageUtil {
    /*
     * 功能：PHP图片水印 (水印支持图片或文字)
     * 参数：
     * $groundImage 背景图片，即需要加水印的图片，暂只支持GIF,JPG,PNG格式；
     * $waterPos水印位置，有10种状态，0为随机位置；
     * 1为顶端居左，2为顶端居中，3为顶端居右；
     * 4为中部居左，5为中部居中，6为中部居右；
     * 7为底端居左，8为底端居中，9为底端居右；
     * $waterImage图片水印，即作为水印的图片，暂只支持GIF,JPG,PNG格式；
     * $waterText文字水印，即把文字作为为水印，支持ASCII码，不支持中文；
     * $textFont文字大小，值为1、2、3、4或5，默认为5；
     * $textColor文字颜色，值为十六进制颜色值，默认为#FF0000(红色)；
     *
     * 注意：Support GD 2.0，Support FreeType、GIF Read、GIF Create、JPG 、PNG
     * $waterImage 和 $waterText 最好不要同时使用，选其中之一即可，优先使用 $waterImage。
     * 当$waterImage有效时，参数$waterString、$stringFont、$stringColor均不生效。
     * 加水印后的图片的文件名和 $groundImage 一样。
     */

    public static function imageWaterMark($groundImage, $waterPos = 0, $waterImage = "", $waterText = "", $textFont = 5, $textColor = "#FF0000") {
        $isWaterImage = false;
        $formatMsg = "暂不支持该文件格式，请用图片处理软件将图片转换为GIF、JPG、PNG格式。";

        //读取水印文件
        if(!empty($waterImage) && file_exists($waterImage)) {
            $isWaterImage = true;
            $waterInfo = getimagesize($waterImage);
            $waterW = $waterInfo[0];//取得水印图片的宽
            $waterH = $waterInfo[1];//取得水印图片的高
            switch($waterInfo[2]) {//取得水印图片的格式
                case 1:
                    $waterImg = imagecreatefromgif($waterImage);
                    break;
                case 2:
                    $waterImg = imagecreatefromjpeg($waterImage);
                    break;
                case 3:
                    $waterImg = imagecreatefrompng($waterImage);
                    break;
                default:
                    die($formatMsg);
            }
        }

        //读取背景图片
        if(!empty($groundImage) && file_exists($groundImage)) {
            $groundInfo = getimagesize($groundImage);
            $groundW = $groundInfo[0];//取得背景图片的宽
            $groundH = $groundInfo[1];//取得背景图片的高
            switch($groundInfo[2]) {//取得背景图片的格式
                case 1:
                    $groundImg = imagecreatefromgif($groundImage);
                    break;
                case 2:
                    $groundImg = imagecreatefromjpeg($groundImage);
                    break;
                case 3:
                    $groundImg = imagecreatefrompng($groundImage);
                    break;
                default:
                    die($formatMsg);
            }
        } else {
            die("需要加水印的图片不存在！");
        }

        //水印位置
        if($isWaterImage) {//图片水印
            $w = $waterW;
            $h = $waterH;
            $label = "图片的";
        } else {//文字水印
            $temp = imagettfbbox(ceil($textFont * 5), 0, PS_ROOT . "/fonts/SimHei.ttf", $waterText);//取得使用 TrueType 字体的文本的范围
            $w = $temp[2] - $temp[6];
            $h = $temp[3] - $temp[7];
            unset($temp);
            $label = "文字区域";
        }

        if(($groundW < $w) || ($groundH < $h)) {
            $errorMsg = "需要加水印的图片的长度或宽度比水印" . $label . "还小，无法生成水印！";
            die($errorMsg);
        }

        switch($waterPos) {
            case 0://随机
                $posX = rand(0,($groundW - $w));
                $posY = rand(0,($groundH - $h));
                break;
            case 1://1为顶端居左
                $posX = 0;
                $posY = 0;
                break;
            case 2://2为顶端居中
                $posX = ($groundW - $w) / 2;
                $posY = 0;
                break;
            case 3://3为顶端居右
                $posX = $groundW - $w;
                $posY = 0;
                break;
            case 4://4为中部居左
                $posX = 0;
                $posY = ($groundH - $h) / 2;
                break;
            case 5://5为中部居中
                $posX = ($groundW - $w) / 2;
                $posY = ($groundH - $h) / 2;
                break;
            case 6://6为中部居右
                $posX = $groundW - $w;
                $posY = ($groundH - $h) / 2;
                break;
            case 7://7为底端居左
                $posX = 0;
                $posY = $groundH - $h;
                break;
            case 8://8为底端居中
                $posX = ($groundW - $w) / 2;
                $posY = $groundH - $h;
                break;
            case 9://9为底端居右
                $posX = $groundW - $w - 10;   // -10 是距离右侧10px 可以自己调节
                $posY = $groundH - $h - 10;   // -10 是距离底部10px 可以自己调节
                break;
            default://随机
                $posX = rand(0,($groundW - $w));
                $posY = rand(0,($groundH - $h));
                break;
        }

        //设定图像的混色模式
        imagealphablending($groundImg, true);
        if($isWaterImage) {//图片水印
            imagecopy($groundImg, $waterImg, $posX, $posY, 0, 0, $waterW, $waterH);//拷贝水印到目标文件
        } else {//文字水印
            if(!empty($textColor) && (strlen($textColor) == 7)) {
                $R = hexdec(substr($textColor,1,2));
                $G = hexdec(substr($textColor,3,2));
                $B = hexdec(substr($textColor,5));
            } else {
                die("水印文字颜色格式不正确！");
            }
            $font = PS_ROOT . "/fonts/SimHei.ttf";
            //imagestring($groundImg, $textFont, $posX, $posY, $waterText, imagecolorallocate($groundImg, $R, $G, $B));
            imagettftext($groundImg, $textFont, 4, $posX, $posY, imagecolorallocate($groundImg, $R, $G, $B), $font, $waterText);
        }

        //生成水印后的图片
        @unlink($groundImage);

        switch($groundInfo[2]) {//取得背景图片的格式
            case 1:
                imagegif($groundImg, $groundImage);
                break;
            case 2:
                imagejpeg($groundImg, $groundImage);
                break;
            case 3:
                imagepng($groundImg, $groundImage);
                break;
            default:
                die($groundInfo[2] . " is not support");
        }

        //释放内存
        if(isset($waterInfo))
            unset($waterInfo);
        if(isset($waterImg))
            imagedestroy($waterImg);
        unset($groundInfo);
        imagedestroy($groundImg);
    }
}