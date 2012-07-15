<?
//require_once "geo_controller.php";
class Geo extends MNG
{
	var $cityId;
	var $ctryid;
	var $vars;
	var $adm1id;
	var $adm2id;
	var $adm3id;
	var $officename;
	var $company;
	var $currentPlace;
	var $company_id;
	var $adminSequence;
	function Geo()
	{
		$this->inc();
	}

	public function GeoNames($vars){
		//return false;

		$companies = new Companies();

		$this->ctryid       = $this->DB->getFieldWhere("GeoCountries", "ISO", "where ISO3='" . $vars['country'] . "'");
		$this->vars         = $vars;
		$this->adm1id       = $vars['adm1'];
		$this->adm2id       = $vars['adm2'];
		$this->adm3id       = $vars['adm3'];
		$this->officename   = $vars['officename'];
		$this->adminSequence = array();

		if($this->company = $vars['companyname']){
			$company_id = $this->CFG->DB->getFieldWhere("orgcom", "id_org", " where uname = '$this->company' ");
			$company = $this->company($company_id);
			$this->company_id = $company_id;
		}

		$this->currentPlace = array('uname'=>'undefined', 'id'=>null, 'name'=>'undefined');
		//dbg($vars);
		$countries = $this->listCountries();
		// * Caching
		// * return cache file content if it exists

		$fn = array();
		foreach($vars as $key => $val){$fn[]=$key.'_'.$val;}
		$CacheDir = 'geo/'.join('/', $vars);
		$CacheFileName = join('-', $fn).'.xml';
		$CachePath = $CacheDir.'/'.$CacheFileName;
		//if($CachedContent = $this->getCache($CacheDir, $CacheFileName))return $CachedContent;
		// / Caching

		// foreach ($vars as $key => $val) {print "[$key = $val] <br>";}




		$admin1 = $this->listRegions($this->ctryid, 1);
		$admin2 = $this->listRegions($this->ctryid, 2, $this->adm1id);
		$admin3 = $this->listRegions($this->ctryid, 3, $this->adm2id);

		//dbg("<adm1>$admin1</adm1><adm2>$admin2</adm2><adm3>$admin2</adm3>");
		//dbg($this->adminSequence);
		$cities = $this->listSettlements(array('country_code' => $this->ctryid, 'admin1_code' => $this->adm1id, 'admin2_code' => $this->adm2id));
		$city_cond = array('country_code' => $this->ctryid, '1' => $this->adm1id, '2' => $this->adm2id, '3' => $this->adm3id, '4' => $this->adm4id, 'company'=>$this->company);
		$city = $this->tryCity($city_cond);

		if($this->cityId)$offices = $this->officeList('city_id = ' . $this->cityId, 'biz_name');

		$xml = '
			<geonames>
				' . $countries  . '
				' . $admin1     . '
				' . $admin2     . '
				' . $admin3     . '
				' . $cities     . '
				' . $city       . '
				' . $offices    . '
				' . $company . '
			</geonames>
		';

		// Write cache
		$this->newCache($CacheDir, $CacheFileName, $xml);

	//	dbg($path);
	//	dbg($xml);
	//	dbg($admin1);
		return $xml;
	}

	public function showCity($id)
	{
		//foreach()
	}

    public function logo($id){
        $dir = $this->CFG->guiImgDir."logo/";
        $temp = $dir."l".$id.".*";
        $path = glob("$temp*");
        $logo = pathinfo($path[0]);
        return $logo['basename'];
    }

	public function company($id){
		if(!$id)return;
		$dir = $this->CFG->guiImgDir."logo/";
		$temp = $dir."l".$id.".*";
		$c = mysql_fetch_object($this->DB->q("select * from `orgcom` where id_org = ".$id));


		$logo = file_exists($dir."l".$id.".gif") ? 'logo="true"' : null;

		/*
		foreach(glob("$temp*") as $fpath){
			if(is_file($fpath)){
				$logos= pathinfo($fpath);
			}
		}
		*/
		//$logos = pathinfo(glob("$temp*"));

		$path = glob("$temp*");
		$logo = pathinfo($path[0]);
		$logo_attr = $logo['basename'] ? 'logo="'.$logo['basename'].'"' : null;

		$xml = $c ? '
			<company id="'.$c->id_org.'" '.$logo_attr.'>
				<uname><![CDATA['.$c->uname.']]></uname>
				<name><![CDATA['.$c->name.']]></name>
				<descr><![CDATA['.$c->comm1.']]></descr>
				<email>'.$c->email.'</email>
				<url><![CDATA['.$c->url.']]></url>
				<headquarters>
					<city><![CDATA['.$c->headcity.']]></city>
					<address><![CDATA['.$c->address.']]></address>
					<phone><![CDATA['.$c->phone.']]></phone>
				</headquarters>
				<logo>
					'.print_r($logo, 1).'
				</logo>
				<uri>/companies/'.$c->uname.'</uri>
			</company>
		' : null;
		//dbg($xml);
		return $xml;
	}

