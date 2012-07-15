<?


class Objects extends HotCat{
	var $Id;
	function Objects($id=NULL){
		$this->Id = $id;
		$this->debug = false;
		$this->inc();
	}
	
	function Exec($name, $params=NULL){
		parse_str($params, $p_arr);
		//foreach($p_arr as $k=>$v)print "<p>[$k]=>[$v]</p>";
		//print "<h1>".$p_arr['type']."</h1>";
		switch($name){
			case "show": return $this->Show($this->uriVars['objid']); break;
			case "cond_list": return $this->mdObjList($params);  break;
			case "list": return $this->objList($p_arr['type'], $p_arr['limit'], $p_arr['order'], $p_arr['city']); break;
			case "userobjects": return $this->userObjects(); break;
			case "fastlist": return $this->fastList($p_arr); break;
			case "objtable": return $this->objTable($p_arr); break;
			case "postdata": return $this->objTable(array('objid'=>$this->Add())); break;
		}
	}
	
	function Show($id){
		if(!is_numeric($id))$id = $this->CFG->HDB->getFieldWhere("Objects", "Id", "where AccountCode = '$id'");
		//print "[$id]";
		$role = $this->getObjRole($id);
		if($this->leftTime($id)>=0 || $role=='admin')
			return $this->fullShow($id);
		else 
			return $this->cutShow($id);
	}
	
	
	function leftTime($id){
		if(!$id or !is_numeric($id))return false;
		$db = $this->CFG->HDB;
		$lastdoc = mysql_fetch_object($db->q("select Expire from Agreements where ObjId = $id order by Expire desc limit 1"));
		$exp_time = strtotime($lastdoc->Expire);
		$now = time();
		return $exp_time-$now;
	}
	
	function cutShow($id){
		//print "Cut show";
		return $this->miniInfo(array('objid'=>$id));
	}
	
	function fullShow($id){
		//print "Full show";
		//$this->CFG->Visitor->incObjStat('Objects', $id);
		if(!is_numeric($id))$id = $this->CFG->HDB->getFieldWhere("Objects", "Id", "where AccountCode = '$id'");
		$updated = $this->CFG->HDB->getField("Objects", "Updated", $id);
		$dir = "objects/$id";
		$fname = $id."_".$this->CFG->lang.".xml";
		$role = $this->getObjRole($id);
		if($role=='admin' || $role=='subadmin' || $role=='user'){
			return $this->cache__Show($id);	// we do not caching an object for admin
		}
		if(!($xml = $this->getCache($dir, $fname, $updated))){
			$xml = $this->cache__Show($id);	// caching
			$this->newCache($dir, $fname, $xml);
		}
		return $xml;
	}
	
	function delCache($id){
		$this->upd($id);
		$dir = "objects/$id/";
		$fname = $id."_*.xml";
		$pattern = $this->CFG->CacheDir.$dir.$fname;
		print "[$pattern]";
		foreach(glob($pattern) as $fpath){
			if(is_file($fpath)){
				unlink($fpath);
				print "<p>Deleted [$fpath]</p>";
			}
		}
	}
	
