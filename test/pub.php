<?php
header('Content-Type: text/html; charset=utf-8');

include("class_scrape.php");

$s = new scrape();

////Get Page Set 1
//Load HTML
$s->loadHTML('src/kent.xml');

$s->loadDOM();
$s->createList("//request/@search-count");
$s->buildTabs(50);
$s->buildURLs("http://www.thegoodpubguide.co.uk/locator/find///pub_county/(extsource_gpgdata%3A%3Agpg_category%3A(M)%20OR%20extsource_gpgdata%3A%3Agpg_category%3A(L))%20AND%20(pub%3A%3Apub_county%3A%22%22)/50/{p1}/");
//$s->printResults();

////Get Page Set 2
$s->createList("//marker/@url");
$s->buildURLs("http://www.thegoodpubguide.co.uk{p1}");
//$s->printResults();

////Get Page Set 3 - Pub Details
$s->loadHTML('src/GBG-pub.htm');

$s->loadDOM();
$s->createContent("//title");
$res['page_title'] = $s->getResults();

$s->loadDOM();
$s->createContent("//meta[@name='description']/@content",1);
$res['page_desc'] = $s->getResults();

$s->loadDOM('<div class="zone-block-container">','</div>');
$s->createContent("//h1");
$res['title'] = $s->getResults();

$s->loadDOM();
$s->createContent("//div[contains(@class,'eztext-content')]");
$res['desc'] = $s->getResults();

$s->loadDOM('<dt><img src="/extension/gpgdesign/design/standard/images/contact-details.png" class="rubric-icon" title="Address" /><span class="rubric-icon">','</dd>');
$s->createList("//div");
$s->buildString("\n");
$res['address'] = $s->getResults();
$s->filterPostcode();
$res['postcode'] = $s->getResults();

$s->createString("/var lat = ([0-9-.]+);/");
$res['lat'] = $s->getResults();

$s->createString("/var lng = ([0-9-.]+);/");
$res['lng'] = $s->getResults();

echo "<pre>";
print_r($res);
echo "</pre>";
?>