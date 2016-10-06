<?php
require "giftcards_common.php";

$inventory = array("categories"=>array( ), "vendors"=>array());

$AccountEmail = "";
$VisibleCtl = false;
function getAccountEmail() {
	global $AccountEmail, $VisibleCtl;

	$accountKey = 'AccountEmail';
	//if( isset($_SESSION, $accountKey )) {
	if( array_key_exists( $accountKey, $_REQUEST )) {
		$val = trim($_REQUEST[$accountKey]);
		if(filter_var($val, FILTER_VALIDATE_EMAIL)) {
			$AccountEmail = $val;
			$VisibleCtl = true;
		}
	} else {
		echo "No AccountEmail";
	}
}

$Special_Messages = "";
$PickupOptions = array();
$Order_Deadline = "";
$Order_Deadline_Display = "";
$Current_Time_Display = "";
$System_Enabled = false;
$Error_Message = "";
/*
	get template data for general instruction, deadline and pickup, etc
function getTemplateInfo() {
	global $Special_Messages, $PickupOptions, $Order_Deadline;
	global $Spreadsheet_ID;
	$template_url= "https://spreadsheets.google.com/feeds/list/".$Spreadsheet_ID."/1/public/values?alt=json";

	$json = file_get_contents($template_url);
	$data = json_decode($json, TRUE);
	$rows = $data['feed']['entry'];
	foreach ($rows as $item) {
		$opt = $item['gsx$pickups']['$t'];
		$opt = trim($opt);
		if (strlen($opt) > 0) {
				array_push($PickupOptions, $opt);
		}
		$nextOrderData = trim($item['gsx$nextorderdate']['$t']);
		if (strlen($nextOrderData) >0) {
				$Order_Deadline = $nextOrderData;
		}

		$msg = trim($item['gsx$specialmessages']['$t']);
		if (strlen($msg)) {
			$Special_Messages = $msg;
		}
	}
}

*/
/*
	build up  inventory data
*/
function getInventoryInfo() {
	global $inventory;
	global $Spreadsheet_ID;
	$url_inventory = "https://spreadsheets.google.com/feeds/list/".$Spreadsheet_ID."/2/public/values?alt=json";

	$json = file_get_contents($url_inventory);
	$data = json_decode($json, TRUE);
	$rows = $data['feed']['entry'];
	$id = 0;
	foreach ($rows as $item) {
		//$id = $item['gsx$id']['$t'];
		$vendorName   = $item['gsx$vendor']['$t'];
		$category =  $item['gsx$category']['$t'];
		$first =  $item['gsx$first']['$t'];
		$second =  $item['gsx$second']['$t'];
		$third =  $item['gsx$third']['$t'];
		$fourth =  $item['gsx$fourth']['$t'];

		$remit =  $item['gsx$remit']['$t'];
		if (! array_key_exists($category, $inventory['categories'])){
			$inventory['categories'][$category] = array();
		}
		$vendor = array($id, $vendorName, $first, $second, $third, $fourth,$remit );
		$inventory['categories'][$category][$id] = $vendor;
		$inventory['vendors'][$id] = $vendor;
		$id += 1;
	}
}

function getCurrentDateTime() {
	date_default_timezone_set('America/Los_Angeles');//pacific time
	//year/mm/dd hh:mm
	$datetime = date("M") .'/' . date("d") .'/'. date("Y").' '.date("H"). ':'.date("i");
	return  $datetime;
}

getAccountEmail();

if($VisibleCtl) {
	$tempplateInfo = getTemplateInfo();
	$Special_Messages = $tempplateInfo[0];
	$PickupOptions= $tempplateInfo[1];
	$Order_Deadline = $tempplateInfo[2];
	//m/d/y
	$deadline_date = strtotime($Order_Deadline);

	$Order_Deadline_Display = date("M/d/Y",$deadline_date);
	$Current_Time_Display = getCurrentDateTime();
	$today = time();
	if($today < $deadline_date)
	{
		$System_Enabled = true;
		getInventoryInfo();
	} else {
		$System_Enabled = false;
		$Special_Messages = "";
		$Error_Message =  "Order deadline ".$Order_Deadline." expired, cannot order Anymore. Please wait for next time";
	}
}
?>

<script>
	var Order_Deadline = "<?php echo $Order_Deadline  ?>";
	var Special_Messages = '<?php echo $Special_Messages ?>';
	var AccountEmail = "<?php echo $AccountEmail  ?>";
	var order = {};

