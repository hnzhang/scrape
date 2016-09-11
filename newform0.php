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
var Order_Deadline = 0
var Special_Messages = ""
var AccountEmail = ""
var order = {}
var vendors = {};//id, category, Vender, Type, Raw, Remit, First, Second, Third, forth
var inventories = {'categories':{}, 'vendors': {}};

var urls = {
    'current_cart': "https://script.google.com/macros/s/AKfycbyFuHnEvzt3XTNc9Sy8R5KZldFVLU75jD1tDvL6l5ck6kJ6nS8Z/exec?alt=json&callback=updateInventory&Account=",
    'template': "https://spreadsheets.google.com/feeds/list/1Kp0Lcneb_UUjE3Vi0FjpCNbXdTRLATjSpyNnLJx-E9Y/1/public/values?alt=json",
    //'inventory': "https://spreadsheets.google.com/feeds/list/1Kp0Lcneb_UUjE3Vi0FjpCNbXdTRLATjSpyNnLJx-E9Y/2/public/values?alt=json&callback=populate",
    'inventory': "https://spreadsheets.google.com/feeds/list/1Kp0Lcneb_UUjE3Vi0FjpCNbXdTRLATjSpyNnLJx-E9Y/2/public/values?alt=json",
    'post': "https://script.google.com/macros/s/AKfycbyFuHnEvzt3XTNc9Sy8R5KZldFVLU75jD1tDvL6l5ck6kJ6nS8Z/exec"
}
function _populateInventory()
{
	var inventoryDetails = "";
	for(category in inventories.categories) {
		inventoryDetails += "<h2 align=\"left\">" + category + " </h2>";

		for( v in inventories.categories[category]){
			inventoryDetails += "<ul align=\"left\">" + v.name +" ," + v.first +"</ul>";
		}
	}
	console.log(inventoryDetails);
	document.getElementById("inventoryDetails").textContent = inventoryDetails;
}
function Vendor(id, name, category, type, raw, remit, first, second, third, fourth) {
	this.id = id;
	this.name = name;
	this.category =  category;
	this.type = type;
	this.raw = raw;
	this.remit = remit;
	this.first = first;
	this.second = secnd;
	this.third = third;
	this.fourth = fourth;
};
function _getInventory(json){
	for (ix in json.feed.entry) {
		vendorid = json.feed.entry[ix].gsx$id.$t;
		category = json.feed.entry[ix].gsx$category.$t;
		vendorName = json.feed.entry[ix].gsx$vendor.$t;
		type = json.feed.entry[ix].gsx$type.$t;
		raw = json.feed.entry[ix].gsx$raw.$t;
		remit = json.feed.entry[ix].gsx$remit.$t;
		first = json.feed.entry[ix].gsx$first.$t;
		second = json.feed.entry[ix].gsx$second.$t
		third = json.feed.entry[ix].gsx$third.$t;
		fourth = json.feed.entry[ix].gsx$fourth.$t;
		inventories.categories[ category ] = category;
		//console.log(vendorid)
		var v = { id:vendorid, name: vendorName, category:category, type:type, raw:raw, remit:remit, first:first, second:second, third:third, fourth:fourth};

		if(!inventories.vendors.hasOwnProperty(category)){
			inventories.vendors[category] ={};
		}
		inventories.vendors[category][v.name] = v;
	}
	_populateInventory();
};

function loadJSON(path, success, error) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                if (success)
                    success(JSON.parse(xhr.responseText));
            } else {
                if (error)
                    error(xhr);
            }
        }
    }
    ;
    xhr.open("GET", path, true);
    xhr.send();
}

