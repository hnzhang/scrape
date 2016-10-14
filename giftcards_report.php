<?php
//get order deadline, and pickip location
require "giftcards_common.php";

$accountKey = 'SuperEmail';
$deadlineKey = 'Deadline';
if( array_key_exists($accountKey,$_REQUEST)) {
	$val = trim($_REQUEST[$accountKey]);
	$deadline = trim($_REQUEST[$deadlineKey]);

	if(isSuperEmail($val) && strlen($deadline) > 0){
		$displayStr = '
		<html>
		<head>
		<title>
			Welcome--SKSC GiftCard Reports
		</title>
		</head>
		<body>
			<table align="center">
				<tr>
					<td><image src="sksc.jpg"></td>
				</tr>
		';

			$displayStr .= '
				<tr>
					<td>Type of Report</td>
				</tr>
				<tr>
					<form method="post" action="giftcards_processreports.php" autocomplete="on">
					<td>
						<input type="radio" name="reportType" checked="true" value="PickupAccount"> By Pickup and Account<br>
						<input type="radio" name="reportType" value="VenderPrice"> By Vender and Price<br>
					</td>
				</tr>
				<tr>
					<td>
						<input type="text" name="deadline" id="deadline" value="'.$deadline.'">
						<input type="hidden" name="superEmail" value="'.$val.'"/>
						<input type="submit" id="go" value="Go" />
					</td>
					</form>
				</tr>
				<tr>
					<td>Tip: for accuracy, please copy & paste deadline from Template spreadsheet.
					</td>
					</tr>
			</table>
		</body>
		</html>
			';
		echo $displayStr;
	}
}
?>