//id, category, Vender, Type, Raw, Remit, First, Second, Third, forth

	var inventories = <?php echo json_encode( $inventory ) ?>;

	function formatMoneyToCAD( amount ) {
		return amount.toLocaleString('en-CA', { style: 'currency', currency: 'CAD' });
	}
	function OnSelectPickupOption(){
		pickupOption = document.getElementById("PickupOptions");
	}

	function addItemToSelectionList(selectionCtrl, item, value) {
		if(item.length === 0  || value.length === 0 || selectionCtrl.nodeName != "SELECT")
		{
			return;
		}

		var el = document.createElement("option");
		el.textContent = item;
		el.value = value;
		selectionCtrl.appendChild( el );
	}

	function OnSelectCategory(selectedVal) {
		venderList = document.getElementById("vendor_name");
		venderList.innerHTML = '';
		addItemToSelectionList(venderList, "==select==", "disabled")
		if(selectedVal != "disabled"){
			for( vendorId in inventories['categories'][selectedVal]){
				var vendor = inventories["vendors"][vendorId]
				addItemToSelectionList(venderList,  vendor[1],  vendor[0])
			}
		}
		OnSelectVendor("disabled");
	}

	function OnSelectVendor(selectedVal) {
		var countCtl = document.getElementById("vendor_card_count");
		countCtl.value = 0;
		var priceCtl = document.getElementById("vendor_price");
		priceCtl.innerHTML = '';
		//console.log("in OnSelectVendor");
		//console.log(selectedVal)
		if(selectedVal != "disabled") {
			var vendor = inventories["vendors"][selectedVal]
			//console.log(vendor)
			addItemToSelectionList(priceCtl, vendor[2], vendor[2]);
			addItemToSelectionList(priceCtl, vendor[3], vendor[3]);
			addItemToSelectionList(priceCtl, vendor[4], vendor[4]);
			addItemToSelectionList(priceCtl, vendor[5], vendor[5]);
		}

		remitCtl = document.getElementById("vendor_remit");
		remitCtl.value = "";
		subtotalCtl = document.getElementById("vendor_subtotal");
		subtotalCtl.value = "";
	}

	function UpdateRemitAndSubtotal(priceStr, remitRate, numOfCards ){
		var priceFloat = Number(priceStr.replace(/[^0-9\.]+/g,""));
		var remiteFloat = parseFloat(remitRate) / 100.0;
		var numOfCardsInt = parseInt(numOfCards);
		remitVal = priceFloat * numOfCardsInt * remiteFloat;
		subtotalVal = priceFloat * numOfCardsInt *(1.0- remiteFloat);

		remitCtl = document.getElementById("vendor_remit");
		subtotalCtl = document.getElementById("vendor_subtotal");
		remitCtl.value = remitVal.toFixed(2);
		subtotalCtl.value = subtotalVal.toFixed(2);
	}

	function OnSelectCardPrice(selectedVal){
		var countCtl = document.getElementById("vendor_card_count");
		countCtl.value = 0;
	}

	function OnCardQuantityChanged(val){
		var countCtl = document.getElementById("vendor_card_count");
		numOfCards = countCtl.value;
		var priceCtl = document.getElementById("vendor_price");
		priceInStr = priceCtl.options[priceCtl.selectedIndex].value;
		var vendorCtl = document.getElementById("vendor_name");
		vendorIdInStr = vendorCtl.options[vendorCtl.selectedIndex].value;
		var vendor = inventories["vendors"][vendorIdInStr]
		remitRate = vendor[6];
		UpdateRemitAndSubtotal(priceInStr,remitRate,  numOfCards);
	}

	function UpdateTotalAndTotalRemitDisplay(){
		//loop through all the orders for totals
		OrderTotal = 0.0;
		OrderRemitTotal = 0.0;
		for(var vendorName in order){
			for(var price in order[vendorName]){
					OrderTotal += parseFloat(order[vendorName][price].Subtotal);
					OrderRemitTotal += parseFloat(order[vendorName][price].RemitVal);
			}
		}

		var totalCostCtl = document.getElementById("order_total_cost");
		totalCostCtl.innerHTML = "Total: $" + formatMoneyToCAD(OrderTotal);
		var totalRemitCtl = document.getElementById("order_total_remit");
		totalRemitCtl.innerHTML = "Total Remit: $" + formatMoneyToCAD(OrderRemitTotal);

		document.getElementById("OrderTotal").value = OrderTotal;
		document.getElementById("OrderRemitTotal").value = OrderRemitTotal;
	}

	function addSelectedToOrder(){
		var vendorCtl = document.getElementById("vendor_name");
		var vendorId = vendorCtl.options[vendorCtl.selectedIndex].value;
		if(vendorId == "disabled") {
			displayErrorMsg("Invalid selection of Vendor!");
			return false;
		}
		var count = document.getElementById("vendor_card_count").value;
		if(count <= 0){
			displayErrorMsg("Please specify how many card you need!");
			return false;
		}

		var priceCtrl = document.getElementById("vendor_price");
		var price = priceCtrl.options[priceCtrl.selectedIndex].value;
		//order entry data
		var vendor = inventories['vendors'][vendorId]

		var remitRate = vendor[6];
		var vendorName = vendor[1];
		remitCtl = document.getElementById("vendor_remit");
		var RemitVal = remitCtl.value;
		subtotalCtl = document.getElementById("vendor_subtotal");
		var SubTotal = subtotalCtl.value;
		//TimeStamp, AccountEmail, pickup, Vendor ID, Vendor, Price, Remit, Count, Order_Deadline
		//temp solution for demo
		if(!(vendorName in order)){
			order[vendorName]={};
		}
		if(!(price in order[vendorName])){
			order[vendorName][price] = {Remit:remitRate, Count:count, RemitVal: RemitVal, Subtotal:SubTotal};//remit, count
		} else {
			order[vendorName][price].Remit = remitRate;
			order[vendorName][price].Count = count;
			order[vendorName][price].RemitVal = RemitVale;
			order[vendorName][price].SubTotal = Subtotal;
		}

		//change selected id of category
		cateGoryControl = document.getElementById("category");
		cateGoryControl.selectedIndex = 0;
		vendorCtl.selectedIndex = 0;

		//for display
		var val = vendorName.concat(vendorName,'  ', price, ' * ', count, '   Remit $', RemitVal, '   subtotal $', SubTotal );
		//display data
		var table = document.getElementById('orderList');

		var listItem = document.createElement("li");
		//create new text node
		var textNode = document.createTextNode(val);
		//add text node to li element
		listItem.appendChild(textNode);
		//add new list element built in previous steps to unordered list
		table.appendChild(listItem);
		//update Total and Total Remit;
		UpdateTotalAndTotalRemitDisplay();

		//clear error message;
		displayErrorMsg("");
	}

	function displayErrorMsg(msg) {
		var  errorMsgCtl = document.getElementById("error_msg");
		errorMsgCtl.innerHTML = msg;
	}

	function validateForm(){
		if(order.length == 0){
			displayErrorMsg("Empty Cart!");
			return false;
		}

		pickupOptionCtl = document.getElementById("PickupOptions");
		if(pickupOptionCtl.options[pickupOptionCtl.selectedIndex].value === "disabled") {
			displayErrorMsg("Please choose pickup option!");
			return false;
		}

		orderTotalCtl = document.getElementById("OrderTotal");
		orderTotalStr = orderTotalCtl.value;
		console.log("order log:" + orderTotalStr);
		if(orderTotalStr.length === 0  || parseFloat(orderTotalStr) <= 0.01 ) {
			displayErrorMsg("Empty order! No worth to sumbit");
			return false;
		}

		document.getElementById("order_details").value = JSON.stringify(order);
	}
