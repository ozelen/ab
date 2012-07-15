<?

class ObjModules extends HotCat{
	function ObjModules(){
		$this->inc();
	}
	
	function addModule($owner_table, $owner_id, $handler){
		$db = $this->CFG->HDB;
		$db->q("insert into Modules (OwnerTable, OwnerId, ModuleHandler) values ('$owner_table', $owner_id, '$handler')");
	}
	
	function getModules($owner_table, $owner_id, $handler=NULL){
		$db = $this->CFG->HDB;
		$handler_condition = $handler ? " and ModuleHandler = '$handler' " : NULL;
		$q = "select * from Modules where OwnerTable = '$owner_table' and OwnerId = $owner_id $handler_condition";
		$modules = $db->q($q);
		$gallery = new gallery();
		while($o=mysql_fetch_array($modules)){
			switch($o['ModuleHandler']){
				case 'album': $xml.= $gallery->Show($o['Id'], $owner_table, $owner_id, 0, 50); break;
				default:
					$xml.='
						<module id="'.$o['Id'].'" page="'.$o['PageId'].'">
							<name>'.$o['Name'].'</name>
							<handler>'.$o['ModuleHandler'].'</handler>
							<owner id="'.$o['OwnerId'].'">'.$o['OwnerTable'].'</owner>
							<data>
								<image>'.$o['TitleImage'].'</image>
								<title>'.$o['Title'].'</title>
								<text>'.$o['Source'].'</text>
								<params>'.$o['Params'].'</params>
							</data>
						</module>
					';
				break;
			}
		}
		$xml = "
			<modules>
				$xml
			</modules>
		";
		return $xml;
	}
	function editModule($id, $postvars){
		$db = $this->CFG->HDB;
		$Params = $postvars['Params'];
		$db->q("update Modules set Params = '$Params' where Id = $id");
	}
	function delModule($id){
		$db = $this->CFG->HDB;
		$handler = $db->getField("Modules", "ModuleHandler", $id);
		if($handler=="album"){
			$gal = new gallery();
			$gal->delAlbum($id);
			print "Album deleted!";
		}
		$db->q("delete from Modules where Id = $id");
	}
}
?>