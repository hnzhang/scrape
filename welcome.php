<?php
$accountKey = 'shannon';
if( array_key_exists($accountKey,$_REQUEST)) {
	$val = trim($_REQUEST[$accountKey]);
	if($val === "AKfycbxeF8VkYYNgTI7V2ppGWA3U0udv3WI8UhSQEa6f_RPLcgo6fU4e"){
echo'
<html>
<head>
</head>
<body>
	<form method="post" action="welcome_validate.php"
	<table align="center">
		<tr>
			<td><image src="sksc.jpg"></td>
		</tr>
		<tr>
			<td>Enter Your SKSC Account Email</td>
		</tr>
		<tr>
			<td><input type="text" name="AccountEmail" size="20"/><input type="submit" id="go" value="GO"/><td>
		</tr
	</table>
	</form>
</body>
</html>
	';
}
}
?>