</script>

<style type="text/css">
	.error_msg {
		background-color: red;
	}
	.important_msg {
		background-color: orange;
	}

	div.scroll {
    background-color: #00FFFF;
		height: 600px;
    overflow: scroll;
}
</style>
<html>
<head>
<title>
	SKSC GiftCard Ordering
</title>
</head>
<body>
	<!--message area-->
<table>
	<tr>
		<td><image src="sksc.jpg" height="128" width="128"/></td>
		<td>
			<table>
				<tr><!--for Special Message-->
					<td>
						<p id="Special_Messages" class="info_msg">
							<?php echo $Special_Messages; ?>
						</p>
				</td>
				</tr>
				<tr><!--for current time and order deadline-->
					<td>
						<p id="Order_Deadline" class="important_msg" >
							Order Deadline: <?php echo $Order_Deadline_Display; ?>     Current time: <?php echo $Current_Time_Display;?>
						</p>
					</td>
				</tr>
				<tr><!--For intruction-->
					<td><p id="instruction_msg" class="info_msg">
						If it is your 1st time to place order, please click <a href="https://www.teamunify.com/cansksc/UserFiles/File/Fundraising/2015-16/INSTRUCTIONSTOUSETHEGIFTCARDONLINEORDERINGSYSTEM.pdf">here</a> for instructions.</p></td>
				</tr>
				<tr><!--For Error message-->
					<td><p id="error_msg" class="error_msg"></p></td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<hr />
