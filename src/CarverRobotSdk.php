<?php

namespace Carver\CarverMsgTools;

use Carver\CarverMsgTools\dingding\DingDingRobot;


class CarverRobotSdk
{
    protected  $data = [];

    /**
     * @see 机器人发送消息
     * @param $params 参数说明如下
     * 聊天方式                chatType     (int)        1 群聊 2单聊
     * 发送的消息类型           msgType     (int )      1 text 2 markdown
     * 发送的消息内容           msgContent  (string)
     * 机器人@的人员手机号列表   msgReceiver (array)
     */
    public function setDingDingMsg(...$params)
    {
        try {
            if (!$params) throw new \Exception('Incorrect request parameters!');
            return DingDingRobot::getInstance()->sendMsg($params[0]);
        } catch (\Exception $e) {
            return json_encode(['code' => 4003, 'msg' => $e->getMessage()]);
        }
    }
}
