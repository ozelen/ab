<?



class Cities extends HotCat{
	function Cities(){
		$this->inc();
	}
	

	
	function Exec($name, $params=NULL){
		$db = $this->CFG->HDB;
		$identcity = $params ? $params : $this->uriVars['identcity'];
		$cityid = $db->getIDbyIdent("Settlements", $identcity );
		//print "[$cityid]";
		switch($name){
			case "show"	: return $this->Show($identcity); break;
			
			case "showlite"	: return $this->Show($identcity, false); break;
			case "menu"	: 
				$xml = $this->Menu( $cityid );
				return '<infrastructure>'.$xml.'</infrastructure>'; 
			break;
			
			case "list" :
				$xml = $this->getList();
				return '<regions>'.$xml.'</regions>'; 
			break;
			
			case "weather" : return $this->showWeather( $cityid ); break;
			/*
			case "infrastructure" : 
				//print "(".$this->uriVars['objtypeid']."))";
				$cityid = $db->getIDbyIdent("Cities", $identcity );
				$xml = $this->isShow($cityid, $this->uriVars['objtypeid']);
				return $xml;
			break;
			*/
			case "infra" : 
				$cityid = $db->getIDbyIdent("Settlements", $identcity );
				$obj = new Objects();
				$xml = $obj->fastList(array('class' => $this->uriVars['classvalue']));
				return $xml;
			break;
			
			case "regions" :
				$node = $params ? $params : $this->uriVars['regid'] ? $this->uriVars['regid'] : 0;
				return $this->lcRegionList($node);
			break;
			
			case "listbydomain":
				return $this->lcSettlementListByDomain(array('domain' => $this->CFG->Domain));
			break;
			
			case "form" : return $this->lcForm($params); break;
			
			case "addpage" : lcForm($tbl); return; break;
			
			case "region":
				return $this->lcSettlementListByRegion();
			break;
			
			case "typeredirect":
				$this->typeRedirect($this->uriVars['objtypeid']);
			break;
			
		}
	}
	
	function typeRedirect($oldtype){
		//print $this->CFG->URI."[$oldtype]";
		$class = $this->CFG->HDB->getField("ObjTypes", "ClassAnalog", $oldtype);
		if($class)$newuri = preg_replace("'/objects/(.*)'", "/infrastructure/$class/", $_SERVER['REQUEST_URI']);
		else $newuri = preg_replace("'/objects/(.*)'", "/", $_SERVER['REQUEST_URI']);
		$path = "http://".$_SERVER['HTTP_HOST'].$newuri;
		header("HTTP/1.1 302 Moved Temporary");
		header("Location: $path");
	}
	
	function lcSettlementListByRegion($p=NULL){
		$reg = array();
		$id = NULL;
		$reg[1] = $this->uriVars['country'];
		$reg[2] = $this->uriVars['region'];
		$reg[3] = $this->uriVars['district'];
		foreach($reg as $r){
			if($id = $this->CFG->HDB->getIDbyIdent("Regions", $r)) break;
		}
		if ($id){
			$domain = $this->CFG->MainDomain;
			$domain = "skiworld.org.ua"; // for testing on other domain
			$inside_xml = $this->lcSettlementListByDomain(array('domain' => $domain, 'region' => $id));
			//$inside_xml.= $this->lcRegionList($id, true);
			$xml = $this->lcShowRegion($id, $inside_xml);
			//print "<textarea>$xml</textarea>";
			return $xml;
		}
	}
	