	function upd($id=NULL){
		//print "Update [$id] on ".time();
		$ID = $id ? $id : $this->Id;
		if(!$ID)return false;
		if($ID)$this->HDB->q("update Objects set Updated = ".time()." where Id = $ID");
	}
	

	
	function cache__Show($id){
		if(is_numeric($id))$cond = "obj.Id = $id";
		else{
			$id = addslashes($id);
			$cond = "AccountCode = '$id'";
		}
		$this->rsi->start();
		$pref = $this->pref;
		if(!$id)return false;
		$q="
			select
				obj.Id as Id,
				obj.Settlement as CityId,
				DateRegistered,
				DeadLineRegist,
				AccountCode,
				Phones,
				Mob,
				Email,
				obj.Topic as Topic,
				obj.PageId,
				objdata.Title as Name,
				$wide_info
				
                profile.ClassValue as ProfId,
                profname.Title as ProfName,
                ctype.ClassValue as TypeId,
                ctypename.Title as TypeName,

				stldata.Title as CityName,
				stl.Name as CityeNameEmrgency,
				stl.Ident as CityIdent
				
			--	Images.Id as ImgName,
			--	Images.Extension as ImgExt
				
			from Objects obj
				
			--	left join Modules 
			--		on  OwnerTable = 'Objects' 
			--		and OwnerId = Objects.Id
			--		and ModuleHandler = 'album'
			--	left join Images on Images.Id = Modules.TitleImage
				
				left join PageData objdata on objdata.PageId = obj.PageId and objdata.Lang = '".$this->CFG->lang."'
				left join Settlements stl on stl.Id = obj.Settlement
				left join PageData stldata on stl.PageId = stldata.PageId and stldata.Lang = '".$this->CFG->lang."'

                left join skiworld.ClassLinks profile on profile.OwnerId = obj.Id and profile.OwnerTable = 'Objects' and profile.ProfileOf = 'Objects'
                left join skiworld.PageData profname on profname.PageId = profile.ClassValue and profname.Lang = '".$this->CFG->lang."'
                left join skiworld.ClassLinks ctype on ctype.OwnerId = obj.Id and profile.OwnerTable = 'Objects' and ctype.TypeOf = 'Objects'
                left join skiworld.PageData ctypename on ctypename.PageId = ctype.ClassValue and ctypename.Lang = '".$this->CFG->lang."'


			where $cond		
			group by obj.Id		
		";
		//print "<textarea>$q</textarea>";
		$xml = $this->getObjsQuery($q, true);
		//$this->rsi->msg("show object");
		//print "<textarea>$xml</textarea>";
		return $xml;
	}
	
	
	function mdObjList($params){
		parse_str($params, $p_arr);
		$type 	= $p_arr['type'];
		$lim 	= $p_arr['lim'];
		$order	= $p_arr['order'];
		$city	= $p_arr['city'];
		
		$xml = $this->objList($type, $lim, $order, $city);
		//print "<textarea>$xml</textarea>";
		return $xml;
		
	}
	
	function getObjRole($id){
		return $this->CFG->UserData->Id ?
			(
				($role = $this->CFG->User->checkRole('objects', $id, 'hotelbase'))
				? $role
				: 'authorized-guest'
			)
			: 'guest';
		;
	}
	
	function getObjsQuery($q, $width_info=false){
		$rsi = new rsi();
		$rsi->start();
		$db = $this->HDB;
		$cat = new Categories();
		$mod = new ObjModules();
		$map = new Map();
		$obj = $db->q($q);
		$pages = new Pages();
		$pages->setDB($pages->CFG->HDB);	// now MySQL link oriented on "hotelbase" DB (HDB)
		while($o = mysql_fetch_array($obj)){
			$modules = new Modules();
			if($width_info){
				$modules_xml = $mod->getModules("Objects", $o['Id']);
				$catmenu_xml = $cat->showByObject($o['Id']);
				$location = $map->Coords('Objects', $o['Id']);
				$forum = new Forum();

				$page_xml = $pages->getPage($o['PageId'], $this->CFG->lang, '', 3);
				//print "<textarea cols=100 rows=30>$pages_xml</textarea>";
				//$ViewModeAttr = $this->isAdmin('hotelbase', 'Objects', $o['Id']);
				
				//print "<h1>[".$o['Topic']."]</h1>";
				if($o['Topic']){
					//$comments = $forum->showByUri($o['Topic']);
				}else{
					$o['Topic'] = $forum->addTopic('Objects', $o['Id']);
					$q = "update Objects set Topic = ".$o['Topic']." where Id = ".$o['Id'];
					//print "[$q]";
					$db->q($q);
				}
				
				// Check on user access level for this object
				
				
				
				$ViewModeAttr = $this->getObjRole($o['Id']);

					//print "<h1>[$ViewModeAttr]</h1>";
			}
			
			
			
			
			
			$xml.='
				<object id="'.$o['Id'].'" page="'.$o['PageId'].'" viewmode="'.$ViewModeAttr.'" format="full">
					'.$this->getClassLinks('Objects', $o['Id']).'
					<type id="'.$o['TypeId'].'"><![CDATA['.$o['TypeName'].']]></type>
					<profile id="'.$o['ProfId'].'"><![CDATA['.$o['ProfName'].']]></profile>
					<city id="'.$o['CityId'].'" ident="'.$o['CityIdent'].'"><![CDATA['.$o['CityName'].']]></city>
					<registered>'.$o['DateRegistered'].'</registered>
					<deadline num="'.join(explode("-",$o['DeadLineRegist'])).'">'.$o['DeadLineRegist'].'</deadline>
					<account><![CDATA['.$o['AccountCode'].']]></account>
					<name><![CDATA['.$o['Name'].']]></name>
					<info><![CDATA['.$o['Info'].']]></info>
					<currency>uah</currency>
					<email><![CDATA['.$o['Email'].']]></email>
					<image>
						<name><![CDATA['.$o['ImgName'].']]></name>
						<ext>'.$o['ImgExt'].'</ext>
					</image>
					<topic id="'.$o['Topic'].'"/>
					'.$page_xml.'
					'.$catmenu_xml.'
					'.$modules_xml.'
					'.$comments.'
				</object>
			';
		}
		
		$cattypes = $cat->catTypeList();
		
		$xml = "
			<objects>
				<additional>
					$cattypes
				</additional>
				$xml
			</objects>
		";

		//print "<textarea cols=150 rows=30>$xml</textarea>";

		//$rsi->msg("objlist");
		
		return $xml;
	}
	

