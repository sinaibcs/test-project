<?php
$hostname = gethostname();

// Resolve the hostname to an IP address
$local_ip = gethostbyname($hostname);

echo "Local IP Address: " . $local_ip;
die;
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://prportal.nidw.gov.bd/partner-service/rest/auth/login',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "username":"dss_systemadmin",
    "password":"nidwAPI@%#2024"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