	public function officeList($cond, $group_field = null)
	{
		if (is_array($cond)) $condtxt = join(' and ', $cond);
		else {
			$condtxt = $cond;
			$cond = array($condtxt);
		}

		if($group_field){
			$group_cond = 'group by '.$group_field;
			$count_cond = ", count(biz_name) as cnt";
		}

		$q = "
			select b.* $count_cond
			from dump_banks b
			join orgcom com on com.id_org = b.company_id and com.PageId is not null
			where " . $condtxt . "
			$group_cond
			order by biz_name
		";
		$offices = $this->DB->q($q);
		//dbg($q);
		while ($b = mysql_fetch_object($offices)) {
			$company = $this->company($b->company_id);
			$n1 = strtolower($b->uname);
			$n2 = strtolower($this->officename);

			if (($group_cond && $b->cnt > 1) && ($n1 == $n2)) {
				$new_arr = $cond;
				$new_arr[] = "b.`uname` = '$this->officename'";
				$included = $this->officeList($new_arr);
			}else{
				$included = null;
				$info_xml = '
					<info><![CDATA['.       $b->biz_info . ']]></info>
					<cat><![CDATA['.        $b->cat_primary . ']]></cat>
					<sub><![CDATA['.        $b->cat_sub . ']]></sub>
					<address><![CDATA['.    $b->e_address . ']]></address>
					<zip>'.                 $b->e_postal . '</zip>
					<email><![CDATA['.      $b->biz_email.']]></email>
					<phone><![CDATA['.      $b->biz_phone.']]></phone>
					<fax><![CDATA['.        $b->biz_fax.']]></fax>
					<web>
						<title><![CDATA['.  $b->web_meta_title.']]></title>
						<desc><![CDATA['.   $b->web_meta_desc.']]></desc>
						<url><![CDATA['.    $b->web_url.']]></url>
					</web>

				';
			}

			//if($this->vars['officeid'] == $b->id)print "<p>".$this->vars['officeid']." = ".$b->id." </p>";
			//if($included)dbg($included);
			$ofid = $this->vars['officeid'];

			if(
				$ofid == $b->id
				|| (!$ofid && $n1==$n2 && $b->cnt==1)
			){
				//print "opened [$ofid = $b->id] [$n1==$n2 && $b->cnt<=1] <br> ";
				$opened='opened="opened"';
			}
			else $opened = null;

			// $uri = "$c->country/$c->admin1/$c->admin2/$c->admin3/$city";

			$xml .= '
				<office id="' . $b->id . '" '.$opened.'>
					<uri>'.$uri.'</uri>
					<name><![CDATA[' . $b->biz_name . ']]></name>
					<uname><![CDATA[' . $b->uname . ']]></uname>
					<count>' . $b->cnt . '</count>
					' . $info_xml . '
					' . $included . '
					'.$company.'
					<loc>
						<city id="'.$c->city.'"></city>
						<lat>'.$b->latitude.'</lat>
						<lng>'.$b->longitude.'</lng>
					</loc>
				</office>
			';
		}
		//dbg($xml);
		//if($opened)dbg($company);
		$xml = $xml ? "<offices>$xml</offices>" : null;
		return $xml;
	}

