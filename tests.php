<?php
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "http://localhost:8080/mesas/estadisticas/mas-usada?from=2026-01-01&to=2026-12-31");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer TU_TOKEN"
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;