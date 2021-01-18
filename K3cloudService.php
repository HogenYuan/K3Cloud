<?php

require_once "./trait/K3cloudBuild.php";
require_once "./exception/K3cloudException.php";
require_once "./request/RequestService.php";


/**
 * 金蝶K3cloud Web api接口类
 * Class K3cloudService
 *
 * @author  Hogen
 * @package K3cloud
 */
class K3cloudService extends RequestService
{
    use K3cloudBuild;

    //API文档
    const LOGIN_API     = 'Kingdee.BOS.WebApi.ServicesStub.AuthService.ValidateUser.common.kdsvc';
    const SAVE_API      = 'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.save.common.kdsvc';
    const BATCHSAVE_API = 'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.batchSave.common.kdsvc';
    const VIEW_API      = 'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.view.common.kdsvc';
    const SUBMIT_API    = 'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.Submit.common.kdsvc';
    const AUDIT_API     = 'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.Audit.common.kdsvc';
    const PUSH_API      = 'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.Push.common.kdsvc';
    const DRAFT_API     = 'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.draft.common.kdsvc';
    const DELETE_API    = 'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.delete.common.kdsvc';
    const GETBILL_API   = 'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.ExecuteBillQuery.common.kdsvc';
    const QUERYINFO_API = 'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.QueryBusinessInfo.common.kdsvc';
    // 金蝶域名或者IP地址;/K3Cloud/
    public $cloudUrl = '';
    //登陆数据
    public $loginData = [];
    //语言ID,中文2052,繁体3076，英文1033
    public $LCID = 2052;

    /**
     * 默认每次调用接口前先login，因为不知道login的时效性，没有做单例模式。
     *
     * @param string $cloudUrl URL
     * @param string $acctID   帐套ID
     * @param string $username 用户名
     * @param string $password 密码
     * @param int    $LCID     语言ID
     */
    public function __construct(string $cloudUrl, string $acctID, string $username, string $password, int $LCID = 2052)
    {
        $this->cloudUrl  = rtrim($cloudUrl, "/") . "/";
        $this->LCID      = $LCID;
        $this->loginData = [$acctID, $username, $password, $this->LCID];
        $this->login();
    }

    /**
     * 登陆
     *
     * @return array
     */
    public function login()
    {
        $this->_cookieJar     = @tempnam('./tmp', 'CloudSession'); //保存登录后的session
        $this->_requestAction = 'login';
        $res                  = $this->_handle(self::LOGIN_API, $this->loginData, true);
        if (!$res['ack']) {
            return $res;
        }
        return $this->success();
    }

    /**
     * 查看
     *
     * @param string $formID
     * @param array  $data
     *
     * @return mixed
     * @example
     * $formID = "STK_TRANSFEROUT",
     * $data = [
     * [
     * "Id"=>"1",
     * "Number"=>"1",
     * ]
     * ];
     */
    public function toView(string $formID, array $data)
    {
        return $this->form($formID)->data($data)->view();
    }

    /**
     * 保存
     *
     * @param string $formID
     * @param array  $model model属性
     * @param array  $data  需要替代的data属性
     *
     * @return mixed
     * @example
     * $formID = "AR_receivable",
     * $data = [
     * "Creator" => "",
     * "NeedUpDateFields" => [],
     * "NeedReturnFields" => [],
     * "IsDeleteEntry" => "true",
     * "SubSystemId" => "",
     * "IsVerifyBaseDataField" => "false",
     * "IsEntryBatchFill" => "true",
     * "ValidateFlag" => "true",
     * "NumberSearch" => "true",
     * "InterationFlags" => "",
     * "IsAutoSubmitAndAudit" => "false",
     * ],
     * $model = [
     * ···(每个model的数据字段不一样，具体参考文档 https://open.kingdee.com/K3Cloud/Open/ApiCenterReportDetail.aspx)
     * ];
     */
    public function toSave(string $formID, array $model, array $data = [])
    {
        return $this->form($formID)->data($data)->model($model)->save();
    }

    /**
     * 批量保存
     *
     * @param string $formID
     * @param array  $model model属性
     * @param array  $data  需要替代的data属性
     *
     * @return mixed
     * @example
     * $formID = "AR_receivable",
     * $data = [
     * "Creator" => "",
     * "NeedUpDateFields" => [],
     * "NeedReturnFields" => [],
     * "IsDeleteEntry" => "true",
     * "SubSystemId" => "",
     * "IsVerifyBaseDataField" => "false",
     * "IsEntryBatchFill" => "true",
     * "ValidateFlag" => "true",
     * "NumberSearch" => "true",
     * "InterationFlags" => "",
     * "IsAutoSubmitAndAudit" => "false",
     * ],
     * $model = [
     * $modelArr1,$modelArr2 (每个model的数据字段不一样，具体参考文档 https://open.kingdee.com/K3Cloud/Open/ApiCenterReportDetail.aspx)
     * ];
     */
    public function toBatchSave(string $formID, array $model, array $data = [])
    {
        return $this->form($formID)->data($data)->model($model)->BatchSave();
    }

