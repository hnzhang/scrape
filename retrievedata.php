<?php
require "giftcards_common.php";


$orders = getOrderWithAccountAndDeadline("hnzhang@gmail.com", "10/7/2016");
//$orders = getOrderWithAccountAndDeadline("greganddonnacook@gmail.com", "10/31/2015");
//$orders = reportByPickupOptionAndAccount("hnzhang@gmail.com", "10/7/2016");
echo displayReportForAccount($orders);
//var_dump($orders);
?>
