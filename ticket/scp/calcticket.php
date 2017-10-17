<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <title>FNETLINK TICKET每日统计</title>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body>
<?php
header("Content-type: text/html; charset=utf-8");

$cmd = "/usr/local/bin/ticketsum.py &";
exec($cmd, $out);
foreach($out as $o) {
  echo $o."<br>";
}
?>
</body>
</html>
