<?php
/*********************************************************************
    TicketData.php
    Dixon
    2017-9-4
    
    从数据库检索数据
    
**********************************************************************/
//调用连接数据库
require('MysqlConn.php');

$startDate=$_POST['startDate'];
$endDate=$_POST['endDate'];

if ($startDate && $endDate) {
    $mSelectTicketID = "select ticket_id,ticket_number,created,status_id from view2 where created between '".$startDate."' and '".$endDate."' and thread_type='M' group by ticket_id";
} else {
    $mYesterday = date("Y-m-d",strtotime("-1 day"));
    //$mYesterday = '2017-06-05';
    //前一天新建的ticket id
    $mSelectTicketID = "select ticket_id,ticket_number,created,status_id from view2 where created like '".$mYesterday."%' and thread_type='M' group by ticket_id";
}
$result = mysql_query($mSelectTicketID);

//保存ticket个数
$mCount = 0;

//保存无效ticket的number
$invalid = array();

/***********************************************************
    二维数组，保存所有ticket信息格式
    Array
    (
        [0] => Array
        (
            [0] => 18215                     ID
            [1] => 4800663                   Number
            [2] => 2                         Status
            [3] => 2017-09-04 00:48:39       创建时间
            [4] => 2017-09-04 07:09:45       首次跟进时间
            [5] => Daniel Ding               跟进人
            [6] => 381.1                     响应时间（分钟）
            [7] => 2017-09-04 07:10:35       Resolve时间
            [8] => 0.83                      服务时间（分钟）
            [9] => true                      有效ticket
            [10]=> 'TICKET'                  ticket类型
        )
    )
***********************************************************/
$mID = array();
$mValidID = array();

//ISO类型的topic_id号
$isoType = array(12,13,14);

//循环,保存ticket的id，number，创建时间,状态ID
while($row = mysql_fetch_array($result)) {
  $mID[$mCount] = array($row['ticket_id'], $row['ticket_number'], $row['status_id'], $row['created']);
  $mCount++;
}
$mCount = count($mID);

//循环
for($i=0; $i<$mCount; $i++) {
    //保存ticket的首次响应时间,响应间隔,负责人
    $mSelectFirstResponse = "select updated,poster,topic_id from view2 where ticket_id=".$mID[$i][0]." and thread_type='R' and poster!='Yuki lu' limit 1";
    $result = mysql_query($mSelectFirstResponse);
    $row = mysql_fetch_array($result);
    $mID[$i][4] = $row['updated'];
    $mID[$i][5] = $row['poster'];
    //保存ticket类型
    $mID[$i][10] = 'TICKET';
    if (in_array($row['topic_id'], $isoType)) {
        $mID[$i][10] = 'ISO';
    }
    if($mID[$i][4]) {
        $mID[$i][6] = number_format((strtotime($mID[$i][4]) - strtotime($mID[$i][3]))/60, 2);
    } else {
        $mID[$i][6] = 'unknown';
    }

    //保存ticket的Resolve时间,服务时间
    $mSelectClosed = "select updated from view2 where ticket_id=".$mID[$i][0]." and thread_type='N' and body like 'Status changed from Open to Resolved %' and poster!='Yuki lu' limit 1";
    $result = mysql_query($mSelectClosed);
    $row = mysql_fetch_array($result);
    $mID[$i][7] = $row['updated'];
    if($mID[$i][4] && $mID[$i][7] && strtotime($mID[$i][7]) >= strtotime($mID[$i][4])) {
        $mID[$i][8] = number_format((strtotime($mID[$i][7]) - strtotime($mID[$i][4]))/60, 2);
    } else {
        $mID[$i][8] = 'unknown';
    }

    //保存ticket有效性
    $mSelectValid = "select body from view2 where ticket_id=".$mID[$i][0];
    $result = mysql_query($mSelectValid);
    $mID[$i][9] = true;
    while($row = mysql_fetch_array($result)) {
        if (stristr($row['body'], 'INVALID TICKET')) {
            $mID[$i][9] = false;
            array_push($invalid, $mID[$i][1]);
            break;
        }
    }

    //过滤批量关闭的ticket,即有跟进人或者状态是open，保存到 $mValidID
    if ($mID[$i][5] || $mID[$i][2] == 1) {
        array_push($mValidID,$mID[$i]);
    }
}

//print_r($mID);
//print_r($mValidID);
$mValidCount=count($mValidID);

//调用断开数据库连接
require('MysqlDisConn.php');





/***********************************************************
    二维数组，保存工程师格式信息
    Array
    (
        [0] => Array
        (
            [0] => Dodo Xie          工程师名字
            [1] => 14                跟进数量
            [2] => 13                完成数量
            [3] => 92.857142857143   完成率
            [4] => 87.04880952381    平均响应时间
            [5] => 41.583333333333   平均服务时间
        )
    )
***********************************************************/
$mST = array();

//循环，统计昨日处理的工程师名字
$tmpName = array();
for($i=0; $i<$mCount; $i++) {
    $tmp = $mID[$i][5];
    if(!in_array($tmp, $tmpName) && $tmp) {
        array_push($tmpName, $tmp);
    }
}

//循环
$sts = count($tmpName);
for($i=0; $i<$sts; $i++) {
    $tmpOwnNum = 0;
    $tmpDoneNum = 0;
    $tmpResponseTime = 0;
    $tmpServiceTime = 0;
    for($j=0; $j<$mCount; $j++) {
        if ($mID[$j][5] == $tmpName[$i]) {
            $tmpOwnNum++;
            if ($mID[$j][7]) {
                $tmpDoneNum++;
                $tmpServiceTime += $mID[$j][8];
            }
            if ($mID[$j][6]) {
                $tmpResponseTime += $mID[$j][6];
            }
        }
    }
    $mST[$i][0] = $tmpName[$i];
    $mST[$i][1] = $tmpOwnNum;
    $mST[$i][2] = $tmpDoneNum;
    $mST[$i][3] = number_format($tmpDoneNum / $tmpOwnNum * 100, 2);
    $mST[$i][4] = number_format($tmpResponseTime / $tmpOwnNum , 2);
    $mST[$i][5] = number_format($tmpServiceTime / $tmpDoneNum , 2);
}

//print_r($mST);



/***********************************************************
    二维数组，保存ticket类型信息
    Array
    (
        [0] => Array
        (
            [0] => ISO     ticket类型
            [1] => 54      跟进数量
            [2] => 46      完成数量
            [3] => 0       耗时2天完成
            [4] => 0       耗时2天以上完成
        )
    )
***********************************************************/
$mTYPE = array(
  array("ISO",0,0,0,0),
  array("TICKET",0,0,0,0)
);

//循环，统计ticket类型
for($i=0; $i<$mValidCount; $i++) {
    if ($mValidID[$i][10] == 'ISO') {
        $mTYPE[0][1]++;
        if ($mValidID[$i][2] > 1) {
            $mTYPE[0][2]++;
        }
        if ($mValidID[$i][8] >1440 && $mValidID[$i][8] <2880) {
            $mTYPE[0][3]++;
        }
        if ($mValidID[$i][8] >2880) {
            $mTYPE[0][4]++;
        }
    } else {
        $mTYPE[1][1]++;
        if ($mValidID[$i][2] > 1) {
            $mTYPE[1][2]++;
        }
        if ($mValidID[$i][8] >1440 && $mValidID[$i][8] <2880) {
            $mTYPE[1][3]++;
        }
        if ($mValidID[$i][8] >2880) {
            $mTYPE[1][4]++;
        }
    }
}

//print_r($mTYPE);

?>