	function lcNearestSettlements($lat, $lng, $r, $except=NULL){
		if(!$lat || !$lng || !$r) return NULL;
		$radius = $r*$r*0.000246176;
		$arr = array();
		$except_cond = $except ? "and st.id !=$except" : "";
		$lang = $this->CFG->lang;
		$q="
			SELECT 
				st.Id, 
				st.Name, 
				((lat - $lat)*(lat - $lat)+(lng - $lng)*(lng - $lng)) AS Distance 
			FROM Settlements st
			join Objects obj on obj.Settlement = st.id $except_cond
			group by st.Id
			HAVING distance < $radius
			ORDER BY distance
		";
		$st = $this->CFG->HDB->q($q);
		while($s = mysql_fetch_object($st)){
			$dist = round(sqrt($s->Distance/0.000246176), 1);
			$arr[$s->Id] = $dist;
			$res.="$s->Id: $s->Name ($dist km)\n";
		}
		//print "<textarea>$res</textarea>";
		
		return $arr;
	}
	
	
	function lcSettlementInfraList($id){
		if(!$id){print "Settlement undefined<br/>"; return false;}
		$lang = $this->CFG->lang;
		$profiles = array();
		$q = "
			select 
				ld.Title as TypeName,
				lnk.ClassValue as TypeId,
				prd.Title as Profile, 
				prf.ClassValue as ProfId,
				count(obj.Id) as CntObjs
			from ClassLinks lnk
			join hotelbase.Objects obj
				on lnk.OwnerTable = 'Objects' 
				and lnk.OwnerId = obj.Id 
				and obj.Settlement = $id
			join PageData ld 
				on ld.PageId = lnk.ClassValue 
				and ld.Lang = '$lang'
			left join ClassLinks prf 
				on prf.OwnerTable = 'Objects' 
				and prf.OwnerId = obj.Id 
				and prf.ProfileOf = 'Objects'
			join PageData prd 
				on prd.PageId = prf.ClassValue 
				and prd.Lang = '$lang'
			where lnk.TypeOf = 'Objects'
			group by lnk.ClassValue
			order by CntObjs desc
		";
		$inf = $this->CFG->DB->q($q);
		//print "<textarea>$q</textarea>";
		while($i = mysql_fetch_object($inf)){
			$xml.='<type id="'.$i->TypeId.'" profile="'.$i->ProfId.'" objcount="'.$i->CntObjs.'"><![CDATA['.$i->TypeName.']]></type>';
			$profiles[$i->ProfId] = '<profile id="'.$i->ProfId.'"><![CDATA['.$i->Profile.']]></profile>';
		}
		return '
			<!-- settlement infrastructure -->
			<inf>
				'.join('', $profiles).'
				'.$xml.'
			</inf>
		';
	}
	
	function lcSettlementListByDomain($p){
		if(!($domain = $p['domain'])){print "Domain id undefined"; return false;}
		$lang = $this->CFG->lang;
		$reg_arr = array();
		$dis_arr = array();
		if($regid = $p['region'])$where_cond.=" and (district_id = $regid or region_id = $regid) ";
		$q = "
			select
				st.Id,
				pd.Title as Name, 
				region_id as RegId, 
				district_id as DisId,
				rd.Title as Region,
				dd.Title as District,
				type_id as TypeId,
				st.Ident,
				lat, lng,
				COUNT(obj.Id) as ObjCount
			from Settlements st
			join ObjSiteLinks lnk
				on 
				PrjId = '$domain' and 
				(
					(OwnerTable = 'Settlements' and ObjId = st.id)
					or
					(OwnerTable = 'Regions' and ObjId = st.district_id)
					or
					(OwnerTable = 'Regions' and ObjId = st.region_id)
				)
			join Objects obj on obj.Settlement = st.id
			join PageData pd on pd.PageId = st.PageId and pd.Lang = '$lang'
			join Regions reg on st.`region_id` = reg.Id
			join PageData rd on rd.PageId = reg.PageId and rd.Lang = '$lang'
			left join Regions dis on st.`district_id` = dis.Id
			left join PageData dd on dd.PageId = dis.PageId and dd.Lang = '$lang'
			where 1=1
			$where_cond
			group by st.id
			order by ObjCount desc
		";
		$set = $this->CFG->HDB->q($q);
		//print "<textarea>$q</textarea>";
		while($s = mysql_fetch_object($set)){
			//print "$s->Name<br/>";
			$infra = $this->lcSettlementInfraList($s->Id);
			//$near = '<near distance="30" measure="km">'.$this->lcSettlementList(array('withobj'=>true, 'objid'=>$this->lcNearestSettlements($s->lat, $s->lng, 30))).'</near>';
			$xml.= '
				<settlement id="'.$s->Id.'" objcount="'.$s->ObjCount.'">
					<region id="'.$s->RegId.'"><![CDATA['.$s->Region.']]></region>
					<district id="'.$s->DisId.'"><![CDATA['.$s->District.']]></district>
					<type id="'.$s->TypeId.'"></type>
					<name><![CDATA['.$s->Name.']]></name>
					<ident><![CDATA['.$s->Ident.']]></ident>
					'.$infra.'
					'.$near.'
				</settlement>'
			;
			$reg_arr[$s->RegId] = '<region id="'.$s->RegId.'">'.$s->Region.'</region>';
			$dis_arr[$s->DisId] = '<district id="'.$s->DisId.'" region="'.$s->RegId.'">'.$s->District.'</district>';
		}
		
		$xml = '
			<settlements>
				<regions>'.join('', $reg_arr).'</regions>
				<districts>'.join('', $dis_arr).'</districts>
				'.$xml.'
			</settlements>
		';
		return $xml;
	}
	
