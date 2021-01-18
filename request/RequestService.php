<?php
//namespace ;

/**
 * K3Cloud请求类
 *
 * @author hogen
 */
class RequestService
{
    //缓存
    protected $_cookieJar = '';
    //请求接口名
    protected $_requestAction = "";

    /**
     * response success格式
     *
     * @param array  $data
     * @param string $message
     * @param int    $code
     *
     * @return array
     */
    public function success($data = [], $message = "success", $code = 200)
    {
        return [
            'ack'     => true,
            "code"    => $code,
            "meesage" => $message,
            'data'    => $data,
        ];
    }

    /**
     * response error格式
     *
     * @param array  $data
     * @param string $message
     * @param int    $code
     *
     * @return array
     */
    public function error($message = "error", $code = 500)
    {
        return [
            "code"    => $code,
            "meesage" => $message,
        ];
    }

    /**
     * 调取接口
     *
     * @param string $url
     * @param string $postContent
     * @param bool   $isLogin
     *
     * @return bool|string
     */
    protected function _curl(string $url, string $postContent, bool $isLogin = false)
    {
        $ch = curl_init($url);

        $thisHeader = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postContent),
        ];

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $thisHeader);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postContent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($isLogin) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->_cookieJar);
        } else {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->_cookieJar);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        //最多循环三次
        $requestCount            = 1;
        $return['requestAction'] = $this->_requestAction;
        $return['org']           = $postContent;
        while ($requestCount <= 3) {
            //执行请求
            $return['result'] = curl_exec($ch);

            //curl是否发生错误
            if ($errNo = curl_errno($ch)) {
                $return['ack']       = false;
                $return['errorType'] = 'Internalc Error';
                $return['errorCode'] = 500;
                $return['message']   = 'K3cloud CurlRequestError,ErrNo:' . $errNo . ',Error:' . curl_error($ch);
            } else {
                $return['ack']     = true;
                $return['message'] = 'success';
                break;
            }
            //请求次数累加
            $requestCount++;
        }
        curl_close($ch);

        if (!$return['ack']) {
            throw new \K3cloudException($return);
        }

        return $return;
    }
}