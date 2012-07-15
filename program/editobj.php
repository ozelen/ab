<?php
	include_once("../config.php");
	
	//session_name('djerelo_info');
	session_start();
	if($_SESSION['email']){
		$caCFG->UserData->getData($_SESSION['email'], $_SESSION['pass']);
	}
	
	
	//foreach($_POST as $k=>$v){print "$k = $v\n";}
	//print "[".$_GET['mode']."]";

class editObj extends MNG{
	var $cfg;
	var $db;
	var $today;
	var $id;
	var $cats;
	var $obj;
	var $temp;
	function editObj(){
		$this->inc();
		$this->cfg 	= $this->CFG;
		$this->db 	= $this->CFG->DB;
		$this->hdb 	= $this->CFG->HDB;
		$this->get 	= $this->CFG->GetVars;
		$this->pv	= $this->CFG->PostVars;
		$this->id	= $this->pv['objid'];
		$this->cats = new editCategories();
		//$this->pages = new Pages();
		$this->temp = $this->pv['temp'];// ? $this->get['template'] : $this->pv['temp'];
		$this->mode = $this->get['mode'];
		$this->lang	= $this->get['lang'] ? $this->get['lang'] : $this->pv['lang'];

		$this->obj = new Objects($this->id);
	}
	function Go(){
		/*
		switch($this->mode){
			case "refreshpricetable": break;
			default: if(!$this->checkAccess())return false;
		}
		*/
		switch($this->mode){
			case "updall":
				$this->hdb->q("update Objects set Updated = unix_timestamp(now())");
			break;
			case "add":
				if(!$this->objAccess('Objects', $this->pv['objid'], 'admin')) {print "[access denied] You can't to add a new object!"; return false;} 
				$this->obj->Add(); 
			break;
			case "objrefresh": print $this->objRefresh($this->id); break;
			case "catlist": print $this->catList($this->id); break;
			case "catsort": 
				if(!$this->objAccess('Objects', $this->id, 'admin, subadmin, user')){print "[access denied] You can't sort these categories!"; return false;}
				$this->sortCats(); 
			break;
			case "catadd": 
				if(!$this->objAccess('Objects', $this->id, 'admin')){print "[access denied] You can't add a new category!"; return false;}
				$this->addCat(); $this->obj->upd($this->id); 
			break;
			case "delcat" : 
				//print "delete: [".$this->get['catid']."]\n";
				if(!$this->objAccess('Categories', $this->get['catid'], 'admin')) {print "[access denied] You can't delete these categories!"; return false;}
				$this->cats->Del($this->get['catid']); 
				$this->obj->upd();
			break;
			case "saveclass": 
				// access check inside the function
				$this->saveClass();
			break;
			
			case "saveprices":
				// access check inside the function
				$this->savePrices();
			break;
			
			case "refreshpricetable":
				// public function
				$pages = new Pages();
				if($this->id && $this->temp){
					
					$xml = $this->CFG->xmlHeader.
					'<out mode="'.$this->pv['mode'].'">'.
						$this->cats->catPrices($this->id, $this->pv['catid'], $this->pv['perid']).
						//$this->addXml.
						'<sitemap>'.
							$pages->siteMap($this->cfg->SiteHomePage).
						'</sitemap>'.
					'</out>'
					;
					//print '<textarea cols="100" rows="30">'.$xml.'</textarea>';
					print $this->xmlOut->qShow($xml, $this->temp);
					
				}
			break;
			
			case "addper":
				if(!$this->objAccess('Objects', $this->pv['objid'], 'admin, subadmin, user')) {print "[access denied] You can't to add a period!"; return false;}
				$this->cats->addPer($this->pv['objid'], $this->pv['from'], $this->pv['to']);
				$this->obj->upd($this->pv['objid']);
			break;
			
			case "delper":
				if(!$this->objAccess('Objects', $this->pv['objid'], 'admin, subadmin, user')) {print "[access denied] You can't to remove a period!"; return false;}
				$this->cats->delPeriod($this->pv['perid']);
				$this->obj->upd();
				//print "delete period #".$this->pv['perid']." of Object #".$this->pv['objid']."\n";
			break;
			
			case "agreements_list" :
				$agreements = new Agreements();
				print $this->xmlOut->qShow($agreements->List(), $this->temp);
			break;
			
			case "objtable":
				$this->cfg->rsi->start();
				$pages = new Pages();
				$xml = 
					'
					<out>
						<sitemap>'.$pages->siteMap(1).'</sitemap>
						'.$this->obj->objTable($this->pv).'
					</out>'
				;
				print $this->xmlOut->qShow($xml, $this->temp);
				$this->cfg->rsi->showLog();
			break;
			
			case "classtypes":
				$this->integClassTypes();
			break;
			
			case "editsitelink":
				if(!$this->objAccess('Objects', $this->pv['objid'], 'admin')) {print "[access denied] You can't modify site collections!"; return false;}
				$pv = $this->pv;
				if( ($site_id = $pv['site_id']) && ($owner_tbl = $pv['owner_tbl']) && ($owner_id = $pv['owner_id']) ){
					$cond = "from ObjSiteLinks where PrjId = '$site_id' and ObjId = $owner_id and OwnerTable = '$owner_tbl'";
					if(mysql_fetch_object($this->hdb->q("select * $cond"))) 
						$q = "delete $cond";
					else 
						$q = "insert into ObjSiteLinks set PrjId = '$site_id', ObjId = $owner_id, OwnerTable = '$owner_tbl', Status = 1";
					$this->hdb->q($q,1);
				}else{
					print "Incorrect data";
					return false;
				}
			break;
			
		}
	}
	
