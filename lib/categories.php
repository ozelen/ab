<?

class Categories extends HotCat{
	function Categories(){
		$this->inc();
	}
	
	function Exec($name, $params=NULL){
		switch($name){
			case "show": $this->Show($this->uriVars['catid']); break;
			case "list": $this->ShowCatMenu($this->uriVars['objid']); break;
			case "showbyobject": $this->showByObject($params); break;
		}
	}
	
	function Show(){
		//foreach($this->uriVars as $key=>$value){print "<p>$key=>$value</p>";}
		$this->showByObject($this->uriVars['objid']);
		
	}
	
	function showByObject($objid){
		$db=$this->HDB;
		$q = "
			select 
				Objects.Id, 
				AccountCode, 
				Title, 
				Objects.Name".$this->pref." as Name,
				TitleImage
			from Objects 
			left join PageData 
				on PageData.PageId = Id 
				and lang = '".$this->CFG->lang."' 
			left join Modules
				on Modules.OwnerTable = 'Objects'
				and Modules.Name = 'album'
				and OwnerId = $objid
			where Objects.Id = ".$objid;
		
		$obj = mysql_fetch_object($db->q($q));
		$perid = $this->currentPeriod($objid);
		
		$xml='
			<categories objid="'.$objid.'" per="'.$perid.'">
				<object id="'.$obj->Id.'">
					<account><![CDATA['.$obj->AccountCode.']]></account>
					<name><![CDATA['.$obj->Name.']]></name>
					<image><![CDATA['.$obj->TitleImage.']]></image>
				</object>
				'.$this->catTypeList().'
				'.$this->showCatMenu("where ObjId = $objid").'
			</categories>
		';
		//print "<textarea>$xml</textarea>";
		return $xml;
	}
	
	function currentPeriod($objid){
		$today = date('Y-m-d');
		return $this->HDB->getFieldWhere("Periods", "Id", "where Begin <= '$today' and End >= '$today' and ObjId = $objid");
	}
	
	function catPrices($objid, $catid=NULL, $perid=NULL){
		$obj = new Objects();
		$q="
			select Id, Title, Source 
			from Categories left join PageData 
				on Categories.PageId = PageData.PageId 
				and Lang = '".$this->CFG->lang."'
			where ObjId = $objid
		";
		$cats = $this->HDB->q($q);
		while($c = mysql_fetch_array($cats)){
			$xml.='
				<category id="'.$c['Id'].'">
					<name><![CDATA['.$c['Title'].']]></name>
					<info><![CDATA['.$c['Source'].']]></info>
					'.$this->getClassLinks('Categories', $c['Id']).'
				</category>
			';
		}
		$xml = '
			<object id="'.$objid.'" viewmode="'.$obj->getObjRole($objid).'">
				<categories cat="'.$catid.'" per="'.$perid.'">
					'.$xml.'
					'.$this->Periods($objid).'
				</categories>
			</object>
		';
		return $xml;
	}
	
