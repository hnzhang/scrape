<?php
$Order_Deadline = "";
$AccountEmail ="";
$PickupOption = "";


function validateOrderDetails($order_details) {
	return false;
}
/*
function sendEmailNotification($order_details) {

}

function submitOrderToDB($order_details){

}
*/
function displayOrder($order_details){
	var_dump($order_details);
}

if(!empty($_POST)) {
		var_dump($_POST);
		$Order_Deadline = $_POST['Order_Deadline'];
		$AccountEmail = $_POST['AccountEmail'];
		$order_details = json_decode($_POST["order_details"]);
		displayOrder();
		if(!is_null($order_details) {
			if(validateOrderDetails($order_details)){
				//sendEmailNotification($order_details);
				//submitOrderToDB($order_details);
				displayOrder($order_details);
			}else{

			}

		} else {

		}
}
echo "Hello";
?>