	function objTable($params){
		$res = $this->fastList($params);
		return $res;
	}


	function miniInfo($p){
		$db = $this->CFG->HDB;
		
		if(is_array($p))
		foreach($p as $key => $value){
			switch($key){
				case 'objid': $cond_str.=" and obj.Id = $value "; break;
				case 'keyword': 
					if($value)
					switch($p['searchin']){
						case "objname"		: $cond_str.=" and concat_ws(' ', obj.AccountCode, objdata.Title) like '%$value%' "; break;
						case "settlement"	: $cond_str.=" and concat_ws(' ', setdata.Title, sett.Name) like '%$value%' "; break;
					}
				break;
				case 'orderby'	 : $order_cond." order by ".$value; break;
				case 'ordertype' : $order_cond ? $order_cond.=" ".$value : false; break;
				case 'limit'	 : $limit = " limit ".$value; break;
			}
		}
		else if(is_numeric($p))$cond_str=" and obj.Id = $value ";

		$city_cond = $cityid ? "and obj.CityId = $cityid" : '';
		
		$q = "
			select 
				obj.Id as ObjId, 
				obj.PageId, Settlement,
				objdata.Title as Name,
				st.Name as CityDef, st.Id as CityId, st.Ident as CityIdent,
                stdata.Title as CityName,
                cdata.Source as Contacts,
				idata.Source as Info,
                gal.TitleImage as ImgName,

                profile.ClassValue as ProfId,
                profname.Title as ProfName,
                ctype.ClassValue as TypeId,
                ctypename.Title as TypeName,
				obj.Topic
				
                from Objects obj
                join PageData objdata on objdata.PageId = obj.PageId and objdata.Lang = '".$this->CFG->lang."'

				left join Pages cont on cont.Rozdil = obj.PageId and cont.Name = 'contacts'
                left join PageData cdata on cont.Id = cdata.PageId and cdata.Lang = '".$this->CFG->lang."'
				
				left join Pages info on info.Rozdil = obj.PageId and info.Name = 'info'
				left join PageData idata on info.Id = idata.PageId and idata.Lang = '".$this->CFG->lang."'
				
				left join Settlements st on st.id = obj.Settlement
                left join PageData as stdata on st.PageId = stdata.PageId and stdata.Lang = '".$this->CFG->lang."'
                left join Modules gal on OwnerId = obj.Id and OwnerTable = 'Objects' and ModuleHandler = 'album'
				
                left join skiworld.ClassLinks profile on profile.OwnerId = obj.Id and profile.OwnerTable = 'Objects' and profile.ProfileOf = 'Objects'
                left join skiworld.PageData profname on profname.PageId = profile.ClassValue and profname.Lang = '".$this->CFG->lang."'
                left join skiworld.ClassLinks ctype on ctype.OwnerId = obj.Id and profile.OwnerTable = 'Objects' and ctype.TypeOf = 'Objects'
                left join skiworld.PageData ctypename on ctypename.PageId = ctype.ClassValue and ctypename.Lang = '".$this->CFG->lang."'
			where 1=1
			$cond_str
            group by obj.Id
		";
		$map = new Map();
		$objs = $db->q($q);
		while($o = mysql_fetch_array($objs)){
			$xml.='
				<object id="'.$o['ObjId'].'" page="'.$o['PageId'].'" format="cut">
					'.$this->getClassLinks('Objects', $o['ObjId']).'
					<type id="'.$o['TypeId'].'"><![CDATA['.$o['TypeName'].']]></type>
					<profile id="'.$o['ProfId'].'"><![CDATA['.$o['ProfName'].']]></profile>
					<city id="'.$o['Settlement'].'" ident="'.$o['CityIdent'].'"><![CDATA['.$o['CityName'].']]></city>
					<registered>'.$o['DateRegistered'].'</registered>
					<name><![CDATA['.$o['Name'].']]></name>
					<contacts><![CDATA['.$o['Contacts'].']]></contacts>
					<info><![CDATA['.$o['Info'].']]></info>
					<image>
						<name><![CDATA['.$o['ImgName'].']]></name>
						<ext>'.$o['ImgExt'].'</ext>
					</image>
					<topic id="'.$o['Topic'].'"/>
					'.$map->Coords('Objects', $o['ObjId']).'
				</object>
			';
		}
		return "<objects>".$xml."</objects>";
	}

