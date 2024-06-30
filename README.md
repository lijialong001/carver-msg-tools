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
    $msg['chatType'] = 1; //1 群聊 2单聊
    $msg['msgType'] = 1; // 1 text 2 markdown
    $msg['msgContent'] = '测试消息';
    $msg['msgReceiver'] = [18910513621];
    $data = $robotSdk->setDingDingMsg($msg);
    var_dump(json_decode($data, true));
} catch (\Exception $e) {
    var_dump($e->getMessage());
}
```