	function lcForm($tbl){
		$pv = $this->CFG->PostVars;
		//print "[regid: ".$this->uriVars['regid'].", cityid: ".$this->uriVars['cityid']."]<br/>";
		if($this->CFG->valid->ident($ident = $pv['Ident'])){
			$edit = new EditPages('hotelbase');
			switch($tbl){
				case "Regions"		: $var = 'regid'; break;
				case "Settlements"	: $var = 'cityid'; break;
				default 			: $var = NULL; break;
			}
			if($owner_id = $this->uriVars[$var]){
				if($var)$edit->pushData($tbl, $owner_id, $pv);
				$this->CFG->HDB->q("update $tbl set Ident = '$ident' where Id = $owner_id");
			} else print "Invalid owner [$tbl | $var: $owner_id]!<br/>";
		}//else print "Invalid Ident!<br/>";
		switch($tbl){
			case "Regions"		: $xml = $this->lcShowRegion($this->uriVars['regid']); break;
			case "Settlements"	: $xml = $this->lcShowSettlement($this->uriVars['cityid']); break;
		}
		$xml = "<form>$xml</form>";
		return $xml;
	}
	
	function lcShowRegion($id, $inside=''){
		$db = $this->CFG->HDB;
		$reg = $db->q("select * from Regions where Id = $id");
		$pages = new Pages();
		$pages->setDB($db);
		if($r = mysql_fetch_object($reg)){
			$page = $r->PageId ? $pages->getPage($r->PageId, $this->CFG->lang, '', 3) : '<page id="-1"></page>';
			$xml=
				'<region id="'.$r->Id.'" type="'.$r->Type.'">
					<name><![CDATA['.$r->Name.']]></name>
					<ident><![CDATA['.$r->Ident.']]></ident>
					'.$page.'
					'.$this->SiteList('Regions', $r->Id).'
					'.$inside.'
				</region>'
			;
			return $xml;
		}
	}
	
	function lcShowSettlement($id){
		$db = $this->CFG->HDB;
		$cts = $db->q("select st.Id, st.Name, st.Ident, type_id as TypeId, PageId, lat, lng from Settlements st where Id = $id");
		$pages = new Pages();
		$pages->setDB($db);
		if($s = mysql_fetch_object($cts)){
			//print "$s->Name<br/>";
			$page = $s->PageId ? $pages->getPage($s->PageId, $this->CFG->lang, '', 3) : '<page id="-1"></page>';
			$xml= '
				<settlement id="'.$s->Id.'">
					<type id="'.$s->TypeId.'"></type>
					<name><![CDATA['.$s->Name.']]></name>
					<ident><![CDATA['.$s->Ident.']]></ident>
					<location>
						<lat>'.$s->lat.'</lat>
						<lng>'.$s->lng.'</lng>
					</location>
					'.$page.'
					'.$this->SiteList('Settlements', $s->Id).'
				</settlement>'
			;
			return $xml;
		}
		
		
	}