	function fastList($p){
		
		$rsi = new rsi();
		$rsi->start();
		$db = $this->HDB;
		$lang = $this->CFG->lang;
		
		// City identificating by name
		$cityid = $p['city'] ? $db->getIDbyIdent("Settlements", $p['city']) : 
			$cityid = $p['cityid'] ? $p['cityid'] : NULL;
		
		
		if(!$cityid) $cityid = $this->uriVars['identcity'] ? $db->getIDbyIdent("Settlements", $this->uriVars['identcity']) : NULL;	// by urivars
		
		//foreach($p as $key => $value){print "$key => $value<br/>";}
		
		if(is_array($p))
		foreach($p as $key => $value){
			switch($key){
				case 'keyword': 
					if($value)
					switch($p['searchin']){
						case "objname"		: $cond_str.=" and concat_ws(' ', obj.AccountCode, objdata.Title) like '%$value%' "; break;
						case "settlement"	: $cond_str.=" and concat_ws(' ', setdata.Title, sett.Name) like '%$value%' "; break;
					}
				break;
				case 'notexpired': $cond_str.=" and Expire >= now() "; break;
				case 'orderby'	 : $order_cond." order by ".$value; break;
				case 'ordertype' : $order_cond ? $order_cond.=" ".$value : false; break;
				case 'groupby'	 : if($value == 'ObjId')$group_cond = "group by obj.Id"; break;
				case 'limit'	 : $limit = " limit ".$value; break;

			}
		}

		$city_cond = $cityid ? "and obj.Settlement = $cityid" : '';
		

		if($p['class']){
			$join.= "join skiworld.ClassLinks ccls on ccls.OwnerId = obj.Id and ccls.OwnerTable = 'Objects' and ccls.ClassValue = ".$p['class'];
			$tclass = '<class id="'.$p['class'].'"><![CDATA['.$this->CFG->DB->getFieldWhere("PageData", "Title", "where PageId = ".$p['class']." and Lang = '$lang'").']]></class>';
			$fields_cond.="ccls.ClassKey as tclass_key, ccls.ClassValue as tclass_val, ";
		}
		
		$q="
			select 
				$fields_cond
				Expire, FIO, AgrStat,
				
				obj.Id as Id,
				objdata.Title as Name,
				pics.TitleImage as pic,
				prc.Price as MinPrice,
				prc.CatId,
				prc.CatName,
				per.Begin, per.End,
				sett.Id as setid,
				sett.ident as setident,
				setdata.Title as setname,
				obj.DateRegistered,
				obj.AccountCode as login,
				lc.lat, lc.lng,
				cls.ClassValue as TypeId
				
			from 
				Objects obj
				left join PageData objdata on obj.PageId = objdata.PageId and objdata.Lang = '$lang'
				left join Modules pics on OwnerTable = 'Objects' and OwnerId = obj.Id and ModuleHandler = 'album'
				left join Periods per on per.ObjId = obj.Id and Begin <= now() and End >= now()
				left join 
					(
						select Prices.Price, CatId, PerId, catdata.Title as CatName
						from Prices 
						left join Categories cat on Prices.CatId = cat.Id
						left join PageData catdata on catdata.PageId = cat.PageId and catdata.Lang = '$lang'
						order by Prices.Price
					) as prc on prc.PerId = per.Id

				left join Settlements sett on sett.Id = obj.Settlement
				left join PageData setdata on setdata.PageId = sett.PageId and setdata.Lang = '$lang'
				left join Locations lc on lc.OwnerTable = 'Objects' and lc.OwnerId = obj.Id
				left join skiworld.ClassLinks cls on cls.OwnerId = obj.Id and cls.OwnerTable = 'Objects' and cls.ProfileOf = 'Objects'


				
				left join 
					(
						select Id,  ObjId, Expire, AgrStat, concat_ws(' ', LastName, FirstName, Patronymic) as FIO
                        from Agreements
                        group by Id
						order by Agreements.Expire desc
					) as agr on agr.ObjId = obj.Id
				
				$join
			where 1=1
			$city_cond
			$cond_str
			group by obj.Id
			order by Expire desc
		";
		//print "<textarea>$q</textarea>";
		
		
		
		$objs = $db->q($q);
		while($obj = mysql_fetch_object($objs)){
			$price = $obj->MinPrice ? '
				<price from="'.$obj->Begin.'" to="'.$obj->End.'">'.$obj->MinPrice.'</price>
				<pricecat id="'.$obj->CatId.'">'.$obj->CatName.'</pricecat>
			' : '';
			
			$exp_time = strtotime($obj->Expire);
			$now = time();
			$status = $exp_time < $now
				? 'expired'
				: '';
			
			
			$leave = $exp_time - $now;
			$leave_days = floor($leave/86400);
			
			
			
			$xml.='
				<obj id="'.$obj->Id.'">
					<k>'.$obj->tclass_key.'</k>
					<name><![CDATA['.$obj->Name.']]></name>
					<login>'.$obj->login.'</login>
					<image>
						<name>'.$obj->pic.'</name>
						<ext>jpg</ext>
					</image>
					<settlement id="'.$obj->setid.'" ident="'.$obj->setident.'">'.$obj->setname.'</settlement>
					<registered>'.$this->prettyDate($obj->DateRegistered).'</registered>
					
					<status expire="'.$this->prettyDate($obj->Expire).'">'.$status.'</status>
					<leave time="'.$leave.'">'.$leave_days.'</leave>
					
					<docs>
						<agreements count="'.$obj->AgrCount.'"/>
						<bills count="'.$obj->BillsCount.'"/>
					</docs>
					<currency>uah</currency>
					'.$price.'
					'.$this->getClassLinks('Objects', $obj->Id).'
				</obj>
			';

		}
		
		$xml = '
			<fastobjlist>
				'.$tclass.'
				'.$xml.'
			</fastobjlist>
		';
		//print "<textarea cols=80 rows=30>$xml</textarea>";
		return $xml;
		$rsi->stop();
	}
	
