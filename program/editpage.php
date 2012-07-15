<?php
	include_once("../config.php");
	
	//session_name('djerelo_info');
	session_start();
	if($_SESSION['email']){
		$caCFG->UserData->getData($_SESSION['email'], $_SESSION['pass']);
	}
	
	
	//foreach($_POST as $k=>$v){print "<p>$k = $v</p>";}
	//print "[".$_GET['mode']."]";

class editPage extends MNG{
	var $cfg;
	var $db;
	var $owner_db;
	var $owner_tbl;
	var $owner_id;
	var $pages;
	var $page_id;
	var $content;
	var $UID;
	var $lang;
	var $today;
	var $field;
	var $node;
	var $forum;
	function editPage(){
		global $caCFG;
		$this->inc();
		$this->pages = new Pages();
		$this->forum = new Forum();
		
		$this->cfg 	= $this->CFG;
		$this->db 	= $this->CFG->DB;
		$this->hdb 	= $this->CFG->HDB;
		$this->get 	= $this->CFG->GetVars;
		$this->pv	= $this->CFG->PostVars;
		
	
		// ***	Incoming parameters
		// **	Owner parametes 
		// *	(if check an access of some object and open its pagetree)
		$this->owner_db 	= $this->pv['db'];
		$this->owner_tbl 	= $this->pv['tbl'];
		$this->owner_id 	= $this->pv['id'];
		$this->UID			= $this->pv['uid'];
		$this->lang			= $this->get['lang'];
		$this->multiform	= $this->get['multiform'];
		$this->mode			= $this->get['mode'];
		$this->field		= $this->get['field'];
		$this->node			= $this->get['node'];
		
		// ** Parameters of requesting page
		$this->page_id	= $this->pv['PageId'];	// : Request page ID
		$this->template	= $this->pv['temp'];	// : 
		if($this->get['temp']){
			$this->template	= $this->get['temp'];
		}
		$this->content	= array();				// : Array of multilanguage Page Content
		
		$this->today = date("Y-m-d H:i");
	}
	
	function Go(){
		
		switch($this->mode){
			case 'postdata':
				if(!$this->checkAccess()){print "You can't moderate this info!"; return false;}
				header("content-type:application/xml;charset=utf-8");
				$this->Edit();
				echo $this->CFG->xmlHeader.$this->getXML();
				//echo "ok";
			break;
			case 'addlang': 
				if(!$this->checkAccess()){print "You can't moderate this info!"; return false;}
				$this->addLang();
				echo $this->Show();
			break;
			
			case 'getfield':
				$this->printField();
			break;
			
			case 'sitemap':
				echo $this->map();
			break;
			
			case "forum":
				//
				$role = $this->CFG->User->checkRole($this->owner_tbl, $this->owner_id, $this->owner_db);
				$appr = $role == 'admin' ? 1 : ($role ? 2 : 0);
				$topic = $this->pv['topic'];
				switch($this->get['act']){
					case "post": 
						$pst = $this->forum->postData($topic, $this->pv, $appr);
						if($pst){
							if($dir = $this->pv['dir']){
								print "move to $dir";
								$this->forum->moveTo($this->pv['topic'], $pst, $dir);
							}
							if($appr) $this->cfg->SysMsg->Add('', 'ok', 'forum.answers.approved');
							else $this->cfg->SysMsg->Add('', 'ok', 'forum.answers.waitmoderate');
						}
						else $this->cfg->SysMsg->Add('', 'error', 'forum.answers.aborted');
						$xml = 
							'<forum>'.
								$this->addXml.
								$this->cfg->SysMsg->XML().
							'</forum>';
						print $this->xmlOut->qShow($xml, $this->template.".xslt");
						/*
						foreach($this->pv as $k=>$v){
							print "$k:$v<br>";
						}
						*/
					break;
					
					case "delmsg":
						if(!$this->checkAccess()){print "Moderation of Comments is closed for you!"; return false;}
						$this->forum->Del($this->get['msg_id']);
						print "comment deleted";
					break;
					
					case "approve":
						if(!$this->checkAccess()){print "Moderation of Comments is closed for you!"; return false;}
						$this->forum->Approve($this->get['msg_id']);
						print "Message approved!";
					break;
					
					case "move":
						if(!$this->checkAccess()){print "Moderation of Comments is closed for you!"; return false;}
						$root_msg = $this->forum->findRoot($this->get['msg_id']);
						//print "[$root_msg]";
						$this->forum->moveTo($root_msg, $this->get['msg_id'], 'book');
						$this->forum->Approve($this->get['msg_id']);
						print "Message has been moved to booking dir.";
					break;
					//moveTo($topic, $msg, $recepient);
					
					case "mail":

						
						$arr = $this->forum->sendEmails($this->get['id']);
						
						
					break;
				}
				
				
				
			break;
			
			default:
				echo $this->Show();
			break;
		}
	}
	
	
	function repForum2(){
		$db = $this->DB;
		$hdb = $this->HDB;
		$objs = $hdb->q("select Id, Topic, AccountCode from Objects");
		while($obj = mysql_fetch_object($objs)){
			if($obj->Topic){
				$db->q("update Msg set Header = 'Objects_$obj->Id' where Id = $obj->Topic");
				$db->q("insert into Msg set Header = 'all', HostNode = $obj->Topic");
				$all_id = $db->insId();
				$db->q("update Msg set HostNode = $all_id where HostNode = $obj->Topic and Header not in ('all', 'book')");
				print "[$obj->AccountCode][".$db->AffectedRows()."] ok\n";
			}else print "ERROR: [$obj->AccountCode]\n";
		}
	}
	