    /**
     * 删除
     *
     * @param string $formID
     * @param array  $data
     *
     * @return array|bool
     * @example
     * $formID = "STK_TRANSFEROUT",
     * $data = [
     * [
     * "Ids"=>"1,2,3",
     * "Numbers"=>[1,2,3],
     * ]
     * ];
     */
    public function toDelete(string $formID, array $data)
    {
        return $this->form($formID)->data($data)->delete();
    }

    /**
     * 提交并审核
     *
     * @param string $FORMID
     * @param array  $data
     *
     * @return bool
     */
    public function saveAndAudit(string $FORMID, array $data)
    {
        $idList     = "";
        $numberList = [];
        foreach ($data as $v) {
            if (isset($v["Id"])) {
                $idList = $idList . "," . $v['Id'];
            }
            if (isset($v["Number"])) {
                $numberList[] = $v['Number'];
            }
        }
        $saveList = [
            "Ids"     => ltrim($idList, ','),
            "Numbers" => $numberList,
        ];
        //提交
        $this->toSubmit($FORMID, $saveList);
        //审核
        $this->toAudit($FORMID, $saveList);
        return true;
    }

    /**
     * 提交
     *
     * @param string $formID
     * @param array  $data
     *
     * @return array|bool
     */
    public function toSubmit(string $formID, array $data)
    {
        return $this->form($formID)->data($data)->submit();
    }

    /**
     * 审核
     *
     * @param string $formID
     * @param array  $data
     *
     * @return array|bool
     */
    public function toAudit(string $formID, array $data)
    {
        return $this->form($formID)->data($data)->Audit();
    }

    /**
     * 下推
     *
     * @param string $formID
     * @param array  $data
     *
     * @return array|bool
     */
    public function toPush(string $formID, array $data)
    {
        return $this->form($formID)->data($data)->Push();
    }

    /**
     * 单据查询
     *
     * @param string $formID
     * @param string $field 查询的字段名
     * @param array  $data  需要替代的data属性
     * @param int    $limit 返回个数，0位不限制
     *
     * @return array
     * @example
     * $formID = "STK_TRANSFEROUT",
     * $field = "FID,FQty,FBaseQty",
     * $data = [
     * [
     * "FieldKeys" => "",     // 获取字段参数，内码，供应商id，物料id,物料编码，物料名称 ex:FID,FBaseQty,FMaterialId
     * "FilterString" => "", // 过滤条件 ex:"FMaterialId.FNumber='HG_TEST'"
     * "OrderString" => "", // 排序条件 ex:FID ASC
     * "TopRowCount" => 0, // 最多允许查询的数量，0或者不要此属性表示不限制
     * "StartRow" => 0,   // 分页取数开始行索引，从0开始，例如每页10行数据，第2页开始是10，第3页开始是20
     * ]
     * ],
     * $limit = 10,
     */
    public function toGetBill(string $formID, string $field, array $data = [], int $limit = 0)
    {
        return $this->form($formID)->field($field)->data($data)->limit($limit)->getBill();
    }

    /**
     * 查看 - 链式
     *
     * @return array|mixed
     */
    public function view()
    {
        $defaultData          = [
            "CreateOrgId" => 0,
            "Number"      => "",
            "Id"          => "",
        ];
        $this->_requestAction = __FUNCTION__;
        $res                  = $this->_handle(self::SUBMIT_API, $defaultData);
        if (!$res['ack']) {
            return $res;
        }
        return $this->success($res['Result']['Result']);
    }

    /**
     * 保存 - 链式
     *
     * @return array|mixed
     */
    public function save()
    {
        $defaultData          = [
            "Creator"               => "",
            "NeedUpDateFields"      => [],
            "NeedReturnFields"      => [],
            "IsDeleteEntry"         => "true",
            "SubSystemId"           => "",
            "IsVerifyBaseDataField" => "false",
            "IsEntryBatchFill"      => "true",
            "ValidateFlag"          => "true",
            "NumberSearch"          => "true",
            "InterationFlags"       => "",
            "IsAutoSubmitAndAudit"  => "false",
        ];
        $this->_requestAction = __FUNCTION__;
        $res                  = $this->_handle(self::SAVE_API, $defaultData);
        if (!$res['ack']) {
            return $res;
        }
        return $this->success($res['Result']['ResponseStatus']['SuccessEntitys']);
    }