	function objList($p_type, $p_lim, $p_order, $p_city){
		
		$rsi = new rsi();
		$rsi->start();
		//print "<h3>[$p_type] [$p_lim] [$p_order] [$p_city]</h3>";
		$db = $this->HDB;
		$pref = $this->pref;
		$type = (
			is_numeric($p_type) 
				? $p_type
				: $db->getAllChildsIn("ObjTypes", $type_id = $db->getIDbyIdent("ObjTypes", $p_type))." ".$type_id
			);
		$city = (is_numeric($p_city) 
			? $p_city
			: $db->getIDbyIdent("Settlements", $p_city) 
		);
		
		//print "<h1>[$city]</h1>";
		//$rsi->msg("Variables");

		//$ord = $p_order ? ""
		
		$type_condition = $type 	? " and Objects.TypeId in ( $type )" : NULL;
		$city_condition = $city 	? " and Objects.CityId = $city" : NULL;
		$lim_condition 	= $p_lim 	? " limit $p_lim" : NULL;
		$order_by 		= $p_order	? " order by $p_order desc" : "order by DeadLineRegist desc";
		
		$where = "
			where 
				AccountCode is not null
			$type_condition
			$city_condition
		";
		
		$cond="
			$where
			group by Objects.Id
			$order_by
			$lim_condition	
		";
		$q = $this->queryObjList($cond);
		
		$rsi->stop();
		return $this->getObjsQuery($q);
	}
	