	function integClassTypes(){
		$db = $this->cfg->DB;
		$q = "
			select cl.Id, 
			k.Name as `key`,
			v.Name as `val`,
			k.Params as kparm,
			v.Params as vpar
			from ClassLinks cl
			join Pages k on cl.ClassKey = k.Id
			join Pages v on cl.ClassValue = v.Id
		";
		$cls = $db->q($q);
		
	
		while($c = mysql_fetch_object($cls)){
			parse_str($c->vpar, $par_arr);
			$res.= "<tr><td>$c->key</td><td>$c->val</td><td>".$par_arr['profile']."</td><td>".$par_arr['nameof']."</td></tr>";
			$db->q("update ClassLinks set TypeOf = '".$par_arr['typeof']."', ProfileOf = '".$par_arr['profile']."', NameOf = '".$par_arr['nameof']."' where Id = $c->Id");
		}
		print "<table border=1>$res</table>";
	}
	
	function objRefresh($id){
		$objid = $this->pv['objid'];
		$xml = 
			'<out>'.
				$this->addXml.
				$this->obj->cache__Show($objid).
			'</out>';
		//print "<textarea cols=80 rows=30>$xml</textarea>";
		return $this->xmlOut->qShow($xml, $this->temp);
	}
	
	function savePrices(){
		$prices = array();
		$i=0;
		foreach($this->pv as $k => $v){
			if(preg_match("/price--(.*)/", $k, $regs)){
				$prices[$i] = array( 
					'params' => array(), 
					'price' => $v
				);
				$params = split("-",$regs[1]);
				foreach($params as $param){
					list($key, $value) = split("_", $param);
					$prices[$i]['params'][$key] = $value;
				}
				$i++;
			}
		}
		
		$per = $prices[0]['params']['per'];
		$cat = $prices[0]['params']['cat'];
		$ObjId = $this->cfg->HDB->getField('Categories', 'ObjId', $cat);

		foreach($prices as $p){
			// check on access for Period and Categories
			if(
				!$this->objAccess('Periods', $p['params']['per'], 'admin, subadmin, user') || 
				!$this->objAccess('Categories', $p['params']['cat'], 'admin, subadmin, user')
			){
				print "You cannot modify this Price Value";
				continue;
			}
			$this->pushPrice($p);
			
		}
		$this->obj->upd($ObjId);
	}
	
	function pushPrice($p){
		$price	= $p['price'];
		$pm 	= $p['params'];
		$id		= $pm['id'];
		$cat	= $pm['cat'];
		$per	= $pm['per'];
		$day	= $pm['day'];
		$acc	= $pm['acc'];
		if($price && !$this->cfg->valid->price($price)){print "[error] Incorrect price [$price] input!\n"; return false;}
		// print "\n push [$price, $pm, $id, $cat, $per, $day, $acc]\n";
		if(!$price && $id) $q = "delete from Prices where Id = $id";
		if($p['price']){
			if($id) 
				$q = "update Prices set Price = $price where Id = $id";
			else
				$q = "insert into Prices set CatId = $cat, PerId = $per, Day = $day, Accomodation = '$acc', Price = $price";
		}
		if($q) $this->hdb->q($q);
	}
	
