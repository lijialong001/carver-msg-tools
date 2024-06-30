<?php

namespace Carver\CarverMsgTools\dingding;


class DingDingRobot
{
    private static $instance;
    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function sendMsg($params)
    {
        try {
            if (count($params) < 2) throw new \Exception('Request at least two parameters!');
            if (!$params['msgType'] || !$params['chatType']) throw new \Exception('The request parameter [msgType | chatType] cannot be empty!');
            if (gettype($params['msgReceiver']) != 'array') throw new \Exception('The type of the request parameter [msgReceiver] is incorrect!');
            return json_encode('请求成功');
        } catch (\Exception $e) {
            return json_encode(['code' => 4003, 'msg' => $e->getMessage()]);
        }
    }

    private function __clone()
    {
    }
}