	function lcRegionList($node=0, $withobj = false){
		$db = $this->CFG->HDB;
		$regs = $db->q("select * from Regions where Node = $node order by name");
		$pages = new Pages();
		$pages->setDB($db);
		while($r = mysql_fetch_object($regs)){
			$page = $r->PageId ? $pages->getPage($r->PageId, $this->CFG->lang, '', 3) : '<page id="-1"></page>';
			$regions.=
				'<region id="'.$r->Id.'" type="'.$r->Type.'">
					<name><![CDATA['.$r->Name.']]></name>
					<ident><![CDATA['.$r->Ident.']]></ident>
					'.$page.'
					'.$this->SiteList('Regions', $r->Id).'
				</region>'
			;
		}
		
		if($node!==NULL)$settlements = $this->lcSettlementList(array('regid'=>$node, 'withobj'=>$widthobj));
		return '
			<regions node="'.$node.'">
				'.$regions.'
				'.$settlements.'
			</regions>
		';
	}
	
	
	function lcSettlementList($p){
		$db = $this->CFG->HDB;
		if(($regid = $p['regid'])!==NULL)$where_cond.=" and district_id = $regid or ( (region_id = $regid and district_id is NULL or district_id='') )";
		if($p['cityid'] && is_array($p['cityid']))$where_cond.=" and st.Id in (".join(', ', $p['cityid']).")";
		$objjointype = $p['withobj'] ? "" : "left";
		$q = "
			select 
				st.Id, st.Name, st.Ident, st.PageId,
				type_id as TypeId,
				lat, lng,
				count(obj.Id) as ObjCount
			from Settlements st
			$objjointype join Objects obj on obj.Settlement = st.Id
			where 1=1
			$where_cond
			group by st.Id
			order by st.Name
		";
		//print "<textarea>$q</textarea>";
		$regs = $db->q($q);
		$pages = new Pages();
		$pages->setDB($db);
		while($s = mysql_fetch_object($regs)){
			//print "$s->Name<br/>";
			$page = $s->PageId ? $pages->getPage($s->PageId, $this->CFG->lang, '', 3) : '<page id="-1"></page>';
			$xml.= '
				<settlement id="'.$s->Id.'" objcount="'.$s->ObjCount.'">
					<type id="'.$s->TypeId.'"></type>
					<name><![CDATA['.$s->Name.']]></name>
					<ident><![CDATA['.$s->Ident.']]></ident>
					'.$page.'
					'.$this->SiteList('Settlements', $s->Id).'
				</settlement>'
			;
		}
		
		return "<settlements>$xml</settlements>";
	}

	function SiteList($owner_tbl, $owner_id){
		$db = $this->CFG->DB;
		//$hdb = $this->CFG->HDB;
		$q = "
			select Domain, lnk.Status, ObjId
			from Sites
			left join hotelbase.ObjSiteLinks lnk on ObjId = $owner_id and OwnerTable = '$owner_tbl' and PrjId = Sites.Domain
			where Node = 'djerelo.info'
		";
		$sites = $db->q($q);
		while($s = mysql_fetch_object($sites)){
			$xml.='<site status="'.$s->Status.'">'.$s->Domain.'</site>';
		}
		$xml = '
			<sites>
				<owner id="'.$owner_id.'">'.$owner_tbl.'</owner>
				'.$xml.'
			</sites>
		';
		//print "<textarea>$xml</textarea>";
		return $xml;
	}


