<?php
class rf_scrape {

    // define properties
    private $db;
	private $dbconn;
	private $batch_id;
	private $urls;
	private $url_scripts;
	private $scripts;
	
	public $log = 1;
	
	public $debug = 0;
	private $debug_log;
	  			
	//Construct
	public function __construct() {
	    
        $this->db = new db;
		$this->dbconn = $this->db->dbconn;
			  
	}
	
    //createScrapeBatch 
	private function createScrapeBatch() {

      $sql = $this->dbconn->prepare("INSERT INTO scrape_batch (batch_id) VALUES (NULL)");
      $this->dbconn->beginTransaction(); 
      $sql->execute();		
	  $this->batch_id = $this->dbconn->lastInsertId();
	  $this->dbconn->commit();
	  $this->logScrape(1,'0','','','','');

	}

    //createScrapeURLs 
	private function createScrapeURLs($url_id="") {

	  //Find URLs to scrape and create batch urls	
	  if($url_id==""){
        $sql = $this->dbconn->prepare("INSERT INTO scrape_batch_urls (batch_id,url_id) SELECT ".$this->batch_id.", url_id FROM scrape_urls WHERE scrape_dt IS NULL ORDER BY url_id LIMIT 1");
	    $sql->execute();
	  }
	  //Srape Selected URL
	  else{
	    $sql = $this->dbconn->prepare("INSERT INTO scrape_batch_urls (batch_id,url_id) SELECT ".$this->batch_id.", url_id FROM scrape_urls WHERE url_id = ".$url_id." LIMIT 1");
	    $sql->execute();
	  }

	}
	
    //getScrapeURLs 
	private function getScrapeURLs() {
		
	  $this->urls = "";
	  	  
	  //Get the list of URLs to be scraped
      $sql = $this->dbconn->prepare("SELECT u.url_id,u.site_id,u.url,c.script_id FROM scrape_urls u INNER JOIN scrape_script c ON u.script_id = c.script_id INNER JOIN scrape_batch_urls bu ON u.url_id = bu.url_id WHERE bu.batch_id = ".$this->batch_id);	  
	  $sql->execute();
      $this->urls = $sql->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
	  $this->urls = array_map('reset', $this->urls);

      //Log - no.of urls
      $this->logScrape(2,'0','','','',count($this->urls));
	}

    //getScrapeURLScripts 
	private function getScrapeURLScripts() {
		
	  $this->url_scripts = "";
	  
      //Get the script blk records for each url to be scraped
      $sql = $this->dbconn->prepare("SELECT u.url_id, cb.script_blk_id FROM scrape_urls u INNER JOIN scrape_script c ON u.script_id = c.script_id INNER JOIN scrape_script_blk cb ON c.script_id = cb.script_id INNER JOIN scrape_batch_urls bu ON u.url_id = bu.url_id WHERE bu.batch_id = ".$this->batch_id);	  
	  $sql->execute();
	  $this->url_scripts = $sql->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

	}

    //getScrapeScripts 
	private function getScrapeScripts() {
	  
	  $this->scripts = "";
	  
	  //Get the scripts to process the urls
      $sql = $this->dbconn->prepare("SELECT cb.script_blk_id,cb.script_code,cb.next_script_id FROM scrape_urls u INNER JOIN scrape_script c ON u.script_id = c.script_id INNER JOIN scrape_script_blk cb ON c.script_id = cb.script_id INNER JOIN scrape_batch_urls bu ON u.url_id = bu.url_id WHERE bu.batch_id = ".$this->batch_id);	  
	  $sql->execute();
	  
	  $this->scripts = $sql->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
	  $this->scripts = array_map('reset', $this->scripts);

	}
	
	//createUrl 
	private function createUrl($site,$url_id,$url,$script_id) {
		if($this->debug!=1){
          $sql = $this->dbconn->prepare("INSERT IGNORE INTO scrape_urls (site_id,parent_url_id,url,script_id) values (".$site.",".$url_id.",'".$url."',".$script_id.")");
          $sql->execute();
		  $rows = $sql->rowCount();
		}
	}