	function showCatMenu($where_conditions){
		$rsi = new rsi();
		$rsi->start();
		if($handler = $_GET['cat_handler'])$handler_condition=" and CatHandler = '$handler' ";
		$db=$this->HDB;
		$q="
			select 
				Categories.Id,
				Categories.PageId,
				cat_data.Title as Title,
				cat_data.Source as Source,
				type_data.data_varchar as TypeName,
				CatHandler as TypeId,
				Images.Id as ImageId,
				Modules.Id as AlbumId,
				COUNT(all_images.Id) as CountImages
			from Categories
			
			left join CatTypes on CatTypes.Id = Categories.CatHandler

			left join PageData as cat_data
				on cat_data.PageId = Categories.PageId
				and cat_data.Lang = '".$this->CFG->lang."'
				
			left join DataFields as type_data
				on type_data.ResId = CatTypes.ResId
				and type_data.Lang = '".$this->CFG->lang."'
				
			left join Modules 
				on  OwnerTable = 'Categories' 
				and OwnerId = Categories.Id
				and ModuleHandler = 'album'
			
			left join Images on Images.Id = Modules.TitleImage
			left join Images as all_images on all_images.AlbumId = Modules.Id
			
			$where_conditions
			$handler_condition
			group by Categories.Id
			order by Categories.Range
		";
		
		$cats = $db->q($q);
		//print "<!-- \n\n $q \n\n -->";
		//$rsi->msg("query ".$db->getField("Objects", "Name_ua", $objid));
		$rsi->start();
		$pages = new Pages();
		$pages->setDB($pages->CFG->HDB);	// now MySQL link oriented on "hotelbase" DB (HDB)
		$gal = new Gallery();
		
		while($c = mysql_fetch_array($cats)){
			//$sitemap = $Objects->siteMap($c['CatPage']);
			//$optlist = $Objects->optList(0, $c['Id']);
			$page_xml = $pages->getPage($c['PageId'], $this->CFG->lang, '', 2);
			//if($c['Id']){
			//	if($c['Id'] == $this->uriVars['catid'])
					$album = $gal->Show(NULL, "Categories", $c['Id'], $cursor=1, $step="all");
			//}
			$Categories.='
				<category id="'.$c['Id'].'">
					'.$this->getClassLinks('Categories', $c['Id']).'
					'.$page_xml.'
					<image>'.$c['ImageId'].'</image>
					<name><![CDATA['.$c['Title'].']]></name>
					<info><![CDATA['.$c['Source'].']]></info>
					<type id="'.$c['TypeId'].'">'.$c['TypeName'].'</type>
					
					<xalbum id="'.$c['AlbumId'].'" count="'.$c['CountImages'].'">
						<session id="'.session_id().'">'.session_name().'</session>
						<url-params>
							<album id="'.$c['AlbumId'].'">'.$album_name.'</album>
							<owner id="'.$c['Id'].'">Categories</owner>
						</url-params>
						
						'.$album.'
					</xalbum>
					
					'.$g_xml.'
					'.$sitemap.'
					'.$optlist.'
					'.$periods.'
					'.$datafields.'
				</category>
			';
		}

		return $Categories;
	}
	
	function catTypeList(){
		$db=$this->HDB;
		$q = "
			select CatTypes.Id, data_varchar as Title
			from CatTypes
			left join DataFields on (CatTypes.ResId = DataFields.ResId and Lang = '".$this->CFG->lang."')
		";
		$ct = $db->q($q);
		while($c = mysql_fetch_array($ct)){
			$xml.='<type id="'.$c['Id'].'"><![CDATA['.$c['Title'].']]></type>';
		}

		return "<cat-types>$xml</cat-types>";
	}

	function Periods($objid){
		$today = date("Y-m-d");
		
		$db=$this->HDB;
		$q="
			select 
				*
			from Periods
			where ObjId = $objid
			and End > '$today'
			order by Begin
		";
		$pers = $db->q($q);
		while($p = mysql_fetch_array($pers)){
			$catdays = array();
			$prices = $db->q("select * from Prices where PerId = ".$p['Id']." order by Day");
			while($pr = mysql_fetch_array($prices)){
				$prices_arr[$i++]=$pr;
				if($pr['Day']!==NULL){
					$day = $pr['Day'];
					if(!is_array($catdays[$pr['CatId']])){
						$catdays[$pr['CatId']] = array();
					}
					
					$catdays[$pr['CatId']][$day] = $pr['Price'];
					
				}
			}
			
			$week='';
			
			foreach($catdays as $cat => $days){
				$week_prices='';
				for($i=1; $i<=7; $i++){
					//if($days[$i]===NULL)$days[$i]=$days[0];
					if($days[$i]==$days[$i-1])continue;
					for($j=$i; $j<7 && $days[$j]==$days[$j+1]; $j++){}
					$pass = $j-$i;
					$span = $pass+1;
					//print "$i > $j (span $span) = ".$days[$i]."<br/>";
					$price = $days[$i] ? $days[$i] : $days[0];
					$week_prices.='
						<day id="'.$i.'" span="'.$span.'">'.$price.'</day>
					';
				}
				$week.='
					<cat id="'.$cat.'" min="'.min($days).'" max="'.max($days).'">
						'.$week_prices.'
					</cat>
				';
			}
			
			$prices_xml='';
			if(is_array($prices_arr))
			foreach($prices_arr as $pr){
				$prices_xml.='
					<price id="'.$pr['Id'].'" catid="'.$pr['CatId'].'" perid="'.$pr['PerId'].'" day="'.$pr['Day'].'" accomodation="'.$pr['Accomodation'].'">'.$pr['Price'].'</price>
				';
			}

			
			$xml.='
				<period id="'.$p['Id'].'">
					<begin>'.$this->prettyDate($p['Begin']).'</begin>
					<end>'.$this->prettyDate($p['End']).'</end>
					
					'.$this->xmlDate($p['Begin'], 'begin').'
					'.$this->xmlDate($p['End'], 'end').'
					
					<name>'.$p['Name'].'</name>
					<unit>'.$p['Unit'].'</unit>
					<prices>
						'.$prices_xml.'
					</prices>
					<week>
						'.$week.'
					</week>
				</period>
			';
		}
		$xml = '
			<periods>
				'.$xml.'
			</periods>
		';
		return $xml;
	}


