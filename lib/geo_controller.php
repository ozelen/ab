<?

class GeoController extends Modules{

	function GeoController(){
		$this->inc();
	}

	function Exec($name, $params=NULL){
		parse_str($params, $p_arr);
		//foreach($this->uriVars as $k=>$v)print "<p>[$k]=>[$v]</p>";
		switch($name){
			case "struct": return $this->tree(); break;
		}
	}


	function tree(){
		$db = $this->CFG->DB;
		$res = $db->q("select uname, type, id, node from Taxonomy where node = 0");
		$arr = array();
		$xml = "";
		while($r = mysql_fetch_object($res)){
			$r->uname = preg_replace('/[^(\x20-\x7F)]*/','', $r->uname);
			$r->uname = preg_replace('/,/','', $r->uname);
			$arr[$r->uname] = $r;
			$xml.="<item><![CDATA[$r->uname]]></item>";
		}
		return "<items>$xml</items>";
		//$obj = (object) $arr;
		//$xml = xmlOut::obj2xml($obj, geo);
		//print_r($obj);
	}
}
?>