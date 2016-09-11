

<?php

$url = "https://spreadsheets.google.com/feeds/list/1Kp0Lcneb_UUjE3Vi0FjpCNbXdTRLATjSpyNnLJx-E9Y/2/public/values?alt=json";

$json = file_get_contents($url);
$data = json_decode($json, TRUE);

//print_r ($data);

$rows = $data['feed']['entry'];
$total = 0;

foreach ($rows as $item) {
  $vender   = $item['gsx$Vender']['$t'];
  $category =  $item['gsx$category']['$t'];
  echo "Vender: ". $vender . "  Type: " . $category . '<br>';
  $total =$total +1;
  
}

echo "Total:". $total;

?>
