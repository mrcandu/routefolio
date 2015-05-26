<?php
class scraper {
	
	private $debug;
	private $debug_log;
	
	private $url;
	private $orig_html;
	private $html;
	private $xml;
	private $results;
	
	//Construct
	public function __construct($debug="") {
		
	  if($debug == 1){
	    $this->debug = 1;
	  }

	}

    /////////////////////Load Methods
	
		
	//LoadHTML
	public function loadHTML($u) {
		$curl_handle=curl_init();
        curl_setopt($curl_handle, CURLOPT_URL,$u);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Your application name');
        
		$h = curl_exec($curl_handle);
        curl_close($curl_handle);
		
		$this->url = $u;

		if($this->debug == 1){
		  $this->orig_html = $h;
		}
		
		$this->html = $this->tidyHTML($h);

		if($this->debug==1){
 		  $this->debug_log[] = __FUNCTION__ . " (" . implode(",", func_get_args()) . ")";
		}
		
	}
	
	//LoadDom
	public function loadDom($s="",$e="") {
		
		unset($this->xml);
		unset($this->results);
		$this->results = "";
		
		$h = $this->html;
		if($s!="" and $e!=""){
			$h = $this->trimHTML($h,$s,$e);
		}
		if($h!=false){
		  $d = @DOMDocument::loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">'.$h);
		  $this->xml = simplexml_import_dom($d);
		}
		
		if($this->debug==1){
 		  $this->debug_log[] = __FUNCTION__ . " (" . implode(",", func_get_args()) . ")";
		}
		
	}

    /////////////////////Create Methods

	//Create array list from DOM xpath query
	public function createList($q) {
        		
		if(isset($this->xml)){
		
		  $xml = $this->xml->xpath($q);

		  foreach($xml as $k){
		    if($k->count()>0) {
              foreach($k as $k2){
			    if($k2[0]!="" and $k2[0]!="null"){
                  $a[] = $k2[0]->__toString();
		  	    }
              }
		    }
		    else {
			  if($k[0]!="" and $k[0]!="null"){
			    $a[] = $k[0]->__toString();
			  }
		    }
          }	
		
		  $this->results = $a;
		
		}

		if($this->debug==1){
 		  $this->debug_log[] = __FUNCTION__ . " (" . implode(",", func_get_args()) . ")";
		}
		
	}

	//Create string from DOM xpath query
	//create a string from a pattern match in the html
	public function createContent($q,$a=0) {
		
		if(isset($this->xml)){
			
		  $xml = $this->xml->xpath($q);
		  
		  //Attribute Value
		  if($a==1){
		    $r = $xml[0]->__toString();
		  }
		  //Other Value
		  else {
		    $r = $xml[0]->asXML();
		    $r = htmlspecialchars_decode($r);
		    $r = strip_tags($r);
		    $r = trim($r);
		  }
          $this->results = $r;

		}
		
		if($this->debug==1){
 		  $this->debug_log[] = __FUNCTION__ . " (" . implode(",", func_get_args()) . ")";
		}
		
	}

	//Create string from HTML preg_match_query
	//create a string from a pattern match in the html
	public function createString($pattern) {
		  preg_match($pattern,$this->html, $matches);
		  $a = $matches[1];
		  $this->results = $a;
		  
		if($this->debug==1){
 		  $this->debug_log[] = __FUNCTION__ . " (" . implode(",", func_get_args()) . ")";
		}
		  
	}
	
    /////////////////////Filter Results

	//Exract URL parameter from results array
	public function filterUrlParam($k) {
        if(!empty($this->results)){
		  foreach($this->results as $u){	
		    $q = parse_url($u, PHP_URL_QUERY);
		    parse_str($q, $p);
		    $a[] = $p[$k];
		  }
		  $this->results = $a;
		}

		if($this->debug==1){
 		  $this->debug_log[] = __FUNCTION__ . " (" . implode(",", func_get_args()) . ")";
		}
		
	}		

	//Exract string from results array
	public function filterUrlElement($pattern) {
        if(!empty($this->results)){
		  foreach($this->results as $u){
			unset($matches)	;
		    preg_match($pattern,$u, $matches);
			$a[] = $matches[1];
		  }
		  $this->results = $a;
		}
		
		if($this->debug==1){
 		  $this->debug_log[] = __FUNCTION__ . " (" . implode(",", func_get_args()) . ")";
		}
		
	}
	
