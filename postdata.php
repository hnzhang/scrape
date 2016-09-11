<?php
/*
jQueryLatest.post(
				urls['post'], {
						Account: jQueryLatest("#AccountEmail")
								.text(),
						Order_Deadline: Order_Deadline,
						Pickup: jQueryLatest("#Pickup")
								.val(),
						Order: Order
				}
		)
		'post': "https://script.google.com/macros/s/AKfycbyFuHnEvzt3XTNc9Sy8R5KZldFVLU75jD1tDvL6l5ck6kJ6nS8Z/exec"
*/
$order1 = array('vender'=>"Costco", 'Price'=>"12.0");
$order2 = array('vender'=>"Walmart",'Price'=> "11.0");

$order = array($order1, $order2);
$data = array ('Order_Deadline' => 'today', 'Account' => 'hello@ea.com','Pickup' =>"FleetWood" ,'Order'=> $order1);


// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    )
);
$context  = stream_context_create($options);
$test_url = 'https://script.google.com/macros/s/AKfycbzYcZMNXvNuP7pR1yNyLugz-qby4RWWumbdmJbz3J4F5Pu3SBU6/exec';

//file_get_contents($test_url, false, $context);
$fp = fopen($test_url, 'r', false, $context);
if ($result === FALSE) { 

	echo "Failed";
 }else {
 	echo "Succeeded";
 	echo "<br>".$result;
 }
 fpassthru($fp);
 fclose($fp);
?>
