<?php
/*
 * 脚本执行步骤：
 * 1、先捞出来a和b表中不一致的数据，先捞出来10条数据。对比两边的open_id数据，然后截图。
 * 2、然后再把这10条数据真正的执行修改和同步算法，然后停掉脚本
 * 3、上述没有问题再整体执行脚本
 * 4、执行脚本命令 nohup php demo1000.php >> demo1000.txt &
 */
require __DIR__ . '/vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
date_default_timezone_set('Asia/Shanghai');
set_time_limit(0);
ini_set('memory_limit', '256M');
echo " the script start ".date('Y-m-d H:i:s').PHP_EOL;

//先把前两天推送**的数据单独记下来
$file = '142.txt';
$handle = fopen($file, "r");
$openIdKeys = array();
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $openIdKeys[trim($line)] = 1;
    }
    fclose($handle);
}


//db连接信息
$servername = "**********************";
$username   = "**********************";
$password   = "**********************";

//审核平台db
$dbname1    = "**********************";
//推审服务db
$dbname2    = "**********************";

$conn1 = new PDO("mysql:host=$servername;dbname=$dbname1", $username, $password);
$conn1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$startTime = '**********************';
$endTime = '**********************';

//1、查审核表中数据
$contentSql = "select ********************** from *** where status = 1 and  create_time >= '$startTime' and create_time < '$endTime'";
echo " **** sql info ".$contentSql." date ".date('Y-m-d H:i:s').PHP_EOL;;
$stmt1 = $conn1->prepare($contentSql);
$stmt1->execute();

$result1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
if (empty($result1)) {
    echo " execute sql after the data not exist, sql = ".$contentSql." date ".date('Y-m-d H:i:s').PHP_EOL;
    exit();
}
//var_dump($result1);exit;
$tcContent       = array();
foreach ($result1 as $row1) {
    $tcContent[$row1['id']] = $row1['third_content_id'];
}

//因表中有41194条数据，划分多组多次执行
$tcIds  = array_chunk(array_keys($tcContent), 3000);

//提前连接推审服务
$conn2 = new PDO("mysql:host=$servername;dbname=$dbname2", $username, $password);
$conn2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//实力化对象
$client = new Client([
    'timeout' => 120.0,
]);