	function lcFind($str){
		if(strlen($str)<2)return false;
		$q = "
			select 
				s.id,
				s.name,
				d.Name as district,
				r.Name as region,
				t.name as type,
				lat, lng
				
			from hotelbase.Settlements s
			left join Regions d on d.Id = district_id
			left join Regions r on r.Id = region_id
			left join locations.types t on t.id = type_id
			
			where s.`name` like '$str%'
			group by s.`Id`
		";
		$loc = $this->CFG->HDB->q($q);
		
		while($l = mysql_fetch_object($loc)){
			$elt[$i++] = '{"id": "'.$l->id.'", "name":"'.$l->name.'", "region": "'.$l->region.'", "district": "'.$l->district.'",	"type": "'.$l->type.'",	"lat": "'.$l->lat.'", "lng": "'.$l->lng.'"}';
		}
		return '{"totalResultsCount":111, "settlements": ['.join(',', $elt).']}';
	}
	
	
	function isShow($city_id, $node=null){
		$obj = new Objects();
		if(!$node)$node=0;
		$db = $this->CFG->HDB;
		/*
		$q = "
			select ObjTypes.Id, Ident, TN.data_varchar as Title
				from ObjTypes
				left join DataFields as TN on TN.ResId = ObjTypes.ResId and TN.Lang = '".$this->CFG->lang."'
				left join Objects on Objects.TypeId = ObjTypes.Id and Objects.CityId = $city_id
			where HostId = $node
		";
		*/
		$qr = $db->q("select Id, Name_ua from ObjTypes where HostId = $node");
		while($t = mysql_fetch_object($qr)){
			$is.= $this->isShow($city_id, $t->Id);
		}
		
		$current = mysql_fetch_object($db->q("select Id, TN.data_varchar as Name from ObjTypes left join DataFields as TN on TN.ResId = ObjTypes.ResId and TN.Lang = '".$this->CFG->lang."' where Id = $node "));
		$xml = '
			<class id="'.$node.'" count="2">
				<name><![CDATA['.$current->Name.']]></name>
				'.$is.'
				'.$obj->objList($current->Id, NULL, NULL, $city_id).'
			</class>
		';
		return $xml;
	}
	
	
	function Show($id, $wide_info=true){
		if(is_numeric($id))$cond = " st.Id = $id";
		else
		if(is_string($id))$cond = " st.Ident = '$id'";
		//print "[$cond]";
		return $this->getByCond($cond, $wide_info);
	}
	
	function getList(){
		return $this->getByCond('');
	}
	
	function getByCond($cond, $wide_info=false){
		if(is_array($cond))
			foreach($cond as $c){
				$conditions.=" and $c ";
			}
		else if($cond && is_string($cond)) $conditions = " and $cond ";
		
		//print "<h1>[$conditions]</h1>";
		$lang = $this->CFG->lang;
		$db = $this->CFG->HDB;
		$q = "
			select 
				st.id as Id,
				st.region_id as RegionId,
				st.ident as Ident,
				CityData.Title as Name,
				CityData.Source as Info,
				Topic,
				st.PageId,
				
				region_id as RegId, 
				district_id as DisId,
				rd.Title as Region,
				dd.Title as District,
				reg.Ident as RegIdent,
				dis.Ident as DisIdent,
				
				lat, lng
			from Settlements st
			join PageData as CityData
				on st.PageId = CityData.PageId
				and Lang = '$lang'
			
			join Regions reg on st.`region_id` = reg.Id
			join PageData rd on rd.PageId = reg.PageId and rd.Lang = '$lang'
			left join Regions dis on st.`district_id` = dis.Id
			left join PageData dd on dd.PageId = dis.PageId and dd.Lang = '$lang'
			
			where 1=1
			$conditions
			group by st.Id
		";
		return $this->getByQuery($q, $wide_info);
	}
	
