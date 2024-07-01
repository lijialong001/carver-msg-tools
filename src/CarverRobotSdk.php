<?php

namespace Carver\CarverMsgTools;

use Carver\CarverMsgTools\dingding\DingDingRobot;
use Carver\CarverMsgTools\dingding\AliRobot;

class CarverRobotSdk
{
    protected $data = [];
    protected $dingDingModelInfo = null;
    protected $aliModelInfo = null;

    public function __construct()
    {
        if (!$this->dingDingModelInfo) $this->dingDingModelInfo = new  DingDingRobot();
        if (!$this->aliModelInfo) $this->aliModelInfo = new  AliRobot();
    }

    /**
     * @param mixed ...$params 参数说明如下
     * @return false|string
     * 聊天方式                chatType     (int)        1 群聊 2单聊
     * 发送的消息类型           msgType     (int )      1 text 2 markdown
     * 发送的消息内容           msgContent  (string)
     * 机器人@的人员手机号列表   msgReceiver (array)
     * @author Carver
     * @see 机器人发送消息
     */
    public function sendGroupDingDingMsg(...$params)
    {
        try {
            if (!$params) throw new \Exception('Incorrect request parameters!');
            $this->data['sendGroupDingDingMsg'] = $params[0];
            $this->data['chatType']             = 1;                                                 //群聊
            $checkResultJson                    = $this->dingDingModelInfo->checkParams($this->data);//验证发送内容参数
            $checkResult                        = json_decode($checkResultJson, true);
            if ($checkResult['code'] != 200) return $checkResultJson;                                                     //参数验证不通过
            return $this->dingDingModelInfo->sendGroupMsg($this->data, $checkResult['data']['webHook']);                  //发送操作
        } catch (\Exception $e) {
            return json_encode(['code' => 4003, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * @param mixed ...$params
     * @return $this
     * @see 通过生成签名生成webhook链接
     * @author Carver
     */
    public function setWebHookBySign(...$params)
    {
        try {
            if (!$params) throw new \Exception('Request requires two parameters!');
            $this->data['makeWebHookUrl'] = $params[0];
            return $this;
        } catch (\Exception $e) {
            return json_encode(['code' => 4003, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * @param mixed ...$params
     * @return $this
     * @see 设置webhook链接
     * @author Carver
     */
    public function setWebHook(...$params)
    {
        try {
            if (!$params) throw new \Exception('Request requires a parameter!');
            $this->data['setWebHookUrl'] = $params[0];
            return $this;
        } catch (\Exception $e) {
            return json_encode(['code' => 4003, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * @param mixed ...$params 参数说明如下
     * @return false|string
     * @author Carver
     * @see 机器人发送消息
     */
    public function sendSingleDingDingMsg(...$params)
    {
        try {
            if (!$params) throw new \Exception('Incorrect request parameters!');
            $this->data['setSingleDingDingMsg'] = $params[0];
            $checkResultJson                    = $this->dingDingModelInfo->checkParams($this->data);//验证发送内容参数
            $checkResult                        = json_decode($checkResultJson, true);
            if ($checkResult['code'] != 200) return $checkResultJson;                                                //参数验证不通过
            return $this->dingDingModelInfo->sendSingleMsg($this->data);                                             //发送操作
        } catch (\Exception $e) {
            return json_encode(['code' => 4003, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * @param mixed ...$params
     * @return $this
     * @see 设置单聊机器人的告警类型【text】
     * @author Carver
     */
    public function setSingleChatTextData(...$params)
    {
        try {
            if (!$params) throw new \Exception('Request requires a parameter!');
            $this->data['setSingleChatTextData'] = $params[0];
            return $this;
        } catch (\Exception $e) {
            return json_encode(['code' => 4003, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * @param mixed ...$params
     * @return $this
     * @see 设置单聊机器人的告警类型【markdown】
     * @author Carver
     */
    public function setSingleChatMarkdownData(...$params)
    {
        try {
            if (!$params) throw new \Exception('Request requires a parameter!');
            $this->data['setSingleChatMarkdownData'] = $params[0];
            return $this;
        } catch (\Exception $e) {
            return json_encode(['code' => 4003, 'msg' => $e->getMessage()]);
        }
    }



}
