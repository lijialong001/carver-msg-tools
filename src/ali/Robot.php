<?php

namespace Carver\CarverMsgTools\ali;

use Carver\CarverMsgTools\AliBaseRobot;
use AlibabaCloud\SDK\Dyvmsapi\V20170525\Dyvmsapi;
use AlibabaCloud\SDK\Dyvmsapi\V20170525\Models\SingleCallByTtsRequest;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Utils\Utils;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use Darabonba\OpenApi\Models\Config;

class Robot implements AliBaseRobot
{

    //阿里器人配置信息
    const ALI_ROBOT_APP_KEY = '';
    const ALI_ROBOT_SECRET = '';

    public function checkParams($params)
    {
        try {
            $configInfo = [];
            if (empty($params['accessKeyId'])) throw new \Exception('请求参数[accessKeyId]不正确!');
            if (empty($params['accessKeySecret'])) throw new \Exception('请求参数[accessKeySecret]不正确!');
            return $this->returnJson(200, '验证通过!', $configInfo);
        } catch (\Exception $e) {
            return $this->returnJson($e->getCode(), $e->getMessage());
        }
    }

    /**
     * @param $params
     * @return \Illuminate\Http\JsonResponse
     * @desc 机器人电话语音通知 【https://help.aliyun.com/zh/vms/developer-reference/api-dyvmsapi-2017-05-25-singlecallbytts?spm
     * =a2c4g.11186623.0.i22】
     * 限流操作 1 次/分钟、5 次/小时、20 次/24 小时
     */
    public function sendAliCall($params = [])
    {
        try {
            if (count($params) == 1) {
                if (!isset($params['calledNumber'])) throw new \Exception('必须的参数为[calledNumber(string)]');
            } else {
                throw new \Exception('参数不正确，参数说明:【必须的参数为[calledNumber(string)]】');
            }
            // 工程代码泄露可能会导致 AccessKey 泄露，并威胁账号下所有资源的安全性。以下代码示例仅供参考。
            // 建议使用更安全的 STS 方式，更多鉴权访问方式请参见：https://help.aliyun.com/document_detail/311677.html。
            $config = new Config([
                // 必填，请确保代码运行环境设置了环境变量 ALIBABA_CLOUD_ACCESS_KEY_ID。
                "accessKeyId"     => self::ALI_ROBOT_APP_KEY,
                // 必填，请确保代码运行环境设置了环境变量 ALIBABA_CLOUD_ACCESS_KEY_SECRET。
                "accessKeySecret" => self::ALI_ROBOT_SECRET
            ]);
            // Endpoint 请参考 https://api.aliyun.com/product/Dyvmsapi
            $config->endpoint       = "dyvmsapi.aliyuncs.com";
            $client                 = new Dyvmsapi($config);
            $singleCallByTtsRequest = new SingleCallByTtsRequest([
                "calledNumber" => $params['calledNumber'],
                "ttsCode"      => "TTS_225130979"
            ]);
            $runtime                = new RuntimeOptions([]);
            try {
                $resData['code'] = 200;
                $resData['msg']  = 'success';
                $client->singleCallByTtsWithOptions($singleCallByTtsRequest, $runtime);
            } catch (\Exception $error) {
                if (!($error instanceof TeaError)) {
                    $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
                }
                // 此处仅做打印展示，请谨慎对待异常处理，在工程项目中切勿直接忽略异常。
                // 错误 message
                // var_dump($error->message);
                // // 诊断地址
                // var_dump($error->data["Recommend"]);
                Utils::assertAsString($error->message);
                $resData['code'] = 5005;
                $resData['msg']  = $error->message;
            }
            return $this->returnJson($resData['code'], $resData['msg']);
        } catch (\Exception $e) {
            return $this->returnJson($e->getCode(), $e->getMessage());
        }
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
