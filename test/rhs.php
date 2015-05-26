<?php
header('Content-Type: text/html; charset=utf-8');
//mb_internal_encoding("UTF-8"); 

include("../helpers/scraper.php");

$s = new scraper(1);

/*
////Get Page Set 1
$s->loadHTML('http://apps.rhs.org.uk/rhsgardenfinder/');
$s->loadDOM('<select name="cboCounty" id="cboCounty"','</select>');
$s->createList("//option/@value");
$s->buildURLs("http://apps.rhs.org.uk/rhsgardenfinder/gardenfinder2.asp?garden=&cboCounty={p1}&Search=Search");
$s->printResults();
echo '<textarea rows="10" cols="170">'.$s->rawhtml."</textarea>";
*/

/*
////Get Page Set 2
//Search Results Tabs
$s->loadHTML('http://apps.rhs.org.uk/rhsgardenfinder/gardenfinder2.asp?garden=&cboCounty=Kent&Search=Search');
$s->loadDOM('<div class="num">','</div>');
$s->createList("//a/@href");
$s->filterUrlParam('page');
$param = $s->getURLParams();
$s->buildURLs("http://apps.rhs.org.uk/rhsgardenfinder/gardenfinder2.asp?page={p1}&cboCounty=".$param['cboCounty']);

$s->printResults();
*/

//Garden Pages
$s->loadHTML('http://apps.rhs.org.uk/rhsgardenfinder/gardenfinder2.asp?page=1&cboCounty=Kent');
$s->loadDOM('<table class="mcl results fifty50">','</table>');
$s->createList("//p/a/@href");
$s->filterUrlParam('id');
$param = $s->getURLParams();
$s->buildURLs("http://apps.rhs.org.uk/rhsgardenfinder/gardenfinder3.asp?id={p1}&cboCounty=".$param['cboCounty']);
$debug = $s->getDebug();

echo '<h3>URL</h3><textarea cols="150" rows="10">'.$debug['url'].'</textarea><br>';
echo '<h3>URL Params</h3><textarea cols="150" rows="10">'.$debug['url_params'].'</textarea><br>';
echo '<h3>Original HTML</h3><textarea cols="150" rows="10">'.$debug['orig_html'].'</textarea><br>';
echo '<h3>Tidy HTML</h3><textarea cols="150" rows="10">'.$debug['html'].'</textarea><br>';
echo '<h3>XML</h3><textarea cols="150" rows="10">'.$debug['xml'].'</textarea><br>';
echo '<h3>Debug Log</h3><textarea cols="150" rows="10">'.$debug['debug_log'].'</textarea><br>';
echo '<h3>Results</h3><textarea cols="150" rows="10">'.$debug['results'].'</textarea><br>';

/*
//Garden Details
$s->loadHTML('src/Wirral.htm');


$s->loadDOM('<div class="span-7"><h2>','</h2>');
$s->createContent("//h2");
$res['title'] = $s->getResults();

$s->loadDOM('<td><h4>Address</h4></td>','</tr>');
$s->createContent("//p");
$res['address'] = $s->getResults();
$s->filterPostcode();
$res['postcode'] = $s->getResults();

$s->loadDOM('<td><h4>Telephone</h4></td>','</tr>');
$s->createContent("//p");
$res['phone'] = $s->getResults();

$s->loadDOM('<td><h4>website</h4></td>','</tr>');
$s->createContent("//p");
$res['website'] = $s->getResults();

$s->loadDOM('<td><h4>Opening Times</h4></td>','</tr>');
$s->createContent("//p");
$res['openingtimes'] = $s->getResults();

$s->loadDOM('<td><h4>Admission</h4></td>','</tr>');
$s->createContent("//p");
$res['admission'] = $s->getResults();

$s->loadDOM('<td><h4>Comment</h4></td>','</tr>');
$s->createContent("//p");
$res['comment'] = $s->getResults();


echo "<pre>";
print_r($res);
echo "</pre>";
*/
?>