	public function tryCity($cond)
	{
		if (!is_array($cond)) return;
		foreach ($cond as $k => $v) {
			$cond[$k] = addslashes($v);
		}
		$country = $cond['country_code'];
		$adm = array();
		//dbg($cond);
		for ($i = 1; $i <= 4; $i++) {
			if ($cond[$i]) $adm[$i] = $cond[$i];
			else {
				$city_pos = $i - 1;
				$city = $cond[$city_pos];
				//print "[".$adm[$city_pos]."]<br>";
				unset($adm[$city_pos]);
				break;
			}
		}

		foreach ($adm as $key => $value) {
			$admcode = $this->adminSequence[$value]['code'];
			$adm_cond_txt .= " and `admin" . $key . "_code` = '$admcode'";
		}

		$q = "select * from GeoNames where country_code = '$country' $adm_cond_txt and uname = '$city' ";
		$c = mysql_fetch_object($this->DB->q($q));
		//dbg($q);
		$this->cityId = $c->geonameid;
		$uri = $uri = $this->getUri(array($this->vars['country'], $c->admin1_code, $c->admin2_code, $c->admin3_code, $c->admin4_code, $c->uname));
		$xml = '
			<city id="' . $c->id . '" geoid="' . $c->geonameid . '" >
				<uri>'.$uri.'</uri>
				<uname>'.$c->uname.'</uname>
				<name><![CDATA[' . $c->name . ']]></name>
				<lat>' . $c->latitude . '</lat>
				<lng>' . $c->longitude . '</lng>
				<population>' . $c->population . '</population>
				<elevation>' . $c->elevation . '</elevation>
				<timezone>' . $c->timezone . '</timezone>
			</city>
		';
		//dbg($xml);
		return $xml;
	}

	public function listCountries()
	{
		$company_cond = $this->company_id ? " and b.company_id = ".$this->company_id : '';

		$q = "
			select c.*, COUNT(b.id) as count_banks from `GeoCountries` c
			join dump_banks b on b.`city_id` is not null and b.country = c.`ISO` $company_cond
			join orgcom com on com.id_org = b.company_id and com.PageId is not null
			group by c.id
		";
		//dbg($q);
		$geo = $this->DB->q($q);
		while ($c = mysql_fetch_object($geo)) {
			$xml .= '
				<country id="' . $c->id . '" geoid="' . $c->geonameid . '">
					<uri>' . strtolower($c->ISO3) . '</uri>
					<countryCode>' . $c->ISO . '</countryCode>
					<countryName>' . $c->Country . '</countryName>
					<isoNumeric>' . $c->ISONumeric . '</isoNumeric>
					<isoAlpha3>' . $c->ISO3 . '</isoAlpha3>
					<fipsCode>' . $c->fips . '</fipsCode>
					<continent>' . $c->Continent . '</continent>
					<capital><![CDATA[' . $c->Capital . ']]></capital>
					<areaInSqKm>' . $c->Area . '</areaInSqKm>
					<population>' . $c->Population . '</population>
					<currencyCode>' . $c->CurrencyCode . '</currencyCode>
					<languages><![CDATA[' . $c->Languages . ']]></languages>
					<geonameId>' . $c->geonameid . '</geonameId>
					<count_banks>' . $c->count_banks . '</count_banks>
				</country> 
					';
		}
		//dbg($xml);
		$xml = "<countries>$xml</countries>";
		return $xml;
	}

	public function listRegions($ctry_code, $level, $node = NULL)
	{
		if($level>1 && !$node) return; // try to optimize


		if ($node) {
			//    $node_cond=" and admin1_code = '".addslashes($node)."'";
			//    $join_cond="and b.admin2 = r.admin2_code";
			//if(!$this->vars['adm'.$level+1]) return;
			$join_cond = '';
			//$this->DB->getFieldWhere("GeoAdminCodes", "admin".$level."_code", " where uname = '".$this->vars['adm'.$level]."' ",1);


			for ($l = 1; $l < $level; $l++) {
				$uname = addslashes($this->vars['adm'.$l]);
				$adm = mysql_fetch_object($this->DB->q("select name, uname, admin".$l."_code as code from GeoAdminCodes where uname = '$uname' and `type` = 'adm$l' and country_code = '$ctry_code' $node_cond "));
				$this->adminSequence[$adm->uname] = array('name'=>$adm->name, 'code'=>$adm->code);
				$node_cond .= " and admin" . $l . "_code = '" . $adm->code . "'";

				//$node_cond .= " and admin" . $l . "_code = '" . addslashes($this->vars['adm' . $l]) . "'";
				//$node_cond .= " and r.uname = '" . addslashes($this->vars['adm' . $l]) . "'";
				$join_cond .= " and b.admin" . ($l + 1) . " = r.admin" . ($l + 1) . "_code";

				//print "[$adm->code]";
			}
		}


		//$adm = mysql_fetch_object($this->DB->q("select admin".$level."_code as code from GeoAdminCodes where uname = '$uname' and `type` = 'adm$level' and country_code = '$ctry_code' $node_cond "));
		//dbg("select admin".$level."_code as code from GeoAdminCodes where uname = '$uname' and `type` = 'adm$level' and country_code = '$ctry_code' $node_cond ");



		if($this->company_id){
			$join_cond .= " and b.company_id = ".$this->company_id;
		}

		$q = "
			select r.*, count(b.id) as count_banks
			from GeoAdminCodes r
			join dump_banks b
				on b.admin1 = r.admin1_code
				$join_cond
			join orgcom com on com.id_org = b.company_id and com.PageId is not null
			where `type` = 'adm$level' and country_code = '$ctry_code' $node_cond
			group by r.id
		";

		//dbg($q." -- [adm$level = $node]");
		//$qs = "select ";


		$geo = $this->DB->q($q);


		if (!$this->DB->AffectedRows()) { // if region not found, then search the city
			//print "[$node, $cityid]";
			return NULL;
		}
		$xml = '';
		while ($c = mysql_fetch_array($geo)) {
			$admcode = $c['admin' . $level . '_code'];
			$ident = is_numeric($admcode) ? (int)$admcode : strtolower($admcode);

			$ident = $c['uname'];

			$xml .= '
				<region id="' . $c['id'] . '" geoid="' . $c['geonamesid'] . '">
					<uri>' . $ident . '</uri>
					<countryCode>' . $c['country_code'] . '</countryCode>
					<name><![CDATA[' . $c['name'] . ']]></name>
					<ascii>' . $c['asciiname'] . '</ascii>
					<adm_code>' . $admcode . '</adm_code>
					<count_banks>' . $c['count_banks'] . '</count_banks>
				</region>
			';
			//$c++;
		}
		//array('uname'=>'undefined', 'id'=>null, 'name'=>'undefined');

		$xml = '<regions level="' . $level . '" country="' . $ctry_code . '">' . $xml . '</regions>';
		//dbg($xml);
		return $xml;
	}

