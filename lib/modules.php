<?
require_once "edit/sitemap.php";
require_once "objects/objects.php";

class Modules extends MNG{
	function Modules(){
		$this->inc();
		$this->categories = new Categories();
		$this->ModuleClassArray = 
		array(
			'hotcat' 	=> new Hotcat(),
			'news'	 	=> new News(),
			'pages'		=> new SiteMap(),
			'users'		=> new Users(),
			'companies' => new Companies(),
			'offices'   => new Offices(),
			'geo'   	=> new GeoController()
		);
	}
	function Exec($name, $params, $templ=NULL){

		switch($name){
			case 'login': print $this->login(); break;
			case 'logout': print $this->logout(); break;
			default:
				$cl = split('[/.]', $name);
				$xml = $this->classExec($cl);
			break;
		}


		if(preg_match("|(.*)\((.*)\)|", $name, $regs))
			$name=$regs[1];

		$single_xml = $this->CFG->xmlHeader.'
			<block lang="'.$this->CFG->lang.'">
				'.$this->addPresets().'
				<name><![CDATA['.$name.']]></name>
				<content>'.$xml.'</content>
			</block>';

		return (
			$templ 
				? $this->xmlOut->qShow($single_xml, $templ, false) 
				: $xml
			);

	}

	function iface($arr_call){
		$current = array_shift($arr_call);
		if(preg_match("|(.*)\((.*)\)|", $current, $regs)){
			$function_name = $regs[1];
			$function_params = $regs[2];
			//print "[$all][$current] is function";
			return $this->Exec($function_name, $function_params);
		}
		if(is_object(($obj = $this->ModuleClassArray[$current])))
		return $obj->iface($arr_call);
		//else print "<p>[$current] id not an object</p>";
	}


	function classExec($arr_call){
		if(is_object(($obj = $this->ModuleClassArray[array_shift($arr_call)])))
		return $obj->iface($arr_call);
	}

	function login(){
		$pv = $this->CFG->PostVars;
		$resp = array('result'=>'error', 'descr'=>'empty');
		$db = $this->CFG->DB;
		$login = $pv['login'];
		$pass = $pv['pass'];
		$md5_pass= md5($pv['pass']);
		$res = $db->q("select * from Users where (Email = '$login' or Username = '$login') and Pass = '$md5_pass' ");
		if($u = mysql_fetch_object($res)){
			$users = new Users($login, $pass);
			$users->Login($login, $pass);
			$resp = array(
				'result' => 'ok',
				'descr' => "Welcome, $u->Username!",
				'username' => "$u->Username",
				'pass' => $u->Pass,
				'sysmsg' => $this->CFG->SysMsg->json()
			);
			//$this->
		}else{
			$resp = array(
				'result' => 'error',
				'descr'  => 'Incorrect login/password'
			);
		}
		return json_encode($resp);
	}

	function logout(){
        session_destroy();
	}
}
?>