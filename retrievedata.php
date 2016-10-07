<?php
require "giftcards_common.php";
$accountEmail = $_REQUEST["Account"];

$date = $_REQUEST["Date"];
echo "Account: ".$accountEmail."<br>";
echo "Date: " .$date ."<br>";
$orders = getOrderWithAccountAndDeadline($accountEmail, $date);
//$orders = getOrderWithAccountAndDeadline("greganddonnacook@gmail.com", "10/31/2015");
//$orders = reportByPickupOptionAndAccount("hnzhang@gmail.com", "10/7/2016");
echo displayReportForAccount($orders);
//var_dump($orders);
?>