	//createPlace 
	private function createPlace($r,$s,$u) {
	    if($this->debug!=1){	
		  $smd = "REPLACE INTO scrape_place (site_id,url_id,place_name,place_url,place_desc,place_addr,place_postcode) values (".$s.",".$u.",'".addslashes($r['place_name'])."','".addslashes($r['place_url'])."','".addslashes($r['place_desc'])."','".addslashes($r['place_addr'])."','".addslashes($r['place_postcode'])."')";
          $sql = $this->dbconn->prepare($smd);
          $sql->execute();
		}
	}

    //updateURLScraped 
	private function updateURLScraped($id) {
	    if($this->debug!=1){	
          $sql = $this->dbconn->prepare("UPDATE scrape_urls SET scrape_dt = '".date("Y-m-d H:i:s")."' WHERE url_id = ".$id);
          $sql->execute();
		}
	}
	
    //logScrape 
	private function logScrape($t,$e,$s="",$c="",$u="",$cnt="") {
        
		if($this->log == 1){
			
          ////Log Codes
          //1 = Scrape Start
          //2 = URLS to Scrape
          //3 = URLS Found
          //4 = Places Found
          //127 = Scrape End
		
		  $s = $this->db->Blank2Null($s);
		  $c = $this->db->Blank2Null($c);
		  $u = $this->db->Blank2Null($u);
		  $cnt = $this->db->Blank2Null($cnt);
		
		  $q = "INSERT INTO scrape_log (batch_id,log_type,log_error,site_id,script_blk_id,url_id,cnt) values (".$this->batch_id.",".$t.",'".$e."',".$s.",".$c.",".$u.",".$cnt.")";
          $sql = $this->dbconn->prepare($q);
          $sql->execute();
		
		}
	}
			
    //Print Results
	public function getDebug() {
	  return $this->debug_log;
	}


    //startScrape
	public function scrape($url_id="") {
		
	  $this->createScrapeBatch();
	  
	  $this->createScrapeURLs($url_id);

	  $this->getScrapeURLs();
      $this->getScrapeURLScripts();
      $this->getScrapeScripts();
	  
	  $this->startScrape();
	  
	}
	  
    //startScrape
	private function startScrape() {	
	  	
	  $urls = $this->urls;
	  $url_scripts = $this->url_scripts;
	  $scripts = $this->scripts;
  
      if(!empty($urls)){

        foreach ($urls as $url_id => $u){
    
	      $url = $u['url']; //Set scrape URL before evaluation
	      $site_id = $u['site_id'];
	      $script_id = $u['script_id'];
	
	      if(!empty($url_scripts[$url_id])) { //****//
		
	        foreach ($url_scripts[$url_id] as $script){ //**//
		  
              $script_blk_id = $script['script_blk_id'];
		   	
              //SCrape Tidy
              unset($s); 
              unset($results); 
			  
              $s = new scraper($this->debug);
	
              //Evaluate Code
              eval($scripts[$script_blk_id]['script_code']);
			  
		      if($this->debug==1){
 		        $this->debug_log = $s->getDebug();
		      }
			  
              unset($s);
    
              //Check we have results
              if(!empty($results)){
		
	            //Check if script produces another set of URLs to be scraped
                if($scripts[$script_blk_id]['next_script_id']!="") {
		  
	              //Log - no.of urls found
                  $this->logScrape(3,'0',$site_id,$script_blk_id,$url_id,count($results));
	  
		          //if so create the records
		          foreach ($results as $v) {
			        $this->createURL($site_id,$url_id,$v,$scripts[$script_blk_id]['next_script_id']);
		          }
                }
	
	            //Else it must be a Place!
	            else {
		  
		          //Log - Place
                  $this->logScrape(4,'0',$site_id,$script_blk_id,$url_id);
                  $this->createPlace($results,$site_id,$url_id);
	            }
              }
  
              //Something has probably gone wrong
              else {
	            if($u['next_script_id']!="") {
	              //Log - No urls found
                  $this->logScrape(3,'1',$site_id,$script_blk_id,$url_id);
	            }
	            else {
	              //Log - No Places Data Found
                  $this->logScrape(4,'1',$site_id,$script_blk_id,$url_id);
	            }
              }
  
	        }//**//

	      }//****//

          //Mark URL as Scrapped
          $this->updateURLScraped($url_id);

          //Sleep
          sleep(1);
        }
  
      }

      //Log - end scrape
      $this->logScrape(127,'0','','','','');
		
	}
	
}
?>