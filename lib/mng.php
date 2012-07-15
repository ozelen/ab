<?

class MNG{
	function MNG(){
		//$this->inc();
	}
	function inc(){
		global $caCFG;
		global $xmlOut;
		global $UriVars;	// Variables that defining in URI string in format /some_page_1/[int:var_name_here]/some_page_2
		global $RSI;
		$this->CFG = &$caCFG;
		$this->xmlOut = $xmlOut;
		$this->addXml = $this->CFG->addXml;
		$this->DB = $this->CFG->DB;
		$this->HDB = $this->CFG->HDB;
		//$this->CFG->lang = ($lang = $_GET['lang']) ? $lang : 'ua';
		$this->pref = ($this->CFG->lang =='en' || $this->CFG->lang =='' ? '' : '_'.$this->CFG->lang);
		$this->PostData = $this->postDecode($_POST);
		$this->uriVars = $UriVars;
		$this->rsi = &$this->CFG->rsi;
		$this->Mail = $this->CFG->Mail;
		//$this->SRC	= new Resources();
	}

	function arr2obj($array = array()){
		if(empty($array) || !is_array($array))return false;
		$data = null;
		foreach($array as $key=>$val){
			$data -> {$key} = $val;
		}
		return $data;
	}

	function getClassName($id){
		if(!$id) return false;
		$t = mysql_fetch_object($this->CFG->DB->q("select Title from Pages pg join PageData pd on pg.Id = pd.PageId and Lang = '".$this->CFG->lang."' where pg.Id = $id"));
		return $t->Title;
	}
	
	function prettyDate($str){
		if(!$str)return '';
		list($date, $time) = split(" ", $str);
		list($y,$m,$d) = split("-",$date);
		$res = $d.'.'.$m.'.'.$y;
		if($time)$res.=' '.$time;
		return $res;
	}
	function xmlDate($str, $id=''){
		list($date, $time) = split(" ", $str);
		list($y,$m,$d) = split("-",$date);
		$res = $d.'.'.$m.'.'.$y;
		if($time)$res.=' '.$time;
		return '
			<date id="'.$id.'" pretty="'.$res.'" original="'.$str.'">
				<y>'.$y.'</y>
				<m>'.$m.'</m>
				<d>'.$d.'</d>
				<time>'.$time.'</time>
			</date>
		';
	}
	
	function leave($date_str){
		$date = strtotime($date_str);  
		  
		$sec=$date - time();  
		$days=floor(($date - time()) /86400);  
		$h1=floor(($date - time()) /3600);  
		$m1=floor(($date - time()) /60);  
		$hour=floor($sec/60/60 - $days*24);  
		$hours=floor($sec/60/60);  
		$min=floor($sec/60 - $hours*60);  
		  
		switch(substr($days, -1)){  
			case 1: $o='остался';  break;  
			case 2: case 3: case 4: case 5: case 6: case 7: case 8: case 9: case 0: $o='осталось';  break;
		}  
		  
		switch(substr($days, -2)){  
			case 1: $d='день';  
			break;  
			case 2: case 3: case 4: $d='дня';  
			break;  
			default: $d='дней';  
		}  
		  
		switch(substr($hour, -2)) {  
			case 1: $h='час';  
			break;  
			case 2: case 3: case 4: $h='часа';  
			break;  
			default: $h='часов';  
		}  
		  
		switch(substr($min, -2)) {  
			case 1: $m='минута';  
			break;  
			case 2: case 3: case 4: $m='минуты';  
			break; 
			default:$m='минут'; 
		}  
		if ($days>0) $res.= $days.'&nbsp;'.$d;
		if ($h1>0) $res.= $hour.'&nbsp;'.$h;
		if ($m1>0) $res.= '&nbsp;и&nbsp;'.$min.'&nbsp;'.$m;
		
		return $res;
	}
	
	
	function postDecode($postvars){
		//print '['.count($postvars).']';
		if(!count($postvars))return false;
		else{
			$res = array();
			foreach($postvars as $key => $value){
				$res[$key]=addslashes($value);
				
				
				//print "$key => $res[$key]<br>";
			}
		}
		return $res;
	}
	function addPresets(){
		foreach($this->uriVars as $key => $value){
			//print "[$key=>$value]";
			if($key)$uv_xml.="<$key>$value</$key>";
		}
		//$x = $this->seoTitle();
		
		$xml='
			<presets>
				<lang>'.$this->CFG->lang.'</lang>
				<real-uri>'.$_SERVER['REQUEST_URI'].'</real-uri>
				<virtual-uri>'.$this->CFG->VirtualUri.'</virtual-uri>
				<uri>'.$this->CFG->URI.'</uri>
				<uri-vars>
					'.$uv_xml.'
				</uri-vars>
				<seo-title>
					<![CDATA['.$x.']]>
				</seo-title>
			</presets>
		';
		//print "<textarea>$xml</textarea>";
		return $xml;
	}
	