	//filter Postcodes
	public function filterPostcode()
    {
	  if(!empty($this->results)){
	    $pattern = "/((GIR 0AA)|((([A-PR-UWYZ][0-9][0-9]?)|(([A-PR-UWYZ][A-HK-Y][0-9][0-9]?)|(([A-PR-UWYZ][0-9][A-HJKSTUW])|([A-PR-UWYZ][A-HK-Y][0-9][ABEHMNPRVWXY])))) [0-9][ABD-HJLNP-UW-Z]{2}))/i"; 
        preg_match($pattern,$this->results, $matches); 
        $this->results = $matches[0];	
	  }
	  
		if($this->debug==1){
 		  $this->debug_log[] = __FUNCTION__ . " (" . implode(",", func_get_args()) . ")";
		}
	  
	}

    /////////////////////Build Methods

	//Create Tabs
	//e.g. used for xml where total results cnt exists and there is the ability to offset the results so scrape can be done in stages (tabs)
	public function buildTabs($l) {
		if(!empty($this->results)){
		  $sr = $this->results[0];
		  unset($this->results);
          for ($i = 0; ; $i++) {
            if ($i*$l > $sr) {
              break;
            }
	        $this->results[] = $i*$l;
          }
		}
		
		if($this->debug==1){
 		  $this->debug_log[] = __FUNCTION__ . " (" . implode(",", func_get_args()) . ")";
		}
		
	 }
	
	//Create URLS
	//e.g. build urls from results
	public function buildURLs($u) {
		if(!empty($this->results)){
		  foreach($this->results as $k => $v){
			$a[] = str_replace("{p1}",urlencode($v),$u);
		  }
		  $this->results = $a;
		}
		
		if($this->debug==1){
 		  $this->debug_log[] = __FUNCTION__ . " (" . implode(",", func_get_args()) . ")";
		}
		
	}

	//Build String
	//e.g. concatenate string with separator, e.g address
	public function buildString($sep) {
		if(!empty($this->results)){
		  $a = "";
		  foreach($this->results as $k => $v){
			$a .= $v.$s;
		  }
		  $this->results = substr($a,0,-strlen($s));
		}
		
		if($this->debug==1){
 		  $this->debug_log[] = __FUNCTION__ . " (" . implode(",", func_get_args()) . ")";
		}
		
	}	
    /////////////////////Get / Print
	
	//Print Results
	public function printResults() {
      echo "<pre>";
      print_r($this->results);
      echo "</pre>";
	}

	//Get Results
	public function getResults() {
      return $this->results;
	}

	//Get URL Paramters
	public function getURLParams() {
	  $q = parse_url($this->url, PHP_URL_QUERY);
	  parse_str($q, $p);
	  return $p;
	  
		if($this->debug==1){
 		  $this->debug_log[] = __FUNCTION__ . " (" . implode(",", func_get_args()) . ")";
		}
	  
	}
	
	//Get Debug
	public function getDebug() {
	  
	  if($this->debug==1){
		  
	    $debug['url'] = $this->url;
	    $debug['url_params'] = print_r($this->getURLParams(),true);
	    $debug['orig_html'] = $this->orig_html;
	    $debug['html'] = $this->html;
		
		$x = new DOMDocument;
		$x->formatOutput = true;
        $x->loadHTML($this->xml->asXML());
        $debug['xml'] = $x->saveXML();
	    //$debug['xml'] = $this->xml->asXML();
		
	    $debug['debug_log'] = print_r($this->debug_log,true);
	    $debug['results'] = print_r($this->results,true);
		  
      return $debug;
	  }
	  
	}
		
	
			
    /////////////////////Private Helpers
	
	//trimHTML
	private function trimHTML($h,$s,$e) {	
	    $error="";	
		$charAt = stripos($h,$s);
		if($charAt!=false){
		  $error=0;
          $h = substr($h,$charAt,strlen($h));
          $charLast = stripos($h,$e);
		  if($charLast!=false){
			$error=0;
		    $h = substr($h, 0,$charLast+strlen($e));
		  }
		}
		if($error===0){
		  return $h;
		}
		else{
		  return false;
		}
	}
		
	//tidyHTML
	private function tidyHTML($h)
    {
      $h = trim($h);
	  $h = preg_replace('~>\s+<~', '><', $h); //Remove space between html elements
	  $h = preg_replace('/\s\s+/', ' ', $h); //Remove more then 1 space
	  $h = preg_replace(array('/<br \/>/','/<br \/>/','/<br \/>/'),"\n", $h); //convert br tags to line breaks
	  $h = preg_replace(array('/\n\s/','/\s\n/'), "\n", $h); //remove start / end of line spaces
      return $h;
    }	
	
}
?>