	function userObjects(){
		$joins="
			join AccessPoints 
				on AccessArea = 'objects' 
				and Params = Objects.Id 
				and UserId = ".$this->CFG->UserData->Id."
		";
		$cond = "
			group by Objects.Id
		";
		
		$q = $this->queryObjList($cond, '', $joins);
		//print "<textarea cols=100 rows=30>$q</textarea>";
		
		
		return $this->getObjsQuery($q);
		
	}
	
	function queryObjList($conditions, $selectors='', $joins=''){
		$pref = $this->pref;
		if($selectors && !preg_match("/\,$/x", $selectors))$selectors.=',';
		$q="
			select
				Objects.Id as Id,
				TypeId,
				Objects.Settlement as CityId,
				DateRegistered,
				DeadLineRegist,
				AccountCode,
				Phones,
				Mob,
				Email,
				Objects.PageId,
				-- Objects.Name".$pref." as Name,
				-- Objects.Address".$pref." as Address,
				ObjData.Title as Name,
				ObjTypes.Name".$pref." as TypeName,
				
				$wide_info
				Objects.Phones as Phones,
				CityData.Title as CityName,
				Settlements.Ident as CityIdent,
				Images.Id as ImgName,
				Images.Extension as ImgExt,
				Objects.DeadLineRegist,
				".$selectors."
				0
			from Objects
				left join ObjTypes on ObjTypes.Id = Objects.TypeId
				left join Settlements on Settlements.Id = Objects.Settlement
				".$joins."
			left join Modules 
				on  OwnerTable = 'Objects' 
				and OwnerId = Objects.Id
				and ModuleHandler = 'album'
			left join PageData as CityData on CityData.PageId = Settlements.PageId and CityData.Lang = '".$this->CFG->lang."'
			left join PageData as ObjData on ObjData.PageId = Objects.PageId and ObjData.Lang = '".$this->CFG->lang."'
			
			left join Images on Images.Id = Modules.TitleImage		
		".$conditions;
		
		
		return $q;
	}
	
	
	
	function Add(){
		if(!$this->objAccess('Objects', $this->pv['objid'], 'admin')) {print "[access denied] You can't to add a new object!"; return false;} 
		$gal = new gallery();
		$p = $this->CFG->PostVars;
		if(!$p['Name']){print "Name undefined"; return false;}
		$ident = $p['Ident'];
		$email = $p['Email'];
		$db = $this->CFG->HDB;
		$db->q("insert into Objects (AccountCode, Email, DateRegistered) values ('$ident', '$email', now()) ");
		$objid = $db->insId();
		$page_id = $this->newPage($objid, 2, $p['Name']);
		$info_id = $this->newPage('info', $page_id);
		$cont_id = $this->newPage('contacts', $page_id);
		$cate_id = $this->newPage('categories', $page_id);
		$this->setPage('Objects', $objid, $page_id);
		$gal->addAlbum('album', 'Objects', $objid);
		return $objid;
	}
	
	function newPage($name, $rozdil, $title=''){
		$this->CFG->HDB->q("insert into Pages (Name, Rozdil) values ('$name', $rozdil)",0);
		$id = $this->CFG->HDB->InsId();
		if($title)$this->addData($id, $title, '', $this->CFG->lang);
		return $id;
	}
	
	function setPage($table, $id, $page){
		$this->CFG->HDB->q("update $table set PageId = $page where Id = $id");
	}
	
