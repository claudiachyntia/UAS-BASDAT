<?php

    $hostname   = "localhost";
    $username   = "root";
    $password   = "";
    $database   = "wikitrip";

    $con = new mysqli($hostname, $username, $password, $database) or die (mysqli_error($con));
?>