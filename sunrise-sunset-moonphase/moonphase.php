<?php
// Your WeatherAPI key
$apiKey = '[insert your weatherapi.com key here]';

// API URL (change location)
$url = "https://api.weatherapi.com/v1/astronomy.json?key=$apiKey&q=auto:[state, country]";

$response = file_get_contents($url);
if ($response === FALSE) {
    // Handle error if needed
    exit;
}

$data = json_decode($response, true);
if (!$data || !isset($data['astronomy']['astro'])) {
    exit; // Invalid data
}

$astro = $data['astronomy']['astro'];
$phase = $astro['moon_phase'];
$sunset = $astro['sunset'];
$sunrise = $astro['sunrise'];

$icons = [
    "New Moon" => "🌑",
    "Waxing Crescent" => "🌒",
    "First Quarter" => "🌓",
    "Waxing Gibbous" => "🌔",
    "Full Moon" => "🌕",
    "Waning Gibbous" => "🌖",
    "Last Quarter" => "🌗",
    "Waning Crescent" => "🌘"
];

$icon = $icons[$phase] ?? "🌙";

$output = [
    "moon_phase" => "$icon $phase",
    "sunset" => "🌇 Sunset: $sunset",
    "sunrise" => "🌅 Sunrise: $sunrise",
    "link" => "https://weatherapi.com/astronomy"
];

// Save JSON file somewhere in your webroot (so gethompage.dev widget can fetch it)
file_put_contents('/path/to/your/public_html/moonphase.json', json_encode($output));

echo "Moon phase updated.";
?>
