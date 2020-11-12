<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "K3cloudService.php";
require_once "./test/TestJd.php";


$client = new K3cloudService("http://127.0.0.1/k3cloud/", "帐套ID", "登录名", "密码");
//$client = new K3cloudService("http://host.docker.internal:81/k3cloud/", "", "Administrator", "888888");

//$res = $client->login();
//var_dump($res);
//testBill($client,'STK_TRANSFERIN','FID,FbillNo,FOwnerTypeIdHead,FOwnerID,FQty');
//testBill($client, 'BD_MATERIAL', 'FName,FNumber');
//testBdStock($client);
//testStkTransferOut($client);
//testStkTransferDirect($client);
//testHsAdjustmentBill($client);
testBdCustomerBill($client);

//仓库 - 查询
function testBdStockBill(\K3cloudService $client)
{
    $formID = 'BD_STOCK';
    $fields = "FStockId,FName,FNumber,FCreateDate,FStockProperty,FStockStatusType ,FCreateOrgId,FUseOrgId";
    $res = $client->toGetBill($formID, $fields, [], 10);
    var_dump($res);
}

//物料 - 查询
function testBdMaterialBill(\K3cloudService $client)
{
    $formID = 'BD_MATERIAL';
    $fields = "FMATERIALID,FNumber,FName,FForbidStatus,FCreateOrgId,FUseOrgId,FCreateDate,";
    $res = $client->toGetBill($formID, $fields, [], 10);
    var_dump($res);
}

//客户 - 查询
function testBdCustomerBill(\K3cloudService $client)
{
    $formID = 'BD_Customer';
    $fields = "FCUSTID,FNumber,FName,FForbidStatus,FCreateOrgId,FUseOrgId,FCreateDate";
    $res = $client->toGetBill($formID, $fields, [], 10);
    //    $res = $client->form($formID)->field($fields)->limit(10)->getBill();
    var_dump($res);
}

//即时库存 - 查询
function testStkInventoryBill(\K3cloudService $client)
{
    $formID = 'STK_Inventory';
    $fields = "FID,FQty,FBaseQty";
    $res = $client->toGetBill($formID, $fields, [], 10);
    var_dump($res);
}

//应收单
function testArReceivable(\K3cloudService $client)
{
    $formID = 'AR_receivable';
    $saveList = TestJd::testArReceivableSave($client, $formID, true);
//    var_dump($saveList);
    testApi($client, $formID, $saveList);
}

//仓库
function testBdStock(\K3cloudService $client)
{
    $formID = 'BD_STOCK';
    $saveList = TestJd::testBdStockSave($client, $formID, true);
//    var_dump($saveList);
    testApi($client, $formID, $saveList);
}

//物料
function testBdMaterial(\K3cloudService $client)
{
    $formID = 'BD_MATERIAL';
//    $res = $client->toDelete($formID, [
//        "Ids" => 110023
//    ]);
//    var_dump($res);
    $saveList = TestJd::testBdMaterialSave($client, $formID, false);
    testApi($client, $formID, $saveList);
}

//客户
function testBdCustomer(\K3cloudService $client)
{
    $formID = 'BD_Customer';
    $saveList = TestJd::testBdCustomerSave($client, $formID, true);
//    var_dump($saveList);
    testApi($client, $formID, $saveList);
}

//直接调拨单
function testStkTransferDirect(\K3cloudService $client)
{
    $formID = 'STK_TransferDirect';
    $saveList = TestJd::testStkTransferDirectSave($client, $formID, true);
//    var_dump($saveList);
    testApi($client, $formID, $saveList);
}

//成本调整单
function testHsAdjustmentBill(\K3cloudService $client)
{
    $formID = 'HS_AdjustmentBill';
    $saveList = TestJd::testHsAdjustmentBillSave($client, $formID, true);
//    var_dump($saveList);
    testApi($client, $formID, $saveList);
}

//分布式调出单
function testStkTransferOut(\K3cloudService $client)
{
    $formID = 'STK_TRANSFEROUT';
    $saveList = TestJd::testStkTransferOutSave($client, FALSE);
    //批量新增
    testApi($client, $formID, $saveList);
}

//分布式调入单
function testStkTransferIn(\K3cloudService $client)
{
    $formID = 'STK_TRANSFERIN';
    $saveList = TestJd::testStkTransferInSave($client, false);
    testApi($client, $formID, $saveList);
}

//其他出库单
function testStkMisDelivery(\K3cloudService $client)
{
    $formID = 'STK_MisDelivery';
    $saveList = TestJd::testStkMisDeliverySave($client, true);
    testApi($client, $formID, $saveList);
}

//销售退款单
function testReturnStock(\K3cloudService $client)
{
    $formID = 'SAL_RETURNSTOCK';
    $saveList = TestJd::testSalReturnStockSave($client, false);
    testApi($client, $formID, $saveList);
}

//销售出库单
function testSalOutStock(\K3cloudService $client)
{
    $formID = 'SAL_OUTSTOCK';
    $saveList = TestJd::testSalOutStockSave($client, true);
    testApi($client, $formID, $saveList);
}

//表单
function testBill(\K3cloudService $client, string $formID, string $fields)
{
    $res = $client->toGetBill($formID, $fields, [], 10);
    //    $res = $client->form($formID)->field($fields)->limit(10)->getBill();
    var_dump($res);
}

//输出添加，查看，删除接口
function testApi(\K3cloudService $client, string $formID, $saveList)
{
    var_dump($saveList);
    if (isset($saveList['data'])) {
        var_dump(TestJd::testView($client, $formID, $saveList['data']));
        var_dump(TestJd::testDelete($client, $formID, $saveList['data']));
    }
}