function _getOrderDeadlineAndSpecialMessage(json) {
    var pickupOption = document.getElementById("Pickup");
    var specialMsgControl = document.getElementById("SpecialMessages");
    var entrySize =json.feed.entry.length;
    var count = 0;
    for (ix in json.feed.entry) {
        opt = json.feed.entry[ix].gsx$pickups.$t
        opt = opt.trim();
        if (opt.length > 0) {
            //pickups.append(jQueryLatest("<option>").attr("value", opt).text(opt))
            var optn = document.createElement("OPTION");
            optn.text = opt;
            optn.value = opt;
            pickupOption.options.add(optn);
            //console.log(opt)
        }
        if (json.feed.entry[ix].gsx$nextorderdate.$t) {
            Order_Deadline = json.feed.entry[ix].gsx$nextorderdate.$t;
        }
		if (json.feed.entry[ix].gsx$specialmessages.$t) {
			Special_Messages = json.feed.entry[ix].gsx$specialmessages.$t;
        }
        count++;
        if(count == entrySize)
        	break;
        //console.log(count);
        //console.log(Order_Deadline)
       	// console.log(Special_Messages);
    }
    specialMsgControl.textContent = Special_Messages;
    pickupOption.disabled = pickupOption.length === 0;
    //console.log(json);
    if(pickupOption.disabled == false)
    {
    loadJSON( urls['inventory'],
		  function(data) { _getInventory(data); },
		  function(xhr) {    console.error(xhr);}
		);
    }
};



loadJSON( urls['template'],
		  function(data) { _getOrderDeadlineAndSpecialMessage(data); },
		  function(xhr) {    console.error(xhr);}
		);


function changeAccount(e) {
    email = prompt("User Account Email Please:");
    document.getElementById("AccountEmail").textContent = email;

    AccountEmail = email;
    getCurrentOrder()
}
</script>
<div>
<table border="0" bordercolor="#ccc" cellpadding="5" cellspacing="0" style="border-collapse:collapse;" summary="test">
	<tbody>
		<tr>
			<td><span id="SpecialMessages"></span></td>
		</tr>
	</tbody>
</table>

<p>&nbsp;</p>
</div>

<div>Please click <a href="https://www.teamunify.com/cansksc/UserFiles/File/Fundraising/2015-16/INSTRUCTIONSTOUSETHEGIFTCARDONLINEORDERINGSYSTEM.pdf">here</a> for instructions.</div>

<p>&nbsp;</p>

<hr />
<form method="post" action="postdata.php">
<table id="cart">
	<thead>
		<tr>
			<td colspan="4">Account Email: <input id="setAccountBtn" onclick="changeAccount()" type="button" value="Set Account Email" /></td>
			<td colspan="3">Pickup date/time<br />
			<select id="Pickup"> <!-- 							<option value="" disabled selected>*Select Pickup Option</option> 							--> </select></td>
		</tr>
		<tr>
			<td colspan="3"><span id="AccountEmail"><input type="text"/></span></td>
		</tr>
		<tr>
			<th colspan="7">Current Gift Card Order:</th>
		</tr>
		<tr >
			<th>Vendor</th>
			<th>Card</th>
			<th>Count</th>
			<th>Subtotal</th>
			<th>Remit</th>
			<th>Recurring</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" style="text-align: center;">Order:<br />
			&nbsp;</td>
			<td id="cart_total_cost" style="text-align: right;">&nbsp;</td>
			<td id="cart_total_remit" style="text-align: right;">&nbsp;</td>
			<td colspan="2"><input onclick="submitCart()" style="background: RGB(128,255,128);" type="button" value="Update Order" /></td>
		</tr>
	</tfoot>
	<tbody>
		<tr>
			<td colspan="7"><span class="loading">Please wait, updating cart.</span></td>
		</tr>
		<tr>
			<td><select /></td><!--Vender-->
			<td><select /></td><!--Card-->
			<td><select /></td><!--Count-->
			<td witdh="10%"><input type="text" /></td><!--Subtotal-->
			<td witdh="100px"><input type="text" /></td><!--Remit-->
			<td witdh="10%"><input type="checkbox"  /></td><!--Recurring-->
			<td></td><!--Subtotal-->
		</tr>
	</tbody>
</table>

<p>&nbsp;</p>

<p><br />
<input checked="true" disabled="true" type="checkbox" />Recurring?</p>

<p>&nbsp;</p>

<table id="inventory">
	<thead>
		<tr>
			<th colspan="4" id="categories"><h2>InVentories:</h2></th>
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
						$url_inventory = "https://spreadsheets.google.com/feeds/list/1Kp0Lcneb_UUjE3Vi0FjpCNbXdTRLATjSpyNnLJx-E9Y/2/public/values?alt=json";
						$inventory = array("categories"=>array( ), "vendors"=>array());

						$json = file_get_contents($url_inventory);
						$data = json_decode($json, TRUE);
						$rows = $data['feed']['entry'];
						$total = 0;

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
