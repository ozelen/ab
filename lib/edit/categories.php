<?
class editCategories extends Categories{
	function editCategories(){
		$this->inc();
	}
	
	function Del($catid){
		$db = $this->CFG->HDB;
		$this->delClassLinks('Categories', $catid);
		$pages = new Pages();
		$pages->CFG->DB = $db;
		$gallery = new gallery();
		$gallery->delByOwner("Categories", $catid);
		$PageId = $db->getField("Categories", "PageId", $catid);
		$pages->delNode($PageId);
		$db->q("delete from Categories where Id = $catid");
		$db->q("delete from Prices where CatId = $catid");
		
		//$db->getField("ModLinks", "Album");
		//$this->Album->Del();
	}
	
	function postData($getvars, $postvars){
		$catid 			= $getvars['catid']; 
		$objid 			= $getvars['objid']; 
		$measure 		= 'uah/'.$postvars['room-pay']; 
		$fullplaces 	= $postvars['full-places']; 
		$addplaces		= $postvars['add-places'];
		$count			= $postvars['count'];
		
		$prices = array();
		$values = array(
			'name' 	=> array(),
			'source'=> array()
		);
		foreach($postvars as $key=>$value){
			//print "<p>[$key]=>[$value]</p>";
			if(preg_match("|multiform--catname--(.*)|", $key, $regs)){
				$values['name'][$regs[1]] = $value;
			}
			
			if(preg_match("|priceperiod--(.*)|", $key, $regs)){
				$prices[$regs[1]] = $value;
			}
		}

		$this->Add($objid, $catid, $measure, $fullplaces, $addplaces, $count, $values, $prices);
	}
	
	function Add($objid, $catid, $measure, $fullplaces, $addplaces, $count, $data, $prices=NULL){
		$db=$this->HDB;
		$handler = $_GET['cat_handler'];
		$pages = new Pages();
		$pages->CFG->DB = $this->HDB;
		$ObjPageId = $db->getField("Objects", "PageId", $objid);
		if(!($CatPageId = $db->getValWhere("Pages", "Id", "where Rozdil = $ObjPageId and Name = 'categories'"))){
			$db->q("insert into Pages (Name, Rozdil) values ('categories', $ObjPageId)", true);
			$CatPageId = $db->insId();
		}
		
		if($catid){
			if($PageId = $db->getField("Categories", "PageId", $catid)){
				$PageName = $db->getField("Pages", "Name", $PageId);
			}
			else{
				$db->q("insert into Pages (Rozdil, Name) values ($CatPageId, '$catid')", true);
				$PageId = $db->insId();
				$db->q("update Categories set PageId = $PageId where Id = $catid");
				$cat_old_data = $db->getLine("*", "Categories", "Id = $catid");
				$data['name']['ua'] = $cat_old_data['Name_ua'];
				$data['name']['ru'] = $cat_old_data['Name_ru'];
				$PageName = $catid;
			}
		}
		else{
			$db->q("insert into Pages (Rozdil, Name) values ($CatPageId, '$catid')", false);
			$PageId = $db->insId();
		}
		
		if($PageId){
			foreach($data['name'] as $lang=>$value){
				//print "$lang=>$value<br>";
				$pv = array(
					'parent'	=> '',
					'Name'		=> $PageName,
					'id'		=> $PageId,
					'lang'		=> $lang,
					'Title'		=> $value,
					//'Source'	=> $values['source'][$this->lang],
					'InMap'		=> 1
				);
				$pages->postData($pv);
			}
			if($catid){
				$db->q("update Categories set Measure = '$measure', CountOf = '$count', FullPlaces = '$fullplaces', AddPlaces = '$addplaces', CatHandler = '$handler' where Id = $catid ");
			}
			else{
				$db->q("insert into Categories (ObjId, Measure, CountOf, PageId, FullPlaces, AddPlaces, CatHandler) values ($objid, '$measure', '$count', $PageId, '$fullplaces', '$addplaces', '$handler')");
				$catid = $db->insId();
				$db->q("update Pages set Name = '$PageId' where Id = $PageId");
			}
			
			foreach($prices as $period => $price){
				if($db->getValWhere("Prices", "Price", "where PerId = $period and CatId = $catid", false)!==NULL)
					$q = "update Prices set Price = '$price' where PerId = $period and CatId = $catid";
				else 
					$q = "insert into Prices (Price, PerId, CatId) values ('$price', $period, $catid)";
				//print "[$q]<br>";
				$db->q($q);
			}
		}
		else print "Page Undefined!";
	}
	
	function Edit($objid, $measure, $fullplaces, $addplaces, $count, $data){
		foreach($values['name'] as $lang => $value){
			$pv = array(
				'id'		=> $PageId,
				'lang'		=> $lang,
				'Title'		=> $value,
			);
			$pages->postData($pv);
		}
	}
}
?>