	function seoTitle(){
		
		$sub = $_GET['sub'];
		$temp="$lang/$sub";
		$q = $this->CFG->HDB->q("select Source from seo where Sub like '%$temp%'");
		while($r=mysql_fetch_array($q)){
			$res .= ' '.$r['Source'].' ';
		}
		//print "[$res]";
		return $res;
		
	}
	
	function nowDate(){
		return date("Y-m-d H:i:s");
	}
	
	function classHotelPages(){
		$pages = new Pages();
		$pages->DB = $this->CFG->HDB;
		return $pages;
	}
	
	
	// *** Caching
	
	function getCache($dir, $fname, $updated=NULL){
		$fpath = $this->CFG->CacheDir.$dir."/".$fname;
		//dbg($fpath);
		if(file_exists($fpath)){
			$stat = stat($fpath);
			if($updated<$stat[9]) return file_get_contents($fpath);
		}
		return NULL;
	}
	
	function newCache($dir, $fname, $data){
		$fpath = $this->CFG->CacheDir.$dir."/".$fname;
		if(!is_dir($dir)){
			$this->newDir($this->CFG->CacheDir, $dir);
			if(!is_dir($this->CFG->CacheDir.$dir)){
				return NULL;
			}
		}
		$fp = fopen($fpath, "w");
		fwrite($fp, $data);
	}
	function delCache($dir, $fname){
		$fpath = $this->CFG->CacheDir.$dir."/".$fname;
		if(!file_exists($fpath)){
			unlink($fpath);
		}
	}
	
	function newDir($basepath, $localpath){
		$arr = explode("/", $localpath);
		if(!is_dir($basepath)) print "<p>Error! Incorrect file address [$basepath]</p>";
		else{
			$current_dir = $basepath.'/'.array_shift($arr);
			if(!is_dir($current_dir))mkdir($current_dir);
			if(($lp = join('/', $arr))!=='')$this->newDir($current_dir, $lp);
		}
	}
	
	function readDir($dir, $id, $templ=NULL){
		if(!($handle = opendir($dir))){
			print "<p>Can't open dir [$dir]</p>";
			return false;
		}
		while(false!==($file = readdir($handle))){
			$db = $this->DB;
			//print "<p>$dir/$file</p>";
			if ($file != "." && $file != ".."){
				$file=trim($file);
				if(is_dir($dir."/".$file)){
					$xml.='
						<dir name="'.$file.'">
							'.$this->readDir($dir."/".$file, $id).'
						</dir>
					';
				}
				else{
					//$path = preg_replace("|".$this->CFG->TempDir."|", "", $dir);
					//$path=$url;
					//$xml.='<file local="'.$path.'" ext="xml">'.$file.'</file>';
					
					$xml.='
						<image name="'.$name.'">
							<title></title>
							<comments topic="">
								
							</comments>
						</image>
					';
					
				}
			}
			
			
			if(is_numeric($edit)){
				$pageid = $db->getField("Categories", "PageId", $edit);
				$titles = $db->q("select Lang, Title from PageData where PageId = $pageid");
				while($t = mysql_fetch_array($titles)){
					$multiname.='
						<lang id="'.$t['Lang'].'">'.$t['Title'].'</lang>
					';
				}
				$fields = $db->getLine("FullPlaces, AddPlaces, CountOf, Measure", "Categories", "Id = $edit");
			}
			
			$xml='
				<images>
					'.$xml.'
				</images>
				<edit>
					<name>
						'.$multiname.'
					</name>
				</edit>
			';
		}

		//if(!$xml)$xml="<empty>empty</empty>";
		return (
			$templ 
				? $this->xmlOut->qShow($this->CFG->xmlHeader.$xml, $templ, true) 
				: $xml);
	}
	
