<?
class Database{
	var $DB;
	var $mysqllog;
	var $mysqlpas;
	var $LastResult;
	var $dbName;
	function printError(){
		//if($err=mysql_errno($this->DB))
			//return '['.$err.' : '.mysql_error($this->DB).']<br>';
	}
	function Database($dname, $log, $pas, $host){
		$this->DB=$this->Connect($dname, $log, $pas, $host);
		return $this->DB;
	}
	function sux(){
		print "sux";
	}
	function Connect($dname, $log, $pas, $host="localhost"){
		$d = mysql_connect($host, $log, $pas, true);
		$res = $d;
		$this->dbName = $dname;
		mysql_select_db($dname, $res);
		mysql_query ('SET character_set_client = utf8', $res);
		mysql_query ('SET character_set_connection = utf8', $res);
		mysql_query ('SET character_set_results = utf8', $res);
		mysql_query('SET collation_connection = utf8_unicode_ci', $res);
		mysql_query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'", $res);
		print $this->printError();
		return $this->DB=$res;
	}
	function Disconnect($res){
		
	}

	function q($query, $print=false){
		//$print = true;
		$rsi = new rsi();
		$rsi->start();
		$result=mysql_query($query, $this->DB);
		if($print)print "\n$query\n";
		$rsi->msg("query: ".$query);
		$this->LastResult = $result;
		if($err=mysql_error($this->DB))dbg("$err\n\n".$query);
		return $result;
	}

	function qr(){
		return mysql_query($query, $this->DB);
	}
	
	function getField($table, $field, $id, $print=false){
		$q="select $field from $table where Id = '$id'";
		$quer = $this->q($q, $print);
		print $this->printError();
		$r=mysql_fetch_array($quer);
		return $r[0];
	}
	
	function getFieldWhere($table, $field, $where, $print=false){
		return $this->getValWhere($table, $field, $where, $print);
	}
	
	function getValWhere($table, $field, $where, $print=false){
		$query="select $field from $table $where";
		$r=mysql_fetch_array($this->q($query,$print));
		//print $r[0];
		//if($print)print $rsi->msg("value: [$r[0]]\n");
		print $this->printError();
		return $r[0];
	}
	
	function getLine($fields, $table, $where, $print=false){
		$q="select $fields from $table where $where";
		$quer = $this->q($q, $print);
		$r=mysql_fetch_array($quer);
		return $r;
	}
	
	function insId(){
		return mysql_insert_id($this->DB);
	}
	
	function inObj(){
		return mysql_fetch_object($this->LastResult);
	}
	function inArr(){
		return mysql_fetch_array($this->LastResult);
	}
	
	function AffectedRows(){
		return mysql_affected_rows($this->DB);
	}
	
	function Error(){
		if($err=mysql_error($this->DB)){
			print '<p>'.$err.'</p>';
			return mysql_errno($this->DB);
		}
		else return false;
	}
	
	// Äîäàííÿ ëàïîê äëÿ çì³ñòó ïîë³â
	function Clean($str){
		//$str=addslashes($str);
		
		return $str;
	}
	// InsVals([array]) - îòðèìóº ìàñèâ òèïó êëþ÷=çíà÷åííÿ òà ôîðìóº ñòðîêó äëÿ âñòàâêè/ðåäàãóâàííÿ ïîë³â
	// InsVals([ìàñèâ ïîëå=çíà÷åííÿ] [ôîðìàò âèäà÷³ (äëÿ âñòàâêè àáî çàì³íè çàïèñó)])
	function InsRepVals($Vals){
		if(!$Vals)return false;
		
		foreach($Vals as $key => $value){
			if($i<count($Vals)-1){
				$zpt=', ';
			}else{
				$zpt='';
			}$i++;
			//print "$key => $value<br>";
			$res['InsKeys'].=$key.$zpt;
			$res['InsVals'].="'".addslashes($value)."'".$zpt;
			$res['RepVars'].=$key.' = '."'".addslashes($value)."'".$zpt;
		}
		return $res;
	}
	
	function getIdent($tname, $id){
		$this->getField($tname, "Ident", $id);
	}
	
	function getIDbyIdent($tname, $ident) {
		if($tname && $ident)return $this->getValWhere($tname, "Id", "where Ident = '$ident'");
		else return false;
	}
	
	function getAllChildsIn($table, $id){
		$tbl = $this->q("select Id from $table where HostId = $id");
		$res = array();
		$childs='';
		
		while( $t = mysql_fetch_object($tbl) ){
			$childs.=$this->getAllChildsIn($table, $t->Id);
		}
		//$childs = $childs ? ", ".$childs : NULL;
		return $id.", ".$childs;
	}
	
	function getAllChildsIn_old($table, $id){
		$tbl = $this->q("select Id from $table where HostId = $id");
		$res = array();
		$childs='';
		
		while( $t = mysql_fetch_object($tbl) ){
			$res[] = $t->Id;
			$childs.=$this->getAllChildsIn($table, $t->Id);
		}
		return join(", ", $res).$childs;
	}
}

?>