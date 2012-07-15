<?

class Visitor{
	var $Id;
	function Visitor($db){
		/*
		$this->DB = $db;
		$this->Id = $_COOKIE['djVisitor'];
		$ip = $_SERVER['REMOTE_ADDR'];
		$ex = mysql_fetch_object($db->q("select * from Visitors where Ip = '$ip' and Registered like '".date("Y-m-d H")."%'"));
		if(!$this->Id && !$ex){
			$this->DB->q("insert into Visitors (Ip, Registered, LastVisit, UserId) values ('$ip', now(), now(), '$userid')");
			$this->Id = setcookie('djVisitor', $db->insId());
			return $this->Id;
		}
		*/
	}
	
	function incObjStat($owner, $id){
		print "<h1>increment $owner, $id</h1>";
		$db = $this->DB;
		$today = date("Y-m-d");
		$q="
			insert ObjStat set 
				OwnerTable='$owner', 
				OwnerId = $id, 
				Day = now(), Val = 1,
				Visitor = ".$this->Id."
			ON DUPLICATE KEY update Val = `Val`+1
		";
		$db->q($q);
	}
	
}

class UserData extends MNG{
	var $Id;
	var $Name;
	var $Pass;
	var $Email;
	var $Level;
	var $Data;
	var $XML;
	function UserData(){
		$this->inc();
		//$this->getData();
	}
	function getData($ln='', $pw=''){
		$db = $this->CFG->DB;
		if(!$ln){
			$ln = $this->Email;
			$pw = $this->Pass;
		}
		$q = "select * from Users where (Email = '$ln' or Username = '$ln') and Pass = '$pw' ";
		$fields = mysql_fetch_object($db->q($q));
		$this->Name = $fields->Username;
		$this->Email = $fields->Email;
		$this->Pass = $fields->Pass;
		$this->Id = $fields->Id;
		$this->Level = $fields->Level;
		$this->getXML();
	}
	function getXML(){
		return $this->XML = '
			<user id="'.$this->Id.'">
				<name>'.$this->Name.'</name>
				<email>'.$this->Email.'</email>
				<level>'.$this->Level.'</level>
			</user>
		';
	}
	function getArrById($id){
		return mysql_fetch_object($this->CFG->DB->q("select * from Users where Id = $id"));
	}
}

class Users extends Modules{
	var $UserData;
	function Users($ln='', $pw='', $md5=false){
		$this->inc();
		if($md5)$pw = md5($pw);
		$this->UserData = new UserData($ln, $pw);
		//print "[users]";
	}
	
	function getIdByField($key, $value){
		$db = $this->CFG->DB;
		return $db->getFieldWhere("Users", "Id", "$key = '$value'");
	}
	
	function Exec($name, $params=NULL){
		parse_str($params, $p_arr);
		$pv = $this->CFG->PostVars;
		//foreach($pv as $k=>$v){print "<h2>$k=>$v</h2>";}
		switch($name){
			case "login": $this->Login($pv['email'], $pv['password'], $pv['direct']); break;
			case "logout": $this->Logout($pv['direct']); break;
		}
	}
	
	function checkRole($area, $id, $db_name='skiworld'){
		if($this->CFG->UserData->Level==1) return "admin";
		if(is_object($this->CFG->Databases[$db_name])){
			return $this->CFG->Databases[$db_name]->getFieldWhere('AccessPoints', 'Role', "where AccessArea = '$area' and Params = $id");
		}else return false;
	}
	
	function Logout(){
		session_destroy();
		header("Location: ".$_SERVER["HTTP_REFERER"]);
	}
	
	function Login($ln, $pw, $direct=''){
		//print "<h1>[$ln, $pw]</h1>";
		if(!$ln){
			$this->CFG->SysMsg->Add("Login not found", "error");
			return false;
		}
//		if(!$direct)$direct = $this->CFG->Domain;
		
		$this->UserData->getData($ln, md5($pw));

		//if($_POST['keystring'] == $_SESSION['captcha_keystring']){
			if($this->UserData->Id){
				//session_name('djerelo_info');
			 	ini_set("session.cookie_lifetime",0);
				session_start();
				$_SESSION['uid']=$this->UserData->Id;
				$_SESSION['uname']=$this->UserData->Name;
				$_SESSION['pass']=$this->UserData->Pass;
				$_SESSION['email']=$this->UserData->Email;
	//			header("Location: http://$direct/");
				$this->CFG->SysMsg->Add('Welcome, '.$this->UserData->Name.'!', "ok");
			}else{
				$this->CFG->SysMsg->Add("Incorrect Login/Password [$ln, $pw]", "error");
			}
		//} else $this->CFG->SysMsg->Add("Incorrect code", "error");

	}
	

	function Show(){
	
	}
	
	function UserList($templ='', $id=''){
		$where = $id ? "where Id = $id":'';
		$db = $this->CFG->DB;
		$usr = $db->q("select * from Users $where");
		while($u = mysql_fetch_array($usr)){
			$xml.='
				<user id="'.$u['Id'].'">
					<name>'.$u['Username'].'</name>
					<pass>'.$u['Pass'].'</pass>
					<level>'.$u['Level'].'</level>
					<status>'.$u['Status'].'</status>
					'.$this->AccessPoints($u['Id']).'
				</user>
			';
		}
		$xml = "<userlist>$xml</userlist>";
		return ($templ ? $this->xmlOut->qShow($this->CFG->xmlHeader.$xml, $templ, false) : $xml);
	}
	
	/* ** Check on permissions ** */
	
	function isEditable($area, $id, $db='skiworld'){
		$db = $this->CFG->Databases['$db'];
		$db->q("select Id from AccessPoints where UserId = ".$this->CFG->UserId." and AccessArea = 'area'");
	}
	
	/** *** **/
	
	function AccessPoints($id, $templ = ''){
		$db = $this->CFG->DB;
		$usr = $db->q("select * from AccessPoints where UserId = $id");
		while($u = mysql_fetch_array($usr)){
			$xml.='
				<point id="'.$u['Id'].'">
					<user>'.$u['UserId'].'</user>
					<area>'.$u['AccessArea'].'</area>
					<params>'.$u['Params'].'</params>
				</point>
			';
		}
		$xml = '<access-points user="'.$id.'">'.$xml.'</access-points>';
		return ($templ ? $this->xmlOut->qShow($this->CFG->xmlHeader.$xml, $templ, false) : $xml);
	}
	
	function postPoint($pv){
		//if(!$pv['Username'])return false;
		if($pv['Id'] && $pv['AccessArea']=='' && $pv['Params']=='')$this->delPoint($pv['Id']);
		$vars = array(
			'Id'			=> $pv['Id'],
			'UserId'		=> $pv['UserId'],
			'AccessArea'	=> $pv['AccessArea'],
			'Params'		=> $pv['Params']
		);
		$this->uniPostData('AccessPoints', $vars, $this->CFG->DB);
	}
	
	function delPoint($id){
		$db = $this->CFG->DB;
		$db->q("delete from AccessPoints where Id = $id");
	}
	
	function postData($pv){
		//foreach($pv as $k => $v){print "<p>$k = $v</p>";}
		if(!$pv['Username'])return false;
		$vars = array(
			'Username'		=> $pv['Username'],
			'Pass'			=> md5($pv['Pass']),
			'Level'	=> $pv['AccessLevel'],
			'Status'	=> $pv['UserStatus']
		);
		$this->uniPostData('Users', $vars, $this->CFG->DB);
	}
	
	function Del(){
	
	}
}
?>