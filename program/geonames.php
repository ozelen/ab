<?

function geoNamesDump(){
	header ("Content-type: text/plain; charset=utf-8"); 
	$fi = "/var/www/djerelo/data/sql/UA.txt";
	$fo = "/var/www/djerelo/data/sql/dump/geonames.sql";
	if(!file_exists($fi)){
		print "Error input file!";	
		return false;
	}
	
	$str = '';
	$sql_header = "insert into geonames (geonameid, name, asciiname, alternatenames, latitude, longitude, feature_class, feature_code, country_code, cc2_code, admin1_code, admin2_code, admin3_code, admin4_code, population, elevation, gtopo30, timezone, modification) values";
	$fp = fopen($fi, "r");
	$row_arr = array();
	while ($data = fgetcsv ($fp, 10000, "\t")) {
		$row++;
		$num = count ($data);
		for($c=0; $c < $num; $c++) {$data[$c]=addslashes($data[$c]);}
		$row_arr[$i++]="('".join("', '", $data)."')";
		if($i>=100){
			$str.= "$sql_header\n".join(",\n", $row_arr).";\n\n";
			$i=0;
			unset($row_arr);
		}
	}
	fclose ($fp);

	$fp = fopen($fo, "w");
	fwrite($fp, $str);
	fclose ($fp);	
	
	print file_get_contents($fo);
}

geoNamesDump();

?>