$algorithmApiUrl = '******************************';
$cursorId = 0;
foreach ($tcIds as $tcId) {
    //把每组的数据处理成in要求的格式数据
    $tempStr = '';
    $tempStr = '(';
    foreach($tcId as $v) {
        $tempStr .= "$v,";
    }
    $tempStr = rtrim($tempStr, ',');
    $tempStr .= ')';

    //2、查审核记录表中数据
    $recordSql = " select ********************** from *** where status in (9, 10) and  create_time >= '$startTime'
                                                          and create_time < '$endTime' and content_id in $tempStr ";
    echo " ***** sql info ".$recordSql." date ".date('Y-m-d H:i:s').PHP_EOL;
    $stmt3 = $conn1->prepare($recordSql);
    $stmt3->execute();
    $result3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
//    var_dump($result3);exit;
    if (empty($result3)) {
        echo " execute sql after the data not exist, sql = ".$recordSql." date ".date('Y-m-d H:i:s').PHP_EOL;
        continue;
    }

    foreach ($result3 as $row3) {
        //记录当前执行第几条数据
        $cursorId++;
        //拿到open_id
        $thirdContentId = $tcContent[$row3['content_id']];

        if (strlen($thirdContentId) != 24) {
            echo " open id format error, open_id = ".$thirdContentId." date ".date('Y-m-d H:i:s').PHP_EOL;
            continue;
        }

        if (!isset($row3['status'])) {
            echo "image_list status error, open_id = ".$thirdContentId." date ".date('Y-m-d H:i:s').PHP_EOL;
            continue;
        }

        //3、查推审服务表中数据
        $sql = " select status from *** where open_id = '$thirdContentId' ";
        echo " select **** sql info ".$sql." date ".date('Y-m-d H:i:s').PHP_EOL;
        $stmt2 = $conn2->prepare($sql);
        $stmt2->execute();
        $result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        if (empty($result2)) {
            echo "image_list data not exist, open_id = ".$thirdContentId." date ".date('Y-m-d H:i:s').PHP_EOL;
            continue;
        }

        if (!isset($result2[0]['status'])) {
            echo "the status not exist image_list, open_id = ".$thirdContentId." date ".date('Y-m-d H:i:s').PHP_EOL;
            continue;
        }

        //表示已经执行
        if ($result2[0]['status'] == 2 || $result2[0]['status'] == 3) {
            echo " the image_list has callback, open_id = ".$thirdContentId." date ".date('Y-m-d H:i:s').PHP_EOL;
            continue;
        }

        // 一、执行脚本前，先打印10条数据，记下image_list和tc_audit_record两张表中的数据
        if ( $cursorId<=10 ) {
            echo " the print ten open_id ".$thirdContentId." date ".date('Y-m-d H:i:s').PHP_EOL;
            continue;
        }
        exit;


        //4、修改推审服务表中数据
        $updateSql = " update *** set ********************** where open_id= '$thirdContentId' ";
        echo " update **** sql info ".$updateSql." date ".date('Y-m-d H:i:s').PHP_EOL;

        $stmt2 = $conn2->prepare($updateSql);
        $stmt2->bindParam(':status', $imageStatus);
        $stmt2->bindParam(':audit_time', $auditFinishTime);
        $stmt2->bindParam(':auditor_id', $auditorId);
        $stmt2->bindParam(':reason', $reason);
        $imageStatus = 2;
        if ($row3['status'] != 9) {
            $imageStatus = 3;
        }
        $auditFinishTime = $row3['audit_finish_time'];
        $auditorId       = $row3['auditor_id'];
        $reason          = $row3['comment'];

        $stmt2->execute();
        $rowCount = $stmt2->rowCount();
        echo "update  *****  row data, open_id = ".$thirdContentId." date ".date('Y-m-d H:i:s').PHP_EOL;

        if (isset($openIdKeys[$thirdContentId])) {
            echo " before send data to suanfa, open_id = ".$thirdContentId." date ".date('Y-m-d H:i:s').PHP_EOL;
            continue;
        }

        //5、发送数据给算法
        for ($k=0; $k<3; $k++) {
            try {
                $response = $client->request("post", $algorithmApiUrl, array(
                    'form_params' => array(
                        'open_id'  => trim($thirdContentId),
                        'status'   => $imageStatus == 2 ? 'approved' : 'rejected',
                        'callback' => ''//存储url
                    ),
                ));
                $content = json_decode($response->getBody()->getContents(), true);
                //记录回调结果结果
                if (!empty($content['code']) && $content['code'] == 200) {
                    echo " the return result success the current cursorId is ".$cursorId." open id is ".trim($thirdContentId)." date ".date('Y-m-d H:i:s').PHP_EOL;
                    echo " the current cursorId is ".$cursorId." return suanfa result ".serialize($content)." date ".date('Y-m-d H:i:s').PHP_EOL;
                    break;
                }
            } catch (\Exception $e) {
                //记录异常信息
                echo " the current cursorId is ".$cursorId." exception msg ".$e->getMessage()." date ".date('Y-m-d H:i:s').PHP_EOL;
            }
        }
        //二、等到执行第10条数据结束后，停掉脚本，观察数据是否正常。正常再执行后续流程
        if ($cursorId >= 10) {
            echo " the exit open_id ".$thirdContentId." date ".date('Y-m-d H:i:s').PHP_EOL;
            exit;
        }
    }
}
//手动关闭连接
$conn1 = null;
$conn2 = null;
echo " the script end ".date('Y-m-d H:i:s').PHP_EOL;