	function searchBook($id){
		$db = $this->DB;
		if($m = mysql_fetch_object($db->q("select Id, Header, HostNode from Msg where Id = $id"))){
			if($m->Header != 'book') return $this->searchBook($m->HostNode);
			else return $m->Id;
		}else return NULL;
	}
	
	function repairForum(){
		header('Content-type: text-plain; charset="utf-8"', true);
		$db = $this->DB;
		$q = "
			select 
				Parent.Id, -- !!!!!
				Child.Id as ChildId,
				Child.Header as Hdr,
				Parent.Header as Obj,
				Child.Content
			from Msg as Child
			left join Msg as Parent on Child.HostNode = Parent.Id
			where Child.Header = 'booking' and Parent.Header = 'booking'
		";
		$roots = $db->q($q);
		while($m = mysql_fetch_object($roots)){
			$db->q("delete from Msg where Id = $m->ChildId");
			/*
			$db->q("update Msg set Header = 'book' where Id = $m->Id");
			//print "$do: [$m->Obj > $m->Hdr] [root: $root]\n";
			
			if( $root = $this->searchBook($m->Id) ){
				$db->q("update Msg set HostNode = $root where Id = $m->ChildId");
				$do = 'upd';
				//print "[$m->ChildId] updated \n";
			}
			else {
				$db->q("delete from Msg where Id = $m->ChildId");
				$do = 'del';
				//print "[$m->ChildId] deleted \n";
				
			}
			print "$do: [$m->Obj > $m->Hdr] [root: $root] [$m->Content]\n";
			*/
		}
	}
	
	function testForum(){
		//header('Content-type: text-plain; charset="utf-8"', true);
		$db = $this->DB;
		$q = "
			select 
				Id, 
				HostNode,
				Header,
				Title,
				Created,
				Content
			from Msg where 
			Header = 'booking'
		";
		$msgs = $db->q($q);
		
		while($msg = mysql_fetch_object($msgs)){
			if($db->getFieldWhere("Msg", "Id", "where Id = $msg->HostNode and Header = 'booking'"))
			$res.= "
				<tr>
					<td>$msg->Id</td>
					<td>$msg->HostNode</td>
					<td>$msg->Header</td>
					<td>$msg->Title</td>
					<td>$msg->Created</td>
					<td>$msg->Content</td>
				</tr>
			";
		}
		$res="<table border=1>$res</table>";
		print $res;
	}
	
	function map(){
		$xml = 
			$this->CFG->xmlHeader.
			'<pages>'.
				$this->addXml.
				$this->pages->getPage($this->page_id, $this->lang, '').
			'</pages>';
		return $this->xmlOut->qShow($xml, $this->template.".xslt", false);
	}
	
