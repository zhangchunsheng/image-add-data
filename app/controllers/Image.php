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

        $str = "你好，我是丁雪丰，喜欢写程序，《玩转 Spring 全家桶》的作者，曾经翻译过《Spring Boot 实战》与《Spring 攻略》两本书。从 2002 年误打误撞开始写 Java 后，就把这门语言当做自己的主要工作语言了，在此期间各种各样的框架层出不穷，一路上也见证了这门语言的飞快发展。
Spring 框架早已成为 Java 后端开发事实上的行业标准，如何用好 Spring ，也就成为 Java 程序员的必修课之一。
同时，Spring Boot 和 Spring Cloud 的出现，可以帮助工程师更好地基于 Spring 框架及各种基础设施来快速搭建系统，可以说，它们的诞生又一次解放了大家的生产力。
所以，Spring Boot 和 Spring Cloud 已成为 Spring 生态中不可或缺的一环。想成为一名合格的 Java 后端工程师，Spring Framework、Spring Boot、Spring Cloud 这三者必须都牢牢掌握。
在这里，我就对 Spring 整体的学习路径做一个梳理，方便大家查漏补缺。同时，这些内容我在《玩转 Spring 全家桶》这个视频课程里面也做了系统的讲解。
学习 Spring 的基础要求
Spring 官网首页是这么介绍自己的——“Spring: the source for modern Java”，这也暗示着 Spring 与 Java 有着密切的关系，虽然 Spring 现在也支持其他语言，比如 Groovy 和 Kotlin，但还是建议在学习 Spring 之前先储备一些基本的 Java 知识，如果能具备以下基础，则是更好不过了。
Spring 学习路径
掌握了上面那些基础之后，你就可以正式踏上 Spring 的学习之旅了。我们通常说的 Spring 主要包括 Spring Framework、Spring Boot 和 Spring Cloud，下面我就分别来看一下它们具体都包含哪些知识点。
1.Spring Framework
大家通常提到的 Spring 其实是指 Spring Framework，它是一个开源的 Java 企业级应用开发框架，提供了一套完整的编程与配置模型，降低了应用的开发复杂度，让开发者能够更加专注于应用真正的业务逻辑。
2.Spring Boot
随着 Spring 的发展，它早已从一个轻量级开发框架演变为一个“庞然大物”，从头开始搭建一个新应用的成本越来越高，充斥着大量的重复工作，有大量新的最佳实践需要总结并落地。因此，Spring Boot 应运而生，它能帮助开发者轻松地创建出具备生产能力的独立应用，只需很少的配置就能让大部分功能运作起来。毫不夸张地说，只要能用好 Spring Boot ，一定能够极大程度地提升开发效率。
3.Spring Cloud
在云计算日益普及的今天，微服务架构、云原生应用等概念也逐步被大家所接受，大家对大规模分布式系统早已司空见惯，这也对开发者提出了更高的要求。Spring Cloud 在 Spring Framework 与 Spring Boot 的基础之上，为分布式系统的开发提供了一套经过实践验证的常见模式，比如服务的发现与注册、服务的熔断与限流、服务配置、服务链路追踪等等。基于 Spring Cloud，开发者能够很快开发出一套分布式系统，以此满足不断变化的业务需要。
4. 其他 Spring 项目
除了上面提到的项目，Spring 的大家族中还有很多成员，它们也在各自的领域中不断为提升开发者的工作效率默默努力着。
上述很多知识点都会在我的视频课《玩转 Spring 全家桶》（15000+ 程序员已经加入学习）中涉及到，整个课程以实战为主，在基础的实践之外，还会涉及一些背后的原理与相关的知识扩展。课程中有大量的示例，还有一个贯穿始终的在线咖啡馆系统——SpringBucks，包含了下单、制作、派送的步骤，麻雀虽小却五脏俱全，随着课程的推进会不断丰满，我们会基于 Spring Framework、Spring Boot 和 Spring Cloud 打造一个完整的系统。";
        $url = $textToImage->makeImageFromString($str);

        $watermark = new \Ajaxray\PHPWatermark\Watermark($url);

        $watermark->setFontSize(48)
            ->setRotate(30)
            ->setOpacity(.4);

        $watermark->setDebug(true);
        $ret = $watermark->withText('易到用车', $url);
        //\LM\ImageUtil::imageWaterMark($url, 0, "", "易到用车", 40);

        $textToImage->showImage($url);
    }
}