<?php
$inventory = array("categories"=>array( ), "vendors"=>array());

$AccountEmail = "";
$VisibleCtl = false;
function getAccountEmail() {
	global $AccountEmail, $VisibleCtl;

	$accountKey = 'AccountEmail';
	if( array_key_exists($accountKey,$_REQUEST)) {
		$val = trim($_REQUEST[$accountKey]);
		if(filter_var($val, FILTER_VALIDATE_EMAIL)) {
			$AccountEmail = $val;
			$VisibleCtl = true;
		}
	}
}
$Special_Messages = "";
$PickupOptions = array();
$Order_Deadline = "";
/*
	get template data for general instruction, deadline and pickup, etc
*/
function getTemplateInfo() {
	global $Special_Messages, $PickupOptions, $Order_Deadline;
	$template_url= "https://spreadsheets.google.com/feeds/list/1Kp0Lcneb_UUjE3Vi0FjpCNbXdTRLATjSpyNnLJx-E9Y/1/public/values?alt=json";
	$json = file_get_contents($template_url);
	$data = json_decode($json, TRUE);
	$rows = $data['feed']['entry'];
	foreach ($rows as $item) {
		$opt = $item['gsx$pickups']['$t'];
		$opt = trim($opt);
		if (strlen($opt) > 0) {
				array_push($PickupOptions, $opt);
				//pickups.append(jQueryLatest("<option>").attr("value", opt).text(opt))
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
/*
	build up  inventory data
*/
function getInventoryInfo() {
	global $inventory;
	$url_inventory = "https://spreadsheets.google.com/feeds/list/1Kp0Lcneb_UUjE3Vi0FjpCNbXdTRLATjSpyNnLJx-E9Y/2/public/values?alt=json";

	$json = file_get_contents($url_inventory);
	$data = json_decode($json, TRUE);
	$rows = $data['feed']['entry'];

	foreach ($rows as $item) {
		$id = $item['gsx$id']['$t'];
		$vendorName   = $item['gsx$vendor']['$t'];
		$category =  $item['gsx$category']['$t'];
		$first =  $item['gsx$first']['$t'];
		//echo '<ul align="left">'. $vendorName . ', '. $category .','. $first .'</ul>';
		if (! array_key_exists($category, $inventory['categories'])){
			$inventory['categories'][$category] = array();
		}
		$vendor = array($id, $vendorName, $first);
		$inventory['categories'][$category][$id] = $vendor;
		$inventory['vendors'][$id] = $vendor;
	}
}

getAccountEmail();
if($VisibleCtl) {
	getInventoryInfo();
	getTemplateInfo();
}
?>
<style type="text/css">
	#inventory, #cart {
	    width: 100%;
	}

	#inventory td {
	    border-bottom: solid 1px RGB(225, 225, 255);
	}

	#inventory td:nth-child(2) {
	    text-align: center;
	}

	#inventory td:first-child {
	    text-align: center;
	}

	#inventory td:last-child input[type=number] {
	    width: 3em;
	}

	.bold {
	    font-weight: bold;
	}

	#categories {
	    font-size: 0.75em;
	}

	#inventory tbody {
	    display: block;
	    overflow: scroll;
	    height: 25em;
	}

	#inventory thead {
	    display: block;
	}

	table #cart {
	    border: solid 1px silver;
	    border-radius: 0.5em;
	    height: 10em;
	}

	#cart span {
	    fonst-weight: bold;
	}

	#cart td {
	    text-align: right;
	}

	#cart td:first-child {
	    text-align: left;
	}

	#cart td:nth-child(6) {
	    text-align: center;
	}

	#cart td input[type=text] {
	    width: 100%;
	}

	#cart th {
	    text-align: right;
	}

	#cart th:first-child {
	    text-align: left;
	}

	#cart th:nth-child(6) {
	    text-align: center;
	}

	#cart input[type=button] {
	    background: RGB(255, 128, 128);
	}

	#cart tfoot td {
	    font-weight: bold;
	    text-align: center;
	}
</style>

<script>
	var Order_Deadline = "<?php echo $Order_Deadline  ?>";
	var Special_Messages = '<?php echo $Special_Messages ?>';
	var AccountEmail = "<?php echo $AccountEmail  ?>";
	var order = {};
//id, category, Vender, Type, Raw, Remit, First, Second, Third, forth

	var inventories = <?php echo json_encode( $inventory ) ?>;
	function OnSelectPickupOption(){

	}
	function OnSelectCategory(selectedVal) {
		if(selectedVal === "disabled"){
			return;
		}
		venderList = document.getElementById("vendor");
		venderList.innerHTML = '';
		for( v in inventories['categories'][selectedVal]){
			var el = document.createElement("option");
			var vendor = inventories["vendors"][v];
			el.textContent = vendor[1];
			el.value = v[0];
			venderList.appendChild( el );
		}
	}
	function OnSelectVendor(selectedVal) {

	}
	function addSelectedToOrder(){
		var table = document.getElementById('orderList');
		/*
		var rowCount = table.rows.length;
		var row = table.insertRow(rowCount);

		var cell3 = row.insertCell(0);
		cell3.innerHTML="test"
		*/
		var listItem = document.createElement("li");
		//create new text node
		var textNode = document.createTextNode("test");
		//add text node to li element
		listItem.appendChild(textNode);
		//add new list element built in previous steps to unordered list
		table.appendChild(listItem);
	}
	function submitOrder(){

	}

