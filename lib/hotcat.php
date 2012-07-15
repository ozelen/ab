<?

include("objects.php");

include("categories.php");
include("edit/categories.php");

include("objmodules.php");
include("cities.php");

class HotCat extends Modules{

	function HotCat(){
		$this->inc();
		$this->ModuleClassArray = array(
			'objects' 		=> new Objects(),
			'categories' 	=> new Categories(),
			'cities' 		=> new Cities()
		);
	}
	
	function Exec($name, $params=NULL){
		parse_str($params, $p_arr);
		//foreach($this->uriVars as $k=>$v)print "<p>[$k]=>[$v]</p>";
		switch($name){
			case "goto": return $this->goTo($this->uriVars['entity']); break;
		}
	}
	
	function setTemplate($ident){
		$db = $this->CFG->DB;
		//$db->getFieldWhere("");
		$this->CFG->TEMPLATE = "sites/zdravtour/index.xslt";
	}
	
	function getName($table, $id){
		if(!$table || !$id || !is_numeric($id)) return false;
		//print "[$table, $id]";
		$t = mysql_fetch_object($this->CFG->HDB->q("select Title from $table obj join PageData pd on obj.PageId = pd.PageId and Lang = '".$this->CFG->lang."' where obj.Id = $id"));
		return $t->Title;
	}
}
	
	
?>