	function saveClass(){
		$db = $this->cfg->DB;
		$sql_vals = array();
		
		$owner_tbl = $this->pv['OwnerTable'];
		
		if($this->pv['OwnerId']=='new'){
			if(!$this->objAccess('Objects', $this->pv['objid'], 'admin')){print "[access denied] You can't add this!"; return false;}
			switch($owner_tbl){
				case "Categories": 
					$cat = new Categories();
					$owner_id = $cat->addCat($this->pv['objid'], $this->pv['name'], $this->lang);
				break;
				case "Objects": 
					print "ADD OBJECT (cityid:".$this->pv['cityid'].", $typeid: ".$this->pv['typeid']." name:".$this->pv['name'].", lang: $this->lang)\n";
				break;
				default: print "?!"; break;
			}
		}
		else {
			$owner_id  = $this->pv['OwnerId'];
			if($owner_tbl == 'Objects' && $this->pv['settlement']){
				$this->hdb->q("update Objects set AccountCode = '".$this->pv['AccountCode']."', Email = '".$this->pv['Email']."', Settlement = ".$this->pv['settlement']." where Id = $owner_id",1);
			}
		}

		if(!$this->objAccess($owner_tbl, $owner_id, 'admin')){print "[access denied] You can't change this classification!"; return false;}
		
		print "[$owner_id]";
		if(!$owner_id || !is_numeric($owner_id)){
			print "Owner undefined";
			return false;
		}

		foreach($_POST as $parse_key => $value){
			if(preg_match("/key_(.*)/", $parse_key, $regs)){
				$key = $regs[1];
				//print "[$key:$value]\n";
				if(is_array($value)){
					foreach($value as $v){
						$par = $this->getPageParams($v);
						$sql_vals[$i++] = "('$owner_tbl', $owner_id, $key, $v, '".$par['typeof']."', '".$par['profile']."', '".$par['nameof']."')";
					}
				}
				else {
					//print "([$key]:[$value])";
					if(!$value)continue;
					$par = $this->getPageParams($value);
					$sql_vals[$i++] = "('$owner_tbl', $owner_id, $key, '$value', '".$par['typeof']."', '".$par['profile']."', '".$par['nameof']."')";
				}
			}
		}
		$this->delClassLinks($owner_tbl, $owner_id);
		$q="insert into ClassLinks (OwnerTable, OwnerId, ClassKey, ClassValue, TypeOf, ProfileOf, NameOf) values ".join(", ", $sql_vals);
		$this->obj->upd($this->pv['objid']); 
		$db->q($q);
		//print "$q";
	}
	
	function getPageParams($id){
		$db = $this->cfg->DB;
		$p = mysql_fetch_object($db->q("select Params from Pages where Id = $id"));
		parse_str($p->Params, $par_arr);
		return $par_arr;
	}

	
	function catList($objid){
		$db = $this->cfg->HDB;
		$cat_groups = array();
		$q="
			select Id, Title, Source, Categories.PageId, CatHandler
			from Categories
			left join PageData 
				on Categories.PageId = PageData.PageId
				and Lang = '$this->lang'
			where ObjId = $objid
			order by Categories.Range
		";
		$list = $db->q($q);
		$pages = new Pages();
		$sitemap = $pages->siteMap($this->cfg->SiteHomePage);
		//print "<textarea>$sitemap<textarea>";
		$pages->setDB($pages->CFG->HDB);	// now MySQL link oriented on "hotelbase" DB (HDB)
		while($c = mysql_fetch_object($list)){
			$cat_groups[$c->CatHandler].='
				<category id="'.$c->Id.'" range="'.$c->Range.'">
					'.$this->getClassLinks('Categories', $c->Id).'
					'.$pages->getPage($c->PageId, $this->lang, '', 2).'
				</category>
			';
		}
		foreach($cat_groups as $group=>$content){
			$xml.='
				<catgroup id="'.$group.'">
					<name>'.$group.'</name>
					'.$content.'
				</catgroup>
			';
		}
		$xml='
			<categories objid="'.$objid.'" mode="edit" lang="'.$this->lang.'">
				<sitemap>
					'.$sitemap.'
				</sitemap>
				'.$xml.'
			</categories>
		';
		//print "<textarea>$xml</textarea>";
		return $this->xmlOut->qShow($xml, $this->temp);
	}
	
	function sortCats(){
		$db = $this->cfg->DB;
		$hdb = $this->cfg->HDB;
		foreach($this->pv as $key=>$value){
			//print "$key:$value\n";
			if(preg_match("/handler_(.*)/", $key, $regs)){
				$catid = $regs[1];
				if(preg_match("/(.*)_(.*)/", $value, $kv)){
					$classkey 	= $kv[1];
					$classval 	= $kv[2];
					
					
					if($c = mysql_fetch_object($db->q("select ClassValue from ClassLinks where OwnerTable = 'Categories' and OwnerId = $catid and ClassKey = $classkey"))){
						if($c->ClassValue != $classval) $db->q("update ClassLinks set ClassValue = $classval where OwnerTable = 'Categories' and OwnerId = $catid and ClassKey = $classkey");
					}else $db->q("insert into ClassLinks set OwnerTable = 'Categories', OwnerId = $catid, ClassKey = $classkey, ClassValue = $classval");
					
				}
			}
				//$db->q("update Categories set CatHandler = '$value' where Id = ".$regs[1]);
			if(preg_match("/range_(.*)/", $key, $regs)) {
				//$db->q("update Categories set Range = $value where Id = ".$regs[1]);
			}
		}
	}
	function addCat(){
		
	}
	function checkAccess(){
		// ****************************************************************************//
		// *** Now we checking access for user
		// 1 step : We check on super-user
		if($this->cfg->UserData->Level == 1) return true;
		else return false;
	}
	
	function objAccess($table, $id, $roles=NULL){
		return $this->obj->objAccess($table, $id, $roles);
	}
}



$edit = new editObj();
$edit->Go();

?>