<?php 
#time test
$timestamp_tmr='11/16/2016';
$deadline_date_tmr = strtotime($timestamp_tmr);
$deadline_str_tmr = date("Y-m-d", $deadline_date_tmr);
echo 'Tomorrow: '. $deadline_str_tmr . '<br>';

$timestamp_yes='11/14/2016';
$deadline_date_yes = strtotime($timestamp_yes);
$deadline_str_yes = date("Y-m-d", $deadline_date_yes);
echo "Yesterday: " . $deadline_str_yes . '<br>';

$today = time();
echo $deadline_date;
echo '<br>';
echo $today;
echo '<br>';
$today_str =  date("Y-m-d", $today);
echo $today_str .'<br>';
if($today_str < $deadline_str_tmr) {
	echo "less than tomorrow<br>";
}

if($today_str < $deadline_str_yes) {
	echo "we are in the past<br>";
}
else {
	echo "we are in today, greater than yesterday";
}


?>
