# carver-msg-tools

#### 介绍
集成了第三方工具【钉钉】机器人操作

#### 安装教程

composer require carver/carver-msg-tools

#### 使用说明

原生引入方式如下：

```
<?php
// 引入composer 包
require __DIR__ . '/carver-msg-tools/vendor/autoload.php';
// 引入 CarverDingDing sdk
use Carver\CarverMsgTools\CarverRobotSdk;

try {
    // 实例化 test sdk
    $robotSdk = new CarverRobotSdk();
    //使用方法如下：

    //----------------------------单聊----------------------------
    $singleConfig = [
        'appKey'    => '机器人key',
        'appSecret' => '机器人秘钥',
        'userIds'   => ['1441442424'] //钉钉分配给每个用户的唯一id
    ];
    //单聊发送文本形式
    $sendContentData = ['content'=>'hello'];
    $result = $robotSdk->setSingleChatTextData($sendContentData)->sendSingleDingDingMsg($singleConfig);
    //单聊发送markdown形式
    $sendContentData = ['title'=>'状态标题,不显示','text'=>'hello'];
    $result = $robotSdk->setSingleChatMarkdownData($sendContentData)->sendSingleDingDingMsg($singleConfig);
    var_dump(json_decode($result, true));

    //----------------------------群聊----------------------------
    //方式1：【直接使用创建机器人的webHook】
    $webHook = ['webHook' => '机器人的webhook'];
    $sendContentData =[
        'msgType'=> 1,                  // 1. text文本格式  2. markdown格式
        'msgReceiver'=>['17515487857'], // 被@人的手机号列表
        'msgContent'=>'发送的内容'        // 发送的内容 如果参数 msgType = 2 可以添加自定义的markdown样式
    ]
    $data    = $robotSdk->setWebHook($webHook)->sendGroupDingDingMsg($sendContentData);
    var_dump(json_decode($result, true));

    //方式2：【通过验签方式自动生成webhook】
    $webHook = ['secret' => '机器人秘钥','accessToken'=>'机器人验签的token'];
    $data    = $robotSdk->setWebHookBySign($webHook)->sendGroupDingDingMsg($sendContentData);
    var_dump(json_decode($result, true));

} catch (\Exception $e) {
    var_dump($e->getMessage());
}
```

版本更新可查看:
https://packagist.org/packages/carver/carver-msg-tools