	function getClassLinks($owner_tbl, $owner_id){
		$db = $this->CFG->DB;
		$q = "
			select 
				ClassLinks.Id, 
				ProfileOf, NameOf, TypeOf,
				KeyData.Title as ClassKeyName, 
				ValData.Title as ClassValName, 
				ValData.Source as ClassValSource,
				ClassKey as ClassKeyId, 
				ClassValue as ClassValId, 
				Value,
				KeyPage.Params as KeyParams,
				ValPage.Params as ValParams
			from ClassLinks
			left join PageData as KeyData
				on KeyData.PageId = ClassKey
				and KeyData.Lang = '".$this->CFG->lang."'
			left join PageData as ValData
				on ValData.PageId = ClassValue
				and ValData.Lang = '".$this->CFG->lang."'
			
			left join Pages as KeyPage 
				on KeyPage.Id = ClassKey
			left join Pages as ValPage 
				on KeyPage.Id = ClassValue
				
			where 
				OwnerTable = '$owner_tbl'
				and OwnerId = $owner_id
		";
		$this->CFG->rsi->start();
		$data = $db->q($q);
		$this->CFG->rsi->stop();
		//$this->CFG->rsi->msg("classlinks");
		$dir = $this->CFG->guiImgDir."icons/classes/";
		
		while($c = mysql_fetch_object($data)){
			parse_str($c->KeyParams, $k_par);
			$kimg = file_exists($dir.$c->ClassKeyId.".png") ? ' img="true" ' : '';
			$vimg = file_exists($dir.$c->ClassValId.".png") ? ' img="true" ' : '';
			
			//print "[".$dir.$c->ClassKeyId.".png]";
			
			$xml.='
				<class id="'.$c->Id.'" typeof="'.$c->TypeOf.'" profile="'.$c->ProfileOf.'" nameof="'.$c->NameOf.'" ownerid="'.$owner_id.'">
					<key id="'.$c->ClassKeyId.'" '.$kimg.'><![CDATA['.$c->ClassKeyName.']]></key>
					<val id="'.$c->ClassValId.'" '.$vimg.'><![CDATA['.$c->ClassValName.']]></val>
					<value>'.$c->Value.'</value>
				</class>
			';
		}
		$xml='
			<classify>
				<owner id="'.$owner_id.'">'.$owner_tbl.'</owner>
				'.$xml.'
			</classify>
		';
		return $xml;
	}
	
	function delClassLinks($owner_tbl, $owner_id){
		$db = $this->CFG->DB;
		$db->q("delete from ClassLinks where OwnerTable = '$owner_tbl' and OwnerId = $owner_id ");
	}
	
	// Getting parameters from hash in XML format
	function getXmlParams($str){
		parse_str($str, $arr);
		foreach($arr as $key=>$value){
			//print "$key=>$value";
			$params_xml.="<$key>$value</$key>";
		}
		$params_xml="<params>$params_xml</params>";
		return $params_xml;
	}
	
	function cout($id, $vals_arr=NULL, $lang=NULL){
		if($lang)$loc = $this->CFG->getLocale($lang);
		else $loc = $this->CFG->addXml;
		if(is_array($vals_arr)){
			$vals_xml = $this->getXmlParamsAttr($vals_arr);
		}
		$xml = '
			<cout id="'.$id.'">
				'.$loc.'
				'.$vals_xml.'
			</cout>
		';
		return $this->xmlOut->qShow($xml, 'standart/vals.xslt');
		//print "<textarea>$xml</textarea>";
	}
	
	// Another getting XML from hash but here $key outed like an element attrubute
	function getXmlParamsAttr($values){
		if(!is_array($values))return false;
		foreach($values as $key => $value){
			if(is_numeric($value)){}
			else $value = '<![CDATA['.$value.']]>';
			$xml.='<val id="'.$key.'">'.$value.'</val>';
		}
		return '<values>'.$xml.'</values>';
	}
	



}

?>