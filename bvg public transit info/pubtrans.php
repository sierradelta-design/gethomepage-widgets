<?php
// TIMEZONE (CET / CEST with DST)
date_default_timezone_set('Europe/Berlin');

// CONFIG
// Add or edit stop(s) to suit your needs, reference https://v6.vbb.transport.rest/getting-started.html for how to obtain stop id
$stops = [
    "Jakob Kaiser Platz" => "900018101"
];
$duration = 30; // minutes ahead
$subway = "true";
$bus = "true";
$tram = "false";
// Save JSON file somewhere in your webroot (so gethompage.dev widget can fetch it)
$outputFile = '/path/to/your/public_html/pubtrans/pubtrans.json';

// BUS DIRECTION BUCKETS (based on end-stops)
// Add or edit end-stops manually. The end-stops for this example can be referenced here: https://www.bvg.de/de/verbindungen/stationsuebersicht/u-jakob-kaiser-platz
$busDirectionMap = [
    "north" => [
        "Rosenthal Nord",
        "Märkisches Viertel",
        "Quickborner Str.",
        "S+U Hauptbahnhof",
        "Rathaus Spandau"
    ],
    "south" => [
        "S+U Jungfernheide",
        "S+U Zoologischer Garten",
        "Mäckeritzwiesen",
        "Rudow",
        "Flughafen BER"
    ]
];

// HELPERS
function classifyDirection(string $direction, array $directionMap): ?string {
    foreach ($directionMap as $bucket => $keywords) {
        foreach ($keywords as $word) {
            if (stripos($direction, $word) !== false) {
                return $bucket;
            }
        }
    }
    return null;
}

// Clock emoji or warning sign + actual time
function formatTimeWithClock(string $isoTime, bool $delayed = false): string {
    $dt = new DateTime($isoTime);
    $dt->setTimezone(new DateTimeZone('Europe/Berlin'));

    if ($delayed) {
        return '⚠️' . $dt->format("H:i"); // warning emoji + actual arrival
    }

$hour = (int)$dt->format('G');  // 0–23
$minute = (int)$dt->format('i');

// Convert to 12-hour format
$hour12 = $hour % 12;
if ($hour12 === 0) $hour12 = 12;

$fullEmojis = ["🕛","🕐","🕑","🕒","🕓","🕔","🕕","🕖","🕗","🕘","🕙","🕚"];
$halfEmojis = ["🕧","🕜","🕝","🕞","🕟","🕠","🕡","🕢","🕣","🕤","🕥","🕦"];


// OFFSET LOGIC
if ($minute >= 50) {
    // jump to next hour full emoji
    $emojiHour = ($hour12 % 12); // next hour index
    $emoji = $fullEmojis[$emojiHour];
} elseif ($minute >= 20) {
    // show half-hour emoji
    $emoji = $halfEmojis[$hour12 % 12];
} else {
    // normal full-hour emoji
    $emoji = $fullEmojis[$hour12 % 12];
}

return $emoji . $dt->format("H:i");
}

// FETCH + PROCESS
$results = [];

foreach ($stops as $stopName => $stopId) {
    $results["$stopName North"] = ["ubahn" => [], "bus" => []];
    $results["$stopName South"] = ["ubahn" => [], "bus" => []];

    $url = "https://v6.vbb.transport.rest/stops/$stopId/departures?duration=$duration&subway=$subway&bus=$bus&tram=$tram";
    $response = @file_get_contents($url);
    if ($response === false) continue;

    $data = json_decode($response, true);
    if (!isset($data['departures'])) continue;

    $northBus = [];
    $southBus = [];

    foreach ($data['departures'] as $d) {
        if (!isset($d['line'], $d['direction']) || (!isset($d['when']) && !isset($d['plannedWhen']))) continue;

        $line = $d['line'];
        $directionText = $d['direction'];
        $arrival = $d['when'] ?? $d['plannedWhen'];
        $planned = $d['plannedWhen'] ?? $d['when'];
        $delayed = $arrival !== $planned;
        $time = formatTimeWithClock($arrival, $delayed);

        // SUBWAY LOGIC
        // Add or edit end-stops manually. The end-stops for this example can be referenced here: https://www.bvg.de/de/verbindungen/stationsuebersicht/u-jakob-kaiser-platz
        if (isset($line['productName'], $line['name']) && $line['productName'] === "U") {
            $bucket = classifyDirection($directionText, ["north" => ["Rathaus Spandau"], "south" => ["Rudow"]]);
            $targetKey = "$stopName " . ucfirst($bucket ?? "North");
            if (count($results[$targetKey]['ubahn']) < 6) {
                $results[$targetKey]['ubahn'][] = "{$line['name']}→$directionText $time";
            }
            continue;
        }

        // BUS LOGIC
        if (isset($line['product'], $line['name']) && $line['product'] === "bus") {
            $bucket = classifyDirection($directionText, $busDirectionMap);
            if (!$bucket) continue;

            $busEntry = "{$line['name']}→$directionText $time";
            if ($bucket === "north") $northBus[] = $busEntry;
            if ($bucket === "south") $southBus[] = $busEntry;
        }
    }

    // SORT BUS ENTRIES CHRONOLOGICALLY
    $timeSort = function($a, $b) {
        preg_match('/(\d{2}:\d{2})$/', $a, $mA);
        preg_match('/(\d{2}:\d{2})$/', $b, $mB);

        // Delayed entries (⚠️) go last
        if (!isset($mA[1])) return 1;
        if (!isset($mB[1])) return -1;

        $timeA = DateTime::createFromFormat('H:i', $mA[1]);
        $timeB = DateTime::createFromFormat('H:i', $mB[1]);

        return $timeA <=> $timeB;
    };

    usort($northBus, $timeSort);
    usort($southBus, $timeSort);

    $results["$stopName North"]['bus'] = array_slice($northBus, 0, 6);
    $results["$stopName South"]['bus'] = array_slice($southBus, 0, 6);
}

// WRITE OUTPUT
file_put_contents($outputFile, json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Public transport departures updated.\n";