	function addCat($objid, $name='', $lang='ua'){
		if(!$objid)return NULL;
		$db=$this->HDB;
		$db->q("insert into Categories set ObjId = $objid");
		$catid = $db->insId();
				
		$ObjPageId = $db->getField("Objects", "PageId", $objid);
		
		if(!($CatPageId = $db->getValWhere("Pages", "Id", "where Rozdil = $ObjPageId and Name = 'categories'"))){
			$db->q("insert into Pages (Name, Rozdil) values ('categories', $ObjPageId)", true);
			$CatPageId = $db->insId();
		}
		$db->q("insert into Pages (Rozdil, Name) values ($CatPageId, $catid)");
		$PageId = $db->insId();
		$db->q("update Categories set PageId = $PageId where Id = $catid");
		
		if($name && $lang){
			$db->q("insert into PageData set Title = '$name', PageId = $PageId, Lang = '$lang'");
		}
		
		$gallery = new gallery();
		$gallery->addAlbum('album', 'Categories', $catid);
		
		
		return $catid;
	}
	
	function delCat($catid){
		$db = $this->HDB;
		$pages = new Pages();
		$pages->setDB($db);
		$gallery = new gallery();
		$gallery->delByOwner("Categories", $catid);
		$PageId = $db->getField("Categories", "PageId", $catid);
		$pages->delNode($PageId);
		$db->q("delete from Categories where Id = $catid");
		$db->q("delete from Prices where CatId = $catid");
	}
	
	function addPer($objid, $begin, $end){
		if(!$objid || !$begin || !$end){
			print '{
				"result": "error", 
				"descr": "Incorrect Data"
			}';
			return NULL;
		}
		$db=$this->HDB;
		//print "add period [$begin - $end] for Object #$objid";
		$q = "
			select * from Periods where Begin <= '$begin' and End >= '$begin' and ObjId = $objid
			union
			select * from Periods where Begin <= '$end' and End >= '$end' and ObjId = $objid
		";
		$conflict = $db->q($q);
		while($c = mysql_fetch_object($conflict)){
			$err++;
			$res.="Conflict with [$c->Begin - $c->End] period \\n";
		}
		if(!$err){
			$db->q("insert into Periods set Begin = '$begin', End = '$end', ObjId = '$objid'");
			$id = $db->insId();
			print '{
				"name" : "'.$this->prettyDate($begin).' &ndash; '.$this->prettyDate($end).'",
				"result": "ok", 
				"id": '.$id.', 
				"descr": "Period has been added"
			}';
			
		}
		else print '{
			"result": "error", 
			"descr": "'.$res.'"
		}';
	}

	function delPeriod($id){
		if(!$id)return false;
		$db = $this->HDB;
		$db->q("delete from Prices where PerId = $id");
		$db->q("delete from Periods where Id = $id");
	}

}

?>