    /**
     * 批量保存 - 链式
     *
     * @return array|mixed
     */
    public function batchSave()
    {
        $defaultData          = [
            "Creator"               => "",
            "NeedUpDateFields"      => [],
            "NeedReturnFields"      => [],
            "IsDeleteEntry"         => "true",
            "SubSystemId"           => "",
            "IsVerifyBaseDataField" => "false",
            "IsEntryBatchFill"      => "true",
            "ValidateFlag"          => "true",
            "NumberSearch"          => "true",
            "InterationFlags"       => "",
            "IsAutoSubmitAndAudit"  => "false",
        ];
        $this->_requestAction = __FUNCTION__;
        $res                  = $this->_handle(self::BATCHSAVE_API, $defaultData);
        if (!$res['ack']) {
            return $res;
        }
        return [
            'ack'  => $res['ack'],
            'data' => $res['Result']['ResponseStatus'],
        ];
//        return $this->success($res['Result']['ResponseStatus']['SuccessEntitys']);
    }

    /**
     * 提交 - 链式
     *
     * @return array|mixed
     */
    public function submit()
    {
        $defaultData          = [
            "CreateOrgId" => 0,
            "Numbers"     => [],
            "Ids"         => "",
        ];
        $this->_requestAction = __FUNCTION__;
        $res                  = $this->_handle(self::SUBMIT_API, $defaultData);
        if (!$res['ack']) {
            return $res;
        }
        return $this->success();
    }

    /**
     * 审核 - 链式
     *
     * @return array|mixed
     */
    public function audit()
    {
        $defaultData          = [
            "CreateOrgId" => 0,
            "Numbers"     => [],
            "Ids"         => "",
        ];
        $this->_requestAction = __FUNCTION__;
        $res                  = $this->_handle(self::AUDIT_API, $defaultData);
        if (!$res['ack']) {
            return $res;
        }
        return $this->success();
    }

    /**
     * 下推 - 链式
     *
     * @return array|mixed
     */
    public function push()
    {
        $defaultData          = [
            "CreateOrgId" => 0,
            "Numbers"     => [],
            "Ids"         => "",
        ];
        $this->_requestAction = __FUNCTION__;
        $res                  = $this->_handle(self::PUSH_API, $defaultData);
        if (!$res['ack']) {
            return $res;
        }
        return $this->success();
    }

    /**
     * 删除 - 链式
     *
     * @return array|bool
     */
    public function delete()
    {
        $defaultData          = [
            "CreateOrgId" => 0,
            "Numbers"     => [],
            "Ids"         => "",
        ];
        $this->_requestAction = __FUNCTION__;
        $res                  = $this->_handle(self::DELETE_API, $defaultData);
        if (!$res['ack']) {
            return $res;
        }
        return $this->success();
    }

    /**k
     * 单据查询 - 链式
     *
     * @return array
     */
    public function getBill(): array
    {
        $defaultData          = [
            'FormId'       => $this->_formID,
            "FieldKeys"    => "",     // 获取字段参数，内码，供应商id，物料id,物料编码，物料名称 ex:FID,FBaseQty,FMaterialId
            "FilterString" => "", // 过滤条件 ex:"FMaterialId.FNumber='HG_TEST'"
            "OrderString"  => "", // 排序条件 ex:FID ASC
            "TopRowCount"  => 0, // 最多允许查询的数量，0或者不要此属性表示不限制
            "StartRow"     => 0,   // 分页取数开始行索引，从0开始，例如每页10行数据，第2页开始是10，第3页开始是20
            "Limit"        => $this->_limit,    // 分页取数每页允许获取的数据，最大不能超过200
        ];
        $fields               = $this->_fields;
        $this->_requestAction = __FUNCTION__;
        $res                  = $this->_handle(self::GETBILL_API, $defaultData);
        if (!$res['ack']) {
            return $res;
        }
        //整理格式
        $newRes = [];
        $fields = explode(",", $fields);
        unset($res['ack']);
        foreach ($res as $k => $each) {
            foreach ($each as $i => $v) {
                $field              = $fields[$i];
                $newRes[$k][$field] = $v;
            }
        }
        return $this->success($newRes);
    }

