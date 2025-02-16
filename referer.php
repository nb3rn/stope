<?php
set_time_limit(0);
ignore_user_abort(true);

$baseUrl = "https://www.insurancegogogo.com/4987/Bolaarena1/";
$m3u8Url = "https://www.insurancegogogo.com/4987/Bolaarena1/playlist.m3u8?|Referer=https://www.kds.tw/";
$outputFolder = "hls_" . time();
mkdir($outputFolder, 0777, true);
$outputM3U8 = "$outputFolder/arenabola.m3u8";

function downloadFile($url, $path) {
    $opts = [
        "http" => [
            "header" => "Referer: https://www.kds.tw/\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    file_put_contents($path, file_get_contents($url, false, $context));
}

while (true) {
    echo "Fetching playlist...\n";
    $opts = ["http" => ["header" => "Referer: https://www.kds.tw/\r\n"]];
    $context = stream_context_create($opts);
    $playlist = file_get_contents($m3u8Url, false, $context);
    
    if ($playlist === false) {
        echo "Failed to fetch playlist. Retrying...\n";
        sleep(5);
        continue;
    }
    
    $lines = explode("\n", $playlist);
    $newPlaylist = [];
    $currentTsFiles = [];

    foreach ($lines as $line) {
        if (strpos($line, ".ts") !== false) {
            $tsFile = basename(trim($line));
            $tsUrl = $baseUrl . $tsFile;
            $tsPath = "$outputFolder/$tsFile";
            
            if (!file_exists($tsPath)) {
                echo "Downloading: $tsFile\n";
                downloadFile($tsUrl, $tsPath);
            }
            $newPlaylist[] = $tsFile;
            $currentTsFiles[] = $tsFile; // Track current .ts files
        } else {
            $newPlaylist[] = $line;
        }
    }
    
    // Save the updated playlist
    file_put_contents($outputM3U8, implode("\n", $newPlaylist));
    echo "Updated playlist saved as: $outputM3U8\n";

    // Delete .ts files not in the current playlist
    $allTsFiles = glob("$outputFolder/*.ts");
    foreach ($allTsFiles as $tsFile) {
        $tsFileName = basename($tsFile);
        if (!in_array($tsFileName, $currentTsFiles)) {
            echo "Deleting unused .ts file: $tsFileName\n";
            unlink($tsFile);
        }
    }
    
    sleep(10); // Adjust based on segment duration
}