<?php
header('Content-Type: text/html; charset=utf-8');

include("class_scrape.php");
include("model_rf_scrape.php");

////Log Codes
//1 = Scrape Start
//2 = URLS to Scrape
//3 = URLS Found
//4 = Places Found
//127 = Scrape End

$rf = new rf_scrape();

//Get URLS that need scrapeing
$rf->getScrapeURLs();
$rf->printResults($rf->urls);
?>