	function addData($id, $title, $source, $lang){
		$lang=='en' ? $pref = '' : $pref = '_'.$lang;
		$today = date("Y-m-d H:i");
		
		$arr = array(
			'PageId'	=> $id,
			'Title' 	=> "'$title'",
			'Source' 	=> "'$source'",
			'Lang'		=> "'$lang'"
		);
		$this->CFG->HDB->q("insert into PageData  (".join(', ', array_keys($arr)).") values (".join(', ', $arr).")");
	}
	
	
	function postData($pv, $templ){
		$db = $this->CFG->HDB;
		$today = date("Y-m-d H:i");
		$tomor = date("Y-m-d H:i", mktime (0,0,0,date("m"),date("d"),date("Y")+1));
		//$p_today = $this->prettyDate($today);
		//$p_tomor = $this->prettyDate($tomor);
		
		$vars = array(
			'AccountCode'			=> $pv['AccountCode'],
			'Settlement'			=> $pv['Settlement'],
			'Email'					=> $pv['Email']
		);
		
		if($pv['Id']){
			if($v = $pv['DateRegistered'])$vars['DateRegistered'] = $v;
			if($v = $pv['DeadLineRegist'])$vars['DeadLineRegist'] = $v;
		}
		else{
			$vars['DateRegistered'] = $today;
			$vars['DeadLineRegist']	= $tomor;
		}
		
		//foreach($pv as $k => $v){print "<p>$k = $v</p>";}
		
		$dta = $db->InsRepVals($vars);
		
		if($pv['Id']){
			$db->q("update Objects set ".$dta['RepVars']." where Id = ".$pv['Id']); 
			$objid = $pv['Id'];
		}
		else{
			$integ = new integr();
			$db->q("insert into Objects  (".$dta['InsKeys'].") values (".$dta['InsVals'].")"); 
			$objid = $db->InsId();
			
			$integ->intObj(mysql_fetch_array($db->q("select * from Objects where Id = $objid")));
		}
		
		//print "<p>$q</p>";
		
		
		return $objid;
	}
	
	function delObject($objid){ // Delete an object
		$db = $this->HDB;
		$categories = new Categories;
		$modules = new Modules();
		// ********************************* //
		// Delete Categories
		$sql = $db->q("select Id from Categories where ObjId = $objid");
		while($i = mysql_fetch_array($sql)){
			print '<p>Delete Category ['.$i["Id"].']</p>';
			//$categories->Del($i["Id"]);
		}
		// Delete Periods
		$sql = $db->q("select Id from Periods where ObjId = $objid");
		while($i = mysql_fetch_array($sql)){
			print '<p>Delete Period ['.$i["Id"].']</p>';
			//$categories->delPeriod($i["Id"]);
		}
		// Delete Modules
		$sql = $db->q("select Id from Modules where OwnerTable = 'Objects' and OwnerId = $objid");
		while($i = mysql_fetch_array($sql)){
			print '<p>Delete Module ['.$i["Id"].']</p>';
			//$modules->delModule($i["Id"]);
		}
		// Delete Service Options
		$sql = $db->q("select Id from ServiceLinks where ObjId = $objid");
		while($i = mysql_fetch_array($sql)){
			print '<p>Delete Option ['.$i["Id"].']</p>';
			//$this->delOptionServices($i["Id"]);
		}
		// Delete Pages
		$pageid = $db->getField("Objects", "PageId", $objid);
		print '<p>Delete Category ['.$i["Id"].']</p>';
		//$pages->delNode($pageid);
		// Delete object
		print '<p>Delete Object ['.$i["Id"].']</p>';
		//$db->q("delete from Objects where Id = $objid");
	}

	function objAccess($table, $id, $roles=NULL){
		$db = $this->CFG->DB;
		$hdb = $this->CFG->HDB;
		switch($table){
			case 'Categories': 
			case 'Periods': 
				$objid = $hdb->getField($table, 'ObjId', $id); 
			break;
			case 'Objects': $objid = $id; break;
		}
		
		if($objrole = $this->getObjRole($objid)){
			if($roles){
				if(is_array($roles))$roles_arr = $roles;
				else $roles_arr = split("[ ,]", $roles);
				if(array_search($objrole, $roles_arr)!==false)return true;
				else return false;
			}
			else return true;
		}
		else return false;
	}
	
}

?>