	function addLang(){
		$db 	= $this->DB;
		$dbname = $this->owner_db;
		if($db->inObj("select * from $dbname.PageData where Lang = '$this->lang' and PageId = $this->page_id")){
			print "You alredy have [$this->lang] version of this Page";
			return false;
		}
		$q = "insert into $dbname.PageData (LastMod, PageId, Lang) values ('$this->today', $this->page_id, '$this->lang')";
		//print "[$q]";
		$db->q($q);
	}
	
	function printField(){
		$db 	= $this->DB;
		 //$db->q("select $this->field from $this->owner_db.PageData where PageId = $this->page_id");
		 print $db->getFieldWhere($this->owner_db.".PageData", $this->field, "where PageId = $this->page_id and Lang = '$this->lang'");
	}
	
	function getXML(){
		$db 	= $this->DB;
		$page	= $db->getLine('Name', 'Pages', 'Id = '.$this->page_id);
		
		
		if($this->multiform) $multi_attr = ' multiform="true" ';
		else $lang_cond = " and Lang = '$this->lang'";
		if($this->mode)$mode_attr = ' mode="'.$this->mode.'" ';
		
		$q 		= "select * from $this->owner_db.PageData where PageId = $this->page_id ".$lang_cond;
		$qq 	= $db->q($q);
		while($data = mysql_fetch_object($qq)){
			$xml.='
				<var lang="'.$data->Lang.'" lastmod="'.$data->lastmod.'">
					<title><![CDATA['.$data->Title.']]></title>
					<source><![CDATA['.$data->Source.']]></source>
				</var>
			';
		}
		$xml = '
			<page id="'.$this->page_id.'" uid="'.$this->UID.'" lang="'.$this->lang.'" domain="'.$this->CFG->MainDomain.'" '.$multi_attr.' '.$mode_attr.'>
				'.$this->addXml.'
				'.$xml.'
			</page>
		';
		return $xml;
	}
	
	function Show(){
		$xml = $this->getXml();
		return $this->template
			? $this->xmlOut->qShow($this->CFG->xmlHeader.$xml, $this->template.".xslt", false) 
			: $xml
		;
	}
	
	function Edit(){
		$db 	= $this->DB;
		$this->parseData();
		foreach($this->content as $lang=>$data){
			if($db->getFieldWhere($this->owner_db.'.PageData', 'PageId', " where PageId = $this->page_id and Lang = '$lang'"))
				$q = "update $this->owner_db.PageData set Title = '".$data['Title']."', Source = '".$data['Source']."', LastMod = '$this->today' where PageId = $this->page_id and Lang = '$lang' ";
			else
				$q = "insert into $this->owner_db.PageData set Title = '".$data['Title']."', Source = '".$data['Source']."', PageId = $this->page_id, Lang = '$lang', LastMod = '$this->today' ";
			$db->q($q);
		}
	}
	
	function parseData(){
		// ** Fill page content array from POST
		$content = array();
		foreach($this->pv as $key=>$value){
			if(preg_match("/content_(.*)_(.*)/", $key, $arr)){
				$field	= $arr[1];
				$lang 	= $arr[2];
				if(is_array($content[$lang])){
					$content[$lang][$field] = $value;
				}
				else $content[$lang] = array($field => $value);
			}
		}
		
		$this->content = $content;
	}
	
	function checkAccess(){
		//print "[".$this->CFG->UserData->Level."]";
		if(!$this->CFG->UserData->Id)return false;
		$db = $this->CFG->DB;
		// ****************************************************************************//
		// *** Now we checking access for user
		// 1 step : We check on super-user
		if($this->cfg->UserData->Level == 1) return true;

		// 2 step : Checking on access to this objects
		$q = "
			select PageId from $this->owner_db.$this->owner_tbl 
			join $this->owner_db.AccessPoints 
				on AccessArea = '$this->owner_tbl' 
				and Params = $this->owner_id
				and UserId = ".$this->CFG->UserData->Id."
			where Objects.Id = $this->owner_id
		";
		if($obj = $db->inObj($db->q($q))){
			$owner_pageid = $obj->PageId;
		}
		else return false;
		
		// 3 step : Check on linkage Requesting Page with Access Page
		$this->pages->DB = $this->pages->HDB;
		if($this->pages->isParent($owner_pageid, $this->page_id)){
			return true;
		}else return false;
	}
}

$edit = new editPage();
$edit->Go();

?>