	public function listSettlements($cond_arr)
	{
	//	dbg($cond_arr);
		foreach ($cond_arr as $key => $value){
			if($adm = $this->adminSequence[$value]['code'])
			$cond_arr[$key] = $adm;
		}
	//	dbg($cond_arr);

		$cond = array();
		foreach ($cond_arr as $key => $value) {
			if ($value) {
				$value = is_numeric($value) ? $value : "'" . addslashes($value) . "'";
				$cond[] = "`$key` = $value";
			}
		}
		$cond_str = join(" and ", $cond);
		$cond_company = $this->company_id ? " and company_id = ".$this->company_id : "";

		if (!$cond_str) return NULL;
		$q = "
			select 
				s.id, 
				s.geonameid, 
				s.country_code,
				s.admin1_code,
				s.admin2_code,
				s.admin3_code,
				s.admin4_code,
				s.name,
				asciiname, 
				iso3,
				s.uname,
				count(b.id) as count_banks
			from GeoNames s 
			join GeoCountries c on s.country_code = c.ISO
			join dump_banks b on s.geonameid = b.city_id
				$cond_company
		    join orgcom com on com.id_org = b.company_id and com.PageId is not null
			where $cond_str
			group by s.id
			order by count_banks desc
			limit 100
		";
		//dbg($q);
		$geo = $this->DB->q($q);
		$guri = '';
		if(!$geo)return "<error />";
		while ($c = mysql_fetch_object($geo)) {
			//$iso3 = $this->CFG->DB->getFieldWhere("GeoCountries", "ISO3", " where ISO = ''");
			$guri = array($c->iso3, $c->admin1_code, $c->admin2_code, $c->admin3_code, $c->admin4_code, $c->uname);
			$uri = $this->getUri($guri);
			//$uri = "$iso3, $c->admin1_code, $c->admin2_code, $c->admin3_code, $c->admin4_code, $c->asciiname";
			$xml .= '
				<settlement id="' . $c->id . '" geoid="' . $c->geonameid . '">
					<name><![CDATA[' . $c->name . ']]></name>
					<count_banks>' . $c->count_banks . '</count_banks>
					<uname><![CDATA[' . $c->asciiname . ']]></uname>
					<ascii><![CDATA[' . $c->asciiname . ']]></ascii>
					<uri>' . $uri . '</uri>
				</settlement>
			';
		}
		//dbg($this->company_id."\n\n".$xml);
		$xml = "<settlements>$xml</settlements>";
		dbg($guri);
		return $xml;
	}

	function getUri($path_arr){
		$path = array();
		foreach ($path_arr as $p) {
			if ($p) $path[] = strtolower($p);
		}
		return join("/", $path);
	}

}

?>