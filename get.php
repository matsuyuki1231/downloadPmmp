<?php
$currentDir = dirname(__FILE__);
echo "[1]Downloading PHP binary". PHP_EOL. "[2]Downloading PocketMine-MP src";
$ctx = stream_context_create([
    "http" => [
        "method" => "GET",
        "header" => ["User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36"],
    ],
    "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false
    ]
]);
$releases = json_decode(file_get_contents(
        "https://api.github.com/repos/pmmp/".
        (($isBinary = str_starts_with(fgets(STDIN), "1"))?"PHP-Binaries":"PocketMine-MP")
        . "/releases"
    , false, $ctx), true);
$stdout = "";
foreach ($releases as $index => $release) {
    $stdout .= PHP_EOL. "[". ($index + 1). "]". $release["name"]. ($release["prerelease"]?" (Pre)":"");
}
echo $stdout;
$stdout = "";
foreach ($releases[$version = ((int) fgets(STDIN)) - 1]["assets"] as $index => $asset) {
    $stdout .= PHP_EOL. "[". ($index + 1). "]". $asset["name"];
}
echo $stdout;
$type = ((int) fgets(STDIN)) - 1;
if ($isBinary && is_dir($currentDir. DIRECTORY_SEPARATOR. "bin")) {
    echo PHP_EOL. "Started removing legacy binary...";
    rmTree($currentDir. DIRECTORY_SEPARATOR. "bin");
    echo PHP_EOL. "Ended removing legacy binary...";
}
echo PHP_EOL. "Started downloading ". ($isBinary?"PHP binary":$releases[$version]["assets"][$type]["name"]. " of"). " ". $releases[$version]["name"]. "...";
$bin = file_get_contents($releases[$version]["assets"][$type]["browser_download_url"], false, $ctx);
file_put_contents($currentDir. DIRECTORY_SEPARATOR. ($isBinary?"bin.zip":$releases[$version]["assets"][$type]["name"]), $bin);
echo PHP_EOL. "Ended downloading";
if ($isBinary) {
    echo PHP_EOL. "Started zip extracting...";
    $zip = new ZipArchive();
    echo PHP_EOL. (($zip->open($currentDir. DIRECTORY_SEPARATOR. "bin.zip") && $zip->extractTo("."))?"Succeed":"Failed")
        . "extracting.";
    echo PHP_EOL. "Remove zip file? (y/n)";
    if (in_array(substr(fgets(STDIN), 0, 1), ["y", "1"])) {
        echo PHP_EOL. (unlink($currentDir. DIRECTORY_SEPARATOR. "bin.zip")?"Succeed":"Failed"). " removing zip file.";
    }
}

function rmTree($filename):bool {
    if (is_dir($filename)) return false;
    $tree = glob($filename. DIRECTORY_SEPARATOR. "*");
    foreach ($tree as $file) {
        if (is_file($file)) unlink($file);
        else rmTree($file);
    }
    rmdir($filename);
    return true;
}
