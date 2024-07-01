<?php

namespace Carver\CarverMsgTools;
interface BaseRobot
{
    public function makeWebHookUrl($params);//生成webHook链接

    public function setWebHookUrl($params);//设置webHook链接

    public function checkParams($params);//检查请求参数

    public function returnJson($code = 0, $msg = '', $data = []);//规范返回值
}
