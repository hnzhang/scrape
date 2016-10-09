<?php
//get order deadline, and pickip location
require "giftcards_common.php";

$accountKey = 'SuperEmail';
if( array_key_exists($accountKey,$_REQUEST)) {
	$val = trim($_REQUEST[$accountKey]);
	if(isSuperEmail($val)){
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
						<input type="text" name="deadline" id="deadline">
						<input type="hidden" name="superEmail" value=""/>
						<input type="submit" id="go" value="Go" />
					</td>
					</form>
				</tr>
			</table>
		</body>
		</html>
			';
		echo $displayStr;
	}
}
?>