	function getByQuery($q, $wide_info=false){
		$db = $this->CFG->HDB;
		$cts = $db->q($q);
		//$forum = new Forum();
		$map = new Map();
		$pages = new Pages();
		$pages->setDB($pages->CFG->HDB);	// now MySQL link oriented on "hotelbase" DB (HDB)
		while($city = mysql_fetch_object($cts)){
			if($wide_info){
				/*
				$istruct = '
					<infrastructure>
						'.$this->Menu($city->Id).'
					</infrastructure>
				';
				*/
				//$info = '<info><![CDATA['.$city->Info.']]></info>';
				//$comments = $forum->showByUri($city->Topic);
				$comments = '<topic id="'.$city->Topic.'"/>';
				//$location = $map->Coords('Cities', $city->Id);
				//$city->MsgCount = $this->CFG->DB->getFieldWhere("Msg", "count(*)", "where HostNode = ".$city->Topic);
				$infra = $this->lcSettlementInfraList($city->Id);
				
				$near_arr = $this->lcNearestSettlements($city->lat, $city->lng, 15, $city->Id);
				foreach($near_arr as $id=>$distance){
					$dist_xml.='<d for="'.$id.'" measure="standart.measure.distance.km">'.$distance.'</d>';
				}
				$near = 
					'<near radius="15" measure="standart.measure.distance.km">'
						.'<distances>'.$dist_xml.'</distances>'
						.$this->lcSettlementList(array('withobj'=>true, 'cityid'=>array_keys($near_arr))).
					'</near>'
				;
			}
			
			$page_xml = $pages->getPage($city->PageId, $this->CFG->lang, '', 3);
			
			$xml.='
				<city id="'.$city->Id.'" lat="'.$city->lat.'" lng="'.$city->lng.'">
					<name><![CDATA['.$city->Name.']]></name>
					<ident>'.$city->Ident.'</ident>
					'.$page_xml.'
					'.$info.'
					'.$istruct.'
					<location lat="'.$city->lat.'" lng="'.$city->lng.'"/>
					<image>
						<path><![CDATA['.$city->ImgPath.']]></path>
						<name><![CDATA['.$city->ImgName.']]></name>
						<ext>'.$city->ImgExt.'</ext>
					</image>
					<region id="'.$city->RegId.'" ident="'.$city->RegIdent.'">'.$city->Region.'</region>
					<district id="'.$city->DisId.'" ident="'.$city->DisIdent.'">'.$city->District.'</district>
					
					'.$infra.'
					'.$near.'
					'.$comments.'

				</city>
			';
		}
		if(!$xml) return "";
		$xml = "<cities>$xml</cities>";
		//print "<textarea>$xml</textarea>";
		return $xml;
	}

	function Menu($id, $node=NULL){
		
		$db = $this->CFG->HDB;
		$pref = $this->pref;
		$nodes = array();
		if($node)$node_cond=" and ObjTypes.Id = $node";
		$q = "
			SELECT 
			  ObjTypes.Id,
			  ObjTypes.HostId as Node,
			  TN.data_varchar AS Name,
			  count(*) as ObjNum,
			  ObjTypes.Ident
			FROM
			  Objects
			  left join ObjTypes ON (ObjTypes.Id = Objects.TypeId)
			  left join DataFields as TN on TN.ResId = ObjTypes.ResId and TN.Lang = '".$this->CFG->lang."'
			WHERE
			  CityId = ".$id."
			  $node_cond
			GROUP BY
			  ObjTypes.Id
		";
		// Firstly we find second level of ObjTypes tree and remember it in array $nodes
		$obj = $db->q($q);
		while($o = mysql_fetch_array($obj)){
			if($o['Name'])$nodes[$o['Node']] .= '
				<type id="'.$o['Id'].'" node="'.$o['Node'].'" city="'.$o['CityId'].'">
					<name><![CDATA['.$o['Name'].']]></name>
					<ident><![CDATA['.$o['Ident'].']]></ident>
					<objs>'.$o['ObjNum'].'</objs>
				</type>
			';
			
		}
		// secondly we join keys in string with comma like join(", ", $array)
		$vals = $db->InsRepVals($nodes);
		$node_keys = $vals['InsKeys'];
		$q = "
			select Id, Ident, HostId as Node, TN.data_varchar as Name 
			from ObjTypes 
			left join DataFields as TN on TN.ResId = ObjTypes.ResId and TN.Lang = '".$this->CFG->lang."'
			where Id in ($node_keys)
		";
		if(!$nodes)return false;
		$hdr = $db->q($q);
		while($node = mysql_fetch_object($hdr)){
			$xml.='
				<class id="'.$node->Id.'" node="'.$node->Node.'" count="1">
					<name><![CDATA['.$node->Name.']]></name>
					<ident><![CDATA['.$node->Ident.']]></ident>
					<types>
						'.$nodes[$node->Id].'
					</types>
				</class>
			';
		}
		//print "<textarea>$xml</textarea>";
		
		return $xml;
	}
	
	function showWeather($id){
		global $BlockContent, $lang, $pref, $rootDirectory;
		$file_path = $this->CFG->RootDir.'/data/weather/weather_'.$id.'.xml';
		//print "[$file_path]";
		if(file_exists($file_path)){
			//print " exists!";
			
			$xml = file_get_contents($file_path);
			$xml = preg_replace("'<\?[\/\!]*?[^<>]*?\?>'si", "", $xml);
			return $xml;
		}
	}
	
}

?>