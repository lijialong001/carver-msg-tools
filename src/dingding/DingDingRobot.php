<?php

namespace Carver\CarverMsgTools\dingding;

use Carver\CarverMsgTools\BaseRobot;

class DingDingRobot implements BaseRobot
{
    protected $apiBaseUrl = 'https://oapi.dingtalk.com/robot/send';

    protected $config = ['webHook' => '', 'code' => 0, 'msg' => '', 'request' => 'fail'];

    public function checkParams($params)
    {
        try {
            $configInfo = [];
            if (isset($params['chatType']) && $params['chatType'] == 1) {//群聊
                //第一步：检查当前内容的参数
                if (count($params['sendGroupDingDingMsg']) < 2) throw new \Exception('Request at least two parameters!');
                if (!isset($params['sendGroupDingDingMsg']['msgType'])) {//发送消息的类型[msgType]
                    throw new \Exception('The request parameter [msgType] cannot be empty!');
                }
                if (gettype($params['sendGroupDingDingMsg']['msgReceiver']) != 'array') {//告警被@的人员手机号码
                    throw new \Exception('The type of the request parameter [msgReceiver] is incorrect!');
                }
                //第二部：检查当前发送需要的webHook链接方式
                if (isset($params['makeWebHookUrl'])) {//验签方式生成webHook链接
                    $webHookUrl = $this->makeWebHookUrl($params['makeWebHookUrl']);
                } elseif (isset($params['setWebHookUrl'])) {//直接传递一个webHook链接
                    $webHookUrl = $this->setWebHookUrl($params['setWebHookUrl']);
                } else {
                    throw new \Exception('Please choose the sending method!');
                }
                $configInfo = ['webHook' => $webHookUrl];
            } elseif ($params['chatType'] == 2) {//单聊
                if (empty($params['setSingleDingDingMsg']['appKey'])) throw new \Exception('The request parameter [appKey] cannot be empty!');
                if (empty($params['setSingleDingDingMsg']['appSecret'])) throw new \Exception('The request parameter [appSecret] cannot be empty!');
                if (empty($params['setSingleDingDingMsg']['userIds'])) throw new \Exception('The request parameter [userIds] cannot be empty!');
                if ((empty($params['setSingleChatTextData']) && empty($params['setSingleChatMarkdownData'])) || (!empty($params['setSingleChatTextData']) && !empty($params['setSingleChatMarkdownData']))) {
                    throw new\Exception('Only one method can be selected!');
                }
                $configInfo = [];
            }
            return $this->returnJson(200, '验证通过!', $configInfo);
        } catch (\Exception $e) {
            return $this->returnJson($e->getCode(), $e->getMessage());
        }
    }

    /**
     * @param $params
     * @param string $webHook
     * @return false|string
     * @see 发送群聊消息
     * @author Carver
     */
    public function sendGroupMsg($params, $webHook = '')
    {
        try {
            $setDingDingMsg = $params['sendGroupDingDingMsg'];
            if ($setDingDingMsg['msgType'] == 1) {//1 text
                $contentInfo = [
                    'text' => [
                        'content' => $setDingDingMsg['msgContent']
                    ]
                ];
            } elseif ($setDingDingMsg['msgType'] == 2) {//2 markdown
                $msgReceiverStr = '';
                if ($setDingDingMsg['msgReceiver']) $msgReceiverStr = '@' . implode(',@', $setDingDingMsg['msgReceiver']);
                $contentInfo = [
                    'markdown' => [
                        'title' => '主人，来信息啦!',
                        'text'  => $setDingDingMsg['msgContent'] . $msgReceiverStr
                    ]
                ];
            } elseif ($setDingDingMsg['msgType'] == 3) {//actionCard
                if (empty($setDingDingMsg['title'])) throw new \Exception('The request parameter [title] cannot be empty!');
                $contentInfo = [
                    'actionCard' => [
                        'title'          => $setDingDingMsg['title'],
                        'text'           => "### **{$setDingDingMsg['title']}** \n **{$setDingDingMsg['msgContent']}**",
                        'btnOrientation' => '0',
                        'btns'           => [
                            [
                                'title'     => '点击查看详情',
                                'actionURL' => $setDingDingMsg['actionURL']
                            ]
                        ]
                    ]
                ];
            }
            $this->getJsonData($contentInfo, $setDingDingMsg);
            $response           = $this->curlRequest($webHook, $contentInfo, 'json', 'post');
            $response           = json_decode($response, true);
            $code               = 200;
            $msg                = '发送成功!';
            $jsonData['result'] = 'success';
            if (in_array($response['errcode'], [310000, 130101])) {
                $code               = $response['errcode'];
                $msg                = $response['errmsg'];
                $jsonData['result'] = 'fail';
            }
            return $this->returnJson($code, $msg, $jsonData);
        } catch (\Exception $e) {
            return $this->returnJson(4003, $e->getMessage());
        }
    }

