<?php
 
function connectToDynamics365Contacts($organizationUrl, $clientId, $clientSecret, $resourceUrl) {
    // Define the token endpoint URL
    $tokenUrl = "https://login.microsoftonline.com/common/oauth2/token";

    // Prepare the token request data
    $tokenData = array(
        'grant_type' => 'client_credentials',
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'resource' => $resourceUrl
    );

    // Initialize cURL session for token retrieval
    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));

    // Execute the token request
    $tokenResponse = curl_exec($ch);
    curl_close($ch);

    // Parse the token response JSON
    $tokenInfo = json_decode($tokenResponse);

    if ($tokenInfo && isset($tokenInfo->access_token)) {
        // Use the access token to make API requests
        $accessToken = $tokenInfo->access_token;

        // Example API request
        $apiUrl = "$organizationUrl/api/data/v9.2/";
        $apiEndpoint = "contacts";
        
        $ch = curl_init($apiUrl . $apiEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer $accessToken",
            "OData-MaxVersion: 4.0",
            "OData-Version: 4.0",
            "Accept: application/json",
            "Content-Type: application/json"
        ));

        $apiResponse = curl_exec($ch);
        curl_close($ch);

        // Handle the API response here (e.g., parse JSON and process data)
        return $apiResponse;
    } else {
        // Handle token retrieval failure
        return false;
    }
}

function getAccessToken($clientId, $clientSecret, $tenantId) {
    $tokenUrl = "https://login.microsoftonline.com/$tenantId/oauth2/token";
    
    $data = array(
        'grant_type' => 'client_credentials',
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'resource' => 'https://graph.microsoft.com'
    );

    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        )
    );

    $context = stream_context_create($options);
    $response = file_get_contents($tokenUrl, false, $context);

    $json = json_decode($response, true);

    if (isset($json['access_token'])) {
        return $json['access_token'];
    } else {
        throw new Exception("Error getting access token");
    }
}

function getContacts($accessToken, $url) {

    $options = array(
        'http' => array(
            'header' => "Authorization: Bearer $accessToken\r\n",
            'method' => 'GET'
        )
    );

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    return $response;
}

// Replace with your actual values
$organizationUrl = "https://aucklandbusinesschamber-test.api.crm6.dynamics.com";
$dynamicCrmResourceUrl = "https://aucklandbusinesschamber-test.api.crm6.dynamics.com";
$graphResourceUrl = 'https://graph.microsoft.com/v1.0/me/contacts';

$clientId = 'e7fcb77a-16e4-408e-a5e4-a1bdd68e0562';
$clientSecret = 'EOY8Q~1Yk2vX0.WW3a0s1WsRxPjMFDyZmuZ_edtb';
$tenantId = '3b42ea33-61d8-4ea8-a1ec-bf50157ec715';

try {
    $accessToken = getAccessToken($clientId, $clientSecret, $tenantId);
    $contactResponse = getContacts($accessToken, $graphResourceUrl);
    
    // Process $apiResponse as needed
    echo "ACCESS TOKEN:<BR>";
    echo $accessToken;
    echo '<br>===========<br>';
    echo "Data:<BR>";
    echo $contactResponse;
    echo '<br>===========<br>';
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

?>