</script>
<body>
<p id="order_html">
<table border="0" bordercolor="#ccc" cellpadding="5" cellspacing="0" style="border-collapse:collapse;" summary="test">
	<tbody>
		<tr>
			<td><span ><?php echo $Special_Messages; ?></span></td>
		</tr>
		<tr>
			<td><span >Order deadline: <?php echo $Order_Deadline; ?></span></td>
		</tr>
		<tr>
			<td><span >Please click <a href="https://www.teamunify.com/cansksc/UserFiles/File/Fundraising/2015-16/INSTRUCTIONSTOUSETHEGIFTCARDONLINEORDERINGSYSTEM.pdf">here</a> for instructions.</span></td>
		</tr>
	</tbody>
</table>
</div>

<hr />
<form method="post" action="postdata.php">
<table id="cart">
	<thead>
		<tr>
			<td colspan="4">Account Email: <?php echo $AccountEmail;?></td>
			<td colspan="3">Pickup date/time:
			<select id="PickupOptions" onchange="OnSelectPickupOption(this.options[this.selectedIndex].value)">
				<option value="disabled">==Select Pickup Option==</option>
				<?php
					foreach($PickupOptions as $option) {
						echo '<option value="'. $option . '">'. $option .'</option>';
					}
				?>
			</select></td>
		</tr>
		<tr>
			<th colspan="7"><hr/></th>
		</tr>

		<tr>
			<th colspan="7">Please Choose and Add Cart. You can reference to inventories to see what are available</th>
		</tr>
		<tr >
			<th>Category</th>
			<th>Vendor</th>
			<th>Count</th>
			<th>Subtotal</th>
			<th>Remit</th>
			<th>Recurring</th>
			<th>&nbsp;</th>
		</tr>

		<tr>
			<td>
				<select  id="category" onChange="OnSelectCategory(this.options[this.selectedIndex].value)">
						<option value="disabled">==Please Choose==</option>
						<?php
							foreach ($inventory['categories'] as $key => $value) {
								echo '<option value="'. $key . '">'. $key .'</option>';
							}
					?>
					</select>
			</td><!--category-->
			<td><select id="vendor"/></td><!--vendor-->
			<td><input type="number" name="quantity" min="1" id="count" ></td><!--Count-->
			<td witdh="10%"><input type="text" id="subtotal"/></td><!--Subtotal-->
			<td witdh="100px"><input type="text" id="remit"/></td><!--Remit-->
			<td witdh="10%"><input type="checkbox"  /></td><!--Recurring-->
			<td><input onclick="addSelectedToOrder()" style="background: RGB(255,255,128);" type="button" value="Add To Order" /></td><!--Subtotal-->
		</tr>

	</thead>
	<tfoot>
		<tr>
			<td>&nbsp;</td>
		</tr>

		<tr>
			<td colspan="3" style="text-align: center;">Current Cart:</td>
			<td id="cart_total_cost" style="text-align: right;">&nbsp;</td>
			<td id="cart_total_remit" style="text-align: right;">&nbsp;</td>
			<td colspan="2"><input onclick="submitCart()" style="background: RGB(128,255,128);" type="button" value="Submit Order" /></td>
		</tr>
	</tfoot>
	<tbody>
		<tr id="order_details">
			<td><ul id="orderList"></ul></td>
			<!--<td colspan="7"><span class="loading">Please wait, updating cart.</span></td>-->
		</tr>
	</tbody>
</table>

<!--
<p>
	<br />
	<input checked="true" disabled="true" type="checkbox" />Recurring?
</p>
-->
<table id="inventory">
	<thead>
		<tr>
			<th colspan="4" id="categories"><h2>Inventories:</h2></th>
		</tr>
		<!--
		<tr>
			<th colspan="4" style="text-align: right;">&nbsp;</th>
		</tr>
-->
	</thead>
	<tbody>
		<tr>
			<td colspan="7">
				<span >
						<?php
						foreach ( $inventory['categories'] as $key => $category ){
							echo '<h3 align="left">'. $key.'</h2>';
							foreach( $category as $id => $vendor ){
								echo '<ul align="left">'.$vendor[1].','. $vendor[2] .'</ul>';
							}
						}
					?>
			</span></td>
		</tr>

	</tbody>
</table>
</form>
</p>
</body>
