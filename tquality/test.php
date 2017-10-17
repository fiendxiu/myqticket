<?php
/*********************************************************************
    index.php
    Dixon
    2017-9-4
    
    首页。输出前端页面。
    
**********************************************************************/

//调用从数据库检索数据
require('TicketData.php');

?>

<html>

<head>
<title>Ticket昨日统计</title>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<link rel="stylesheet" type="text/css" href="css/lyz.calendar.css" />
<script type="text/javascript" src="js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="js/lyz.calendar.min.js"></script>

<script>
    $(function () {
        $("#startDate").calendar({
            controlId: "divDate",                                 // 弹出的日期控件ID，默认: $(this).attr("id") + "Calendar"
            speed: 200,                                           // 三种预定速度之一的字符串("slow", "normal", or "fast")或表示动画时长的毫秒数值(如：1000),默认：200
            complement: true,                                     // 是否显示日期或年空白处的前后月的补充,默认：true
            readonly: true,                                       // 目标对象是否设为只读，默认：true
            upperLimit: new Date(),                               // 日期上限，默认：NaN(不限制)
            lowerLimit: new Date("2015/01/01"),                   // 日期下限，默认：NaN(不限制)
            //callback: function () {                             // 点击选择日期后的回调函数
            //    alert("您选择的日期是：" + $("#txtBeginDate").val());
            //}
        });
        $("#endDate").calendar({
            speed: 200,
            upperLimit: new Date(),
            lowerLimit: new Date("2015/01/01"),
        });
    });
</script>
</head>

<body>
    <div id="header">
        <h1> Ticket 统计 </h1>
        <form method="post">
            <input id="startDate" name="startDate" /> -
            <input id="endDate" name="endDate" />
            <input id="btnSubmit" type="submit" value="统计" />
        </form>
        <?php
        if ($startDate && $endDate) {
            echo "<p>".$startDate."~".$endDate." Ticket总数: ".count($mValidID)."</p>";
        } else {
            echo "<p>".$mYesterday." Ticket总数: ".count($mValidID)."</p>";
        }
        
        if ($invalid) {
            echo "无效ticket号：";
            foreach($invalid as $inNum) {
                echo $inNum.",";
            }
        }
        ?>
    </div>

    <div id="container">
        <table>
            <tr>
                <th>Ticket类型</th>
                <th>跟进</th>
                <th>完成</th>
            </tr>
            <?php
            foreach($mTYPE as $type) {
            echo "<tr>
                <td>".$type[0]."</td>
                <td>".$type[1]."</td>
                <td>".$type[2]."</td>
            </tr>";
            }
            ?>
        </table>
        <table>
            <tr>
                <th>工程师</th>
                <th>跟进</th>
                <th>完成</th>
                <th>完成率(%)</th>
                <th>平均响应时间(分钟)</th>
                <th>平均服务时间(分钟)</th>
            </tr>
            <?php
            foreach($mST as $st) {
            echo "<tr>
                <td>".$st[0]."</td>
                <td>".$st[1]."</td>
                <td>".$st[2]."</td>
                <td>".$st[3]."</td>
                <td>".$st[4]."</td>
                <td>".$st[5]."</td>
            </tr>";
            }
            ?>
        </table>
        <table>
            <tr>
                <th>Ticket号</th>
                <th>创建时间</th>
                <th>第一次响应时间</th>
                <th>跟进工程师</th>
                <th>响应时间(分钟)</th>
                <th>服务时间(分钟)</th>
            </tr>
            <?php
            foreach($mValidID as $id) {
            echo "<tr>
                <td>".$id[1]."</td>
                <td>".$id[3]."</td>
                <td>".$id[4]."</td>
                <td>".$id[5]."</td>
                <td>".$id[6]."</td>
                <td>".$id[8]."</td>
            </tr>";
            }
            ?>
        </table>
    </div>

    <div id="footer">
        <p>Copyright © 2015-2017 Fnetlink Customer Service Center All Rights Reserved.</p>
    </div>
</body>

</html>
