<?php
    $hostname   = "localhost";
    $username   = "postgres";  
    $password   = "Chyntia1809";      
    $database   = "wikitrip";

    $con = pg_connect("host=$hostname dbname=$database user=$username password=$password");

    if (!$con) {
        die("Koneksi ke database gagal: " . pg_last_error());
    }
?>