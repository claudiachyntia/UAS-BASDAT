<?php
// URL RSS Feed
$rss_url = "https://disbudparekraf.sumutprov.go.id/category/event/feed/";

// Fungsi untuk membaca RSS
function fetch_rss($url) {
    $rss = simplexml_load_file($url); // Membaca file RSS
    return $rss;
}

// Ambil data dari RSS
$rss_data = fetch_rss($rss_url);
?>