<form method="post" action="process_order.php"  onsubmit="return validateForm()">
	<table width="100%">
		<tr>
			<td width="40%">
				<input type ="hidden" name="Order_Deadline" value ="<?php echo $Order_Deadline?>" />
				<input type ="hidden" name="AccountEmail" value ="<?php echo $AccountEmail?>" />
				Account Email: <?php echo $AccountEmail?>
			</td>
			<td width="60%">Pickup date/time:
				<select id="PickupOptions" name="pickupOption" onchange="OnSelectPickupOption(this.options[this.selectedIndex].value)">
					<option value="disabled">==Select Pickup Option==</option>
					<?php
						foreach($PickupOptions as $option) {
							echo '<option value="'. $option . '">'. $option .'</option>';
						}
					?>
				</select>
			</td>
		</tr>
	</table>
	<hr>
	<table width="100%">
		<tr>
			<th colspan="6" style="text-align: left;">Please Choose and Add Order. You can reference to inventories to see what are available</th>
		</tr>
		<tr >
			<th style="text-align: left;">Category</th>
			<th style="text-align: left;">Vendor</th>
			<th style="width: 100px; text-align: left; ">Card</th>
			<th style="width: 100px; text-align: left;">Count</th>
			<th style="width: 100px; text-align: left;">Remit</th>
			<th style="width: 100px; text-align: left;">Subtotal</th>
			<th style="width: 150px; text-align: left;"></th>
		</tr>
		<tr>
			<td><!--category-->
				<select  id="category" onChange="OnSelectCategory(this.options[this.selectedIndex].value)">
						<option value="disabled">==Please Choose==</option>
						<?php
							foreach ($inventory['categories'] as $key => $value) {
								echo '<option value="'. $key . '">'. $key .'</option>';
							}
						?>
					</select>
			</td>
			<td>
				<select id="vendor_name" onChange="OnSelectVendor(this.options[this.selectedIndex].value)"/>
			</td><!--vendor-->
			<td>
				<select id="vendor_price" onChange="OnSelectCardPrice(this.options[this.selectedIndex].value)" />
			</td>
			<td >
				<input type="number"  min="1" id="vendor_card_count" onchange="OnCardQuantityChanged(this.value)" style="width: 50px;"/>
			</td>
			<td style="width: 100px;">
				<input type="text" id="vendor_remit"   disabled="true" style="width: 50px;"/>
			</td>
			<td style="width: 100px;">
				<input type="text" id="vendor_subtotal" disabled="true" style="width: 50px;"/>

			</td><!--Subtotal-->
			<td style="width: 150px;">
				<input onclick="addSelectedToOrder()" style="background: RGB(255,255,128);" type="button" value="Add To Order" />
			</td>
		</tr>
		<tr>
			<td colspan="6">
				<br>
				<h2>Your Order:</h2>
			</td>
		</tr>
		<tr>
			<td colspan="6">
				<ul id="orderList"></ul>
			</td>
		</tr>
		<tr>
			<td colspan="2"  style="text-align: center;width: 300px;"><p id="order_total_cost"></p></td>
			<td colspan="2"  style="text-align: center;width: 300px;"><p id="order_total_remit" ></p> </td>
			<td colspan="2"  style="width: 300px;">
				<input type="hidden" name="OrderRemitTotal" id="OrderRemitTotal" value="" />
				<input type="hidden" name="OrderTotal" id="OrderTotal" value="" />
				<input type="hidden" name="order_details" id="order_details" value="" />
				<input  style="background: RGB(128,255,128);" type="submit" value="Submit Order" /></td>
			</td>
		</tr>
	</table>
</form>
<table id="inventory">
		<tr>
			<th style="text-align: left;">Inventories:(Vender Name, Remit Rate, Card Values)</th>
		</tr>
		<tr>
			<td >
				<div class="scroll">
						<?php
						foreach ( $inventory['categories'] as $key => $category ){
							echo '<h3 align="left">'. $key.'</h3>';
							foreach( $category as $id => $vendor ){
								$inventoryStr = $vendor[1].' | '. $vendor[6]. " | " .$vendor[2];
								if(strlen($vendor[3]) >0 ) {	$inventoryStr = $inventoryStr. " ,".$vendor[3]; }//second price
								if(strlen($vendor[4]) >0 ) {	$inventoryStr = $inventoryStr. " ,".$vendor[4]; }//third
								if(strlen($vendor[5]) >0 ) {	$inventoryStr = $inventoryStr. " ,".$vendor[5]; }//fourth
								echo '<ul align="left">'. $inventoryStr .'</ul>';
							}
						}
					?>
			</div>
		</td>
	</tr>
</table>
</body>
</html>