    /**
     * @param $params
     * @return false|string
     * @see 发送单聊消息
     */
    public function sendSingleMsg($params)
    {
        try {
            $robotConfig = $params['setSingleDingDingMsg'];
            if (isset($params['setSingleChatTextData'])) {
                if (!isset($params['setSingleChatTextData']['content'])) throw new \Exception('The request parameter [content] cannot be empty!');
                $msgParam = json_encode(['content' => $params['setSingleChatTextData']['content']]);
                $type     = 'sampleText';
            } elseif (isset($params['setSingleChatMarkdownData'])) {
                if (!isset($params['setSingleChatTextData']['title']) || !isset($params['setSingleChatTextData']['text'])) throw new \Exception('The request parameter [title|text] cannot be empty!');
                $msgParam = json_encode(['title' => $params['setSingleChatMarkdownData']['title'], 'text' => $params['setSingleChatMarkdownData']['text']]);
                $type     = 'sampleMarkdown';
            } else {
                throw new \Exception('Please set the parameter [content] | [title and text]!');
            }
            $authInfo['appkey']    = $robotConfig['appKey'];
            $authInfo['appsecret'] = $robotConfig['appSecret'];
            $robotToken            = $this->getSingleRobotToken($authInfo);
            // 钉钉机器人单聊批量发送
            $header             = ['x-acs-dingtalk-access-token:' . $robotToken, 'Content-Type:application/json'];
            $url                = "https://api.dingtalk.com/v1.0/robot/oToMessages/batchSend";
            $code               = 200;
            $msg                = '发送成功!';
            $jsonData['result'] = 'success';
            if ($msgParam) {
                $userConfig         = [
                    'robotCode' => $robotConfig['appKey'],
                    'userIds'   => $robotConfig['userIds'],
                    'msgKey'    => $type,
                    'msgParam'  => $msgParam
                ];
                $response           = $this->curlRequest($url, $userConfig, 'json', 'post', $header);
                $jsonData['result'] = $response;
            }
            return $this->returnJson($code, $msg, $jsonData);
        } catch (\Exception $e) {
            return $this->returnJson(4004, $e->getMessage());
        }
    }

    /**
     * @param $contentInfo
     * @param $params
     * @see 重组json数据
     * @author Carver
     */
    public function getJsonData(&$contentInfo, $params)
    {
        $msgType = $params['msgType'];
        switch ($msgType) {
            case 1:
                $type = 'text';
                break;
            case 2:
                $type = 'markdown';
                break;
            case 3:
                $type = 'actionCard';
                break;
        }
        $contentInfo['msgtype'] = $type;
        if (in_array($msgType, [1, 2])) {
            $contentInfo['at'] = [
                'atMobiles' => $params['msgReceiver'],
                'isAtAll'   => false
            ];
        }
    }

    /**
     * @param $params
     * @return string
     * @throws \Exception
     * @author Carver
     * @see 生成 webhook
     */
    public function makeWebHookUrl($params)
    {
        if (!isset($params['secret']) || !isset($params['accessToken'])) throw new \Exception('Missing request parameter [secret | accessToken]!');
        $apiBaseUrl = $this->apiBaseUrl;
        list($s1, $s2) = explode(' ', microtime());
        $timestamp    = (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
        $secret       = $params['secret'];
        $signStr      = $this->getDinDingWebHookSign($timestamp, $secret);
        $base_url     = $apiBaseUrl;
        $access_token = $params['accessToken'];
        return "{$base_url}?access_token={$access_token}&timestamp={$timestamp}&sign={$signStr}";
    }

    /**
     * @param $params
     * @return mixed
     * @throws \Exception
     * @author Carver
     * @see 设置 webhook
     */
    public function setWebHookUrl($params)
    {
        if (!isset($params['webHook'])) throw new \Exception('Missing request parameter [webHook]!');
        return $params['webHook'];
    }

    /**
     * @param string $timestamp
     * @param string $secret
     * @return false|string
     * @author Carver
     * @see 指定算法进行加签
     */
    public function getDinDingWebHookSign($timestamp = '', $secret = '')
    {
        $data    = $timestamp . "\n" . $secret;
        $signStr = base64_encode(hash_hmac('sha256', $data, $secret, true));
        $signStr = utf8_encode(urlencode($signStr));
        return $signStr;
    }

    /**
     * @param $authInfo
     * @return mixed
     * @throws \Exception
     * @see 获取单聊机器人授权
     */
    public function getSingleRobotToken($authInfo)
    {
        //获取token
        $token_url = http_build_query($authInfo);
        $token_url = 'https://oapi.dingtalk.com/gettoken?' . $token_url;
        $result    = $this->curlRequest($token_url);
        $result    = json_decode($result, true);
        if ($result && !isset($result['access_token'])) throw new \Exception('Robot authorization failed!', 4005);
        return $result['access_token'];
    }


    /**
     * @param string $url
     * @param null $data
     * @param string $type
     * @param string $method
     * @param string[] $header
     * @param bool $https
     * @param int $timeout
     * @return bool|string
     * @see 客户端请求方法
     */
    public function curlRequest($url = '', $data = null, $type = 'json', $method = 'get', $header = array("Content-Type: application/json"), $https = true, $timeout = 120)
    {
        $ch = curl_init();                             //初始化
        curl_setopt($ch, CURLOPT_URL, $url);           //访问的URL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//只获取页面内容，但不输出
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//https请求 不验证证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//https请求 不验证HOST
        }
        //默认是GET请求
        switch (strtoupper($method)) {
            case 'GET':
                curl_setopt($ch, CURLOPT_POST, 0);//请求方式为get请求
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);//请求方式为post请求
                break;
            case 'PUT':
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); //设置请求方式
                break;
        }
        if ($data) {
            switch ($type) {
                case 'json':
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));//请求数据
                    break;
                default:
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//请求数据
            }
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //curl_setopt($ch, CURLOPT_HEADER, false);//设置不需要头信息
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //模拟的header头
        $result = curl_exec($ch);                      //执行请求
        curl_close($ch);                               //关闭curl，释放资源
        return $result;
    }


    /**
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return false|string
     * @see 返回json数据
     */
    public function returnJson($code = 0, $msg = '', $data = [])
    {
        return json_encode(['code' => $code, 'msg' => $msg, 'data' => $data]);
    }

}
