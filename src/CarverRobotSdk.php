<?php

namespace Carver\CarverMsgTools;

use RobotService;

class CarverRobotSdk
{
    protected  $data = [];

    // public function getRobotType($type)
    // {
    //     $className  = 'dingding\\Robot';
    //     // 使用反射获取类的信息
    //     $class = new \ReflectionClass($className);
    //     $methodName = 'createRobot';
    //     // 检查类是否存在，并且方法是否可调用
    //     if ($class->hasMethod($methodName) && $class->getMethod($methodName)->isPublic()) {
    //         // 实例化类
    //         $object = new $className();
    //         // 调用方法
    //         $object->$methodName($data);
    //     } else {
    //         // 类或方法不存在的处理逻辑
    //         exit('this Type not exists!');
    //     }
    // }

    // 机器人发送消息
    public function sendRobotDingDingMsg(...$params)
    {
        try {
            if (count($params[0]) != 3) throw new \Exception('请求参数不正确!');
            return $params;
        } catch (\Exception $e) {
            return json_encode(['code' => 4003, 'msg' => $e->getMessage()]);
        }
    }
}
