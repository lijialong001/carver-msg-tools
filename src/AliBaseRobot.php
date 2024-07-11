<?php

namespace Carver\CarverMsgTools;
interface AliBaseRobot
{
    public function sendAliCall($params = []);

    public function returnJson($code = 0, $msg = '', $data = []);//规范返回值
}