    /**
     * 处理请求
     *
     * @param string $api
     * @param array  $defaultData
     * @param bool   $isLogin
     *
     * @return array|mixed
     */
    private function _handle(string $api, array $defaultData = [], bool $isLogin = false)
    {
        try {
            if (!$isLogin) {
                $data     = $this->_formatData($defaultData);
                $postData = $this->_createPostData($data);
                //还原初始化属性
                $this->_initData();
            } else {
                $postData = $this->_createPostData($defaultData);
            }
            $url = $this->cloudUrl . $api;

            $return = $this->_curl($url, $postData, $isLogin);
            $res    = json_decode($return["result"], true);
            if (!$res) {
                $erroInfo = [
                    'ack'           => false,
                    'message'       => "服务错误",
                    'errorCode'     => 500,
                    'errorType'     => '数据解析错误',
                    'requestAction' => $this->_requestAction,
                ];
                throw new K3cloudException($erroInfo);
            }
            if ($this->_requestAction == 'login') {
                if ($res['LoginResultType'] != 1) {
                    $erroInfo = [
                        'ack'           => false,
                        'message'       => $res['Message'],
                        'errorCode'     => 500,
                        'errorType'     => '接口请求错误5102',
                        'requestAction' => $this->_requestAction,
                    ];
                    throw new K3cloudException($erroInfo);
                }
            } elseif ($this->_requestAction == 'getBill') {
                if (isset($res[0][0]['Result']['ResponseStatus']['IsSuccess'])
                    && !$res[0][0]['Result']['ResponseStatus']['IsSuccess']) {
                    $res      = $res[0][0];
                    $erroInfo = [
                        'ack'           => false,
                        'message'       => $res['Result']['ResponseStatus']['Errors'],
                        'errorCode'     => $res['Result']['ResponseStatus']['ErrorCode'],
                        'errorType'     => '接口请求错误5103',
                        'requestAction' => $this->_requestAction,
                    ];
                    throw new K3cloudException($erroInfo);
                }
            }
            $res['ack'] = true;
            return $res;
        } catch (K3cloudException $e) {
            return [
                'ack'           => false,
                'message'       => $e->getErrorMessage(),
                'code'          => $e->getErrorCode(),
                'errType'       => $e->getErrorType(),
                'requestAction' => $e->getRequestAction(),
                'data'          => [],
            ];
        }
    }

    /**
     * 整理数据
     *
     * @param $data
     *
     * @return array
     */
    private function _formatData($data): array
    {
        //替换data属性
        if (!empty($this->_data)) {
            foreach ($this->_data as $k => $v) {
                $data[ucfirst($k)] = $v;
            }
        }
        //替换Model属性
        if (!empty($this->_model)) {
            $data["Model"] = $this->_model;
        }

        //查单据时,强替换属性
        if ($this->_requestAction == "getBill") {
            if ($this->_formID != "") {
                $data["FormId"] = $this->_formID;
                $this->_formID  = "";
            }
            if ($this->_fields != "") {
                $data["FieldKeys"] = $this->_fields;
            }
            if ($this->_limit > 0) {
                $data["Limit"] = $this->_limit;
            }
        }
        //整理格式
        if ($this->_formID == "") {
            //有顺序规定，不能调转
            $postData = [
                $data,
            ];
        } else {
            $postData = [
                $this->_formID,
                $data,
            ];
        }
        return $postData;
    }

    /**
     * 还原初始化属性
     * 因为不知道login的时效性，没有做单例模式，还原属性避免一个实例复用导致的数据混乱
     */
    private
    function _initData()
    {
        $this->_formID = "";
        $this->_limit  = 0;
//        $this->_requestAction = "";
        $this->_fields = "";
        $this->_model  = [];
        $this->_data   = [];
    }

    /**
     * 构造API请求格式
     *
     * @param $parameters
     *
     * @return string
     */
    private
    function _createPostData(
        $parameters
    ): string {
        return json_encode([
            'format'     => 1,
            'useragent'  => 'ApiClient',
            'rid'        => $this->_createGUID(),
            'parameters' => $parameters,
            'timestamp'  => date('Y-m-d'),
            'v'          => '1.0',
        ]);
    }

    /**
     * 生成GUID
     *
     * @return Closure
     */
    private
    function _createGUID()
    {
        return function () {
            $charid = strtoupper(md5(uniqid(mt_rand(), true)));
            $hyphen = chr(45); // "-"
            // "}"
            return chr(123) // "{"
                . substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12)
                . chr(125);
        };
    }


    /**
     * TODO！先不开发 暂存
     *
     * @return array
     */
    public
    function draft()
    {
        $defalutData          = [];
        $this->_requestAction = __FUNCTION__;
        $res                  = $this->_handle(self::DRAFT_API, $defalutData);
        if ($res['Result']['ResponseStatus']['IsSuccess'] == false) {
            return [
                'code'    => $res['Result']['ResponseStatus']['ErrorCode'],
                'message' => $res['Result']['ResponseStatus']['Errors'],
            ];
        }
        return $this->success($res);
    }

    /**
     * TODO！跑不通 元数据查询
     *
     * @return array
     */
    public
    function queryInfo()
    {
        $defaultData          = [];
        $this->_requestAction = __FUNCTION__;
        $res                  = $this->_handle(self::QUERYINFO_API, $defaultData);
        if ($res['Result']['ResponseStatus']['IsSuccess'] == false) {
            return [
                'code'    => $res['Result']['ResponseStatus']['ErrorCode'],
                'message' => $res['Result']['ResponseStatus']['Errors'],
            ];
        }
        return $this->success($res);
    }
}