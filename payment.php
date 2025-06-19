<?php
$popup = "";

// Toggle this to TRUE when ready for live
$isLive = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = preg_replace('/^0/', '254', trim($_POST['mpesa_number']));
    $amount = trim($_POST['amount']);

    // Choose credentials + URLs
    if ($isLive) {
       $consumerKey = getenv('PESAPAL_CONSUMER_KEY');
        $consumerSecret = getenv('PESAPAL_CONSUMER_SECRET');

        $authUrl = 'https://pay.pesapal.com/v3/api/Auth/RequestToken';
        $submitUrl = 'https://pay.pesapal.com/v3/api/Transactions/SubmitOrderRequest';
    } else {
        $consumerKey = 'qkio1BGGYAXTu2JOfm7XSXNruoZsrqEW'; // Sandbox key
        $consumerSecret = 'osGQ364R49cXKeOYSpaOnT++rHs=';   // Sandbox secret
        $authUrl = 'https://sandbox.pesapal.com/v3/api/Auth/RequestToken';
        $submitUrl = 'https://sandbox.pesapal.com/v3/api/Transactions/SubmitOrderRequest';
    }

    // Encode credentials
    $credentials = base64_encode("$consumerKey:$consumerSecret");

    // Request token
    $ch = curl_init($authUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => ["Authorization: Basic $credentials"],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    $token = json_decode($response, true)['token'] ?? '';

    // Debug Output (remove or comment when live)
    echo "<pre>";
    echo "HTTP Code: $httpCode\n";
    echo "Raw Response:\n$response\n";
    echo "cURL Error:\n" . ($curlError ?: 'No error') . "\n";
    echo "</pre>";

    // Proceed if token was received
    if ($token) {
        $data = [
            "phone_number" => $phone,
            "amount" => $amount,
            "currency" => "KES",
            "reference" => "KelvinGraphics",
            "description" => "Service Payment",
            "callback_url" => "https://kgpayments.onrender.com/callback.php"

        ];

        $ch2 = curl_init($submitUrl);
        curl_setopt_array($ch2, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $token",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        $submitResponse = curl_exec($ch2);
        $code = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        curl_close($ch2);

        if ($code === 200) {
            $popup = "✅ Payment request sent! Check your phone to complete.";

            // CallMeBot notification
            $message = urlencode("📲 STK Push sent to $phone for KES $amount via Kelvin Graphics.");
            $callmebot_url = "https://api.callmebot.com/whatsapp.php?phone=254799800366&text=$message&apikey=7694151";

            $ch3 = curl_init($callmebot_url);
            curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch3);
            curl_close($ch3);
        } else {
            $popup = "❌ Payment failed – please try again.";
        }
    } else {
        $popup = "❌ Authentication failed. Contact support.";
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Kelvin Graphics | Secure Payment</title>
 <link rel="icon" href="favicon.png" type="image/png">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
:root {
  --primary: #6a1b9a;
  --primary-light: #f3e5f5;
  --accent: #8e24aa;
  --deep: #4a148c;
  --white: #ffffff;
  --gray: #444;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'Segoe UI', sans-serif;
  background: var(--primary-light);
  color: var(--gray);
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}
header {
  position: sticky;
  top: 0;
  background: var(--primary);
  color: white;
  padding: 10px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  z-index: 1000;
}
.logo {
  font-size: 1.4rem;
  font-weight: bold;
}
nav {
  position: relative;
}
.nav-toggle {
  display: none;
  font-size: 1.6rem;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
}
.nav-links {
  display: flex;
  gap: 15px;
}
.nav-links a {
  color: white;
  text-decoration: none;
  font-size: 0.95rem;
  padding: 6px 10px;
  border-radius: 5px;
  transition: background 0.3s;
}
.nav-links a:hover {
  background: rgba(255, 255, 255, 0.2);
}
@media (max-width: 600px) {
  .nav-toggle {
    display: block;
  }
  .nav-links {
    display: none;
    flex-direction: column;
    position: absolute;
    top: 100%;
    right: 0;
    background: var(--primary);
    width: 200px;
    padding: 10px;
    border-radius: 8px;
  }
  .nav-links.show {
    display: flex;
  }
}
.container {
  max-width: 420px;
  margin: auto;
  background: var(--white);
  padding: 30px 20px;
  border-radius: 12px;
  box-shadow: 0 8px 16px rgba(0,0,0,0.15);
  text-align: center;
  margin-top: 40px;
}
img.logo-img {
  width: 100px;
  margin-bottom: 15px;
}
h1 {
  color: var(--primary);
  font-size: 1.6rem;
  margin-bottom: 10px;
}
p.subtitle {
  color: var(--accent);
  margin-bottom: 20px;
  font-size: 1rem;
}
form input, form button {
  width: 100%;
  padding: 14px;
  margin: 10px 0;
  border-radius: 8px;
  border: 1px solid #ccc;
  font-size: 1rem;
}
form button {
  background: var(--primary);
  color: var(--white);
  font-weight: bold;
  cursor: pointer;
  transition: background 0.3s;
}
form button:hover {
  background: var(--accent);
}
.popup {
  background: #eee;
  padding: 14px;
  border-radius: 8px;
  margin-top: 15px;
}
footer {
  margin-top: auto;
  background: var(--deep);
  color: var(--white);
  padding: 14px;
  text-align: center;
  font-size: 0.9rem;
}
</style>
</head>
<body>

<header>
  <div class="logo">Kelvin Graphics</div>
  <nav>
    <button class="nav-toggle" onclick="toggleMenu()">☰</button>
    <div class="nav-links" id="navLinks">
      <a href="#">Home</a>
      <a href="https://kelvinkikwa.free.nf" target="_blank">Back to Services</a>
      <a href="https://kelvinkikwa.ct.ws" target="_blank">Leave a Review</a>
      <a href="https://wa.me/254799800366" target="_blank">Chat</a>
    </div>
  </nav>
</header>

<div class="container">
  <img src="https://static.cdnlogo.com/logos/m/95/m-pesa.svg" alt="M-Pesa" class="logo-img">
  <h1>Thank you for choosing Kelvin Graphics</h1>
  <p class="subtitle">You’ve already been served — please complete your payment securely below.</p>

  <form method="POST">
    <input type="text" name="mpesa_number" placeholder="Enter your M-Pesa number" required>
    <input type="number" name="amount" placeholder="Enter amount (KES)" required>
    <button type="submit">Pay Now</button>
  </form>

  <?php if (!empty($popup)): ?>
    <div class="popup"><?= htmlspecialchars($popup) ?></div>
  <?php endif; ?>
</div>

<footer>
  © 2025 Kelvin Graphics | Online payment system
</footer>

<script>
function toggleMenu() {
  const nav = document.getElementById('navLinks');
  nav.classList.toggle('show');
}
document.addEventListener('click', function(e) {
  const nav = document.getElementById('navLinks');
  const toggle = document.querySelector('.nav-toggle');
  if (!nav.contains(e.target) && !toggle.contains(e.target)) {
    nav.classList.remove('show');
  }
});
</script>

</body>
</html>
