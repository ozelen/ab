<?
class News extends Modules{
	var $Doc;
	function News(){
		$this->inc();
	}

	function Exec($name, $params=NULL){
		parse_str($params, $p_arr);
		switch($name){
			case "showcat": 
				$catid = $this->uriVars['news_catid'] ? $this->uriVars['news_catid'] : NULL;
				$catid = $p_arr['news_catid'] ? $p_arr['news_catid'] : NULL;
				//foreach($p_arr as $k=>$v)print "<p>$k=>$v</p>";
				if(!$catid)return false;

				$limit = $p_arr['news_limit'];
				$xml = $this->showByCategory($catid, $limit, $this->uriVars['skip']);
				return  $xml;
			break;
			case "open": 
				$xml =$this->Open($this->uriVars['docid']);
				//print "<textarea>$xml</textarea>";
				return $xml; 
			break;

			case "showlist":
				return $this->showList($p_arr);
			break;

			case "showitem":
				return $this->showItem($this->uriVars['itemid']);
			 break;

			case 'update':
				$this->Update($this->uriVars['itemid']);
			break;

			case 'delitem':
				$this->delItem($this->uriVars['itemid']);
			break;

			case 'additem':
				if($id = $this->Add($this->uriVars['channelid'])){
					return $this->showItem($id);
				}
			break;

		}
	}

	function Update($id){
		$pv = $this->arr2obj($this->CFG->PostVars);
		if(!$pv->title || !$pv->content){print "<p>Incorrect data for update</p>"; return null;}
		$company = $pv->CompanyId ? "'$pv->CompanyId'" : "NULL";
		$q = "
			update Articles set
				pdate       = '$pv->pdate',
				title       = '$pv->title',
				description = '$pv->description',
				content     = '$pv->content',
				channel     = '$pv->channel',
				CompanyId   = $company
			where id = $id
		";
		//dbg($q);
		$this->CFG->DB->q($q);
	}

	function Add($channel=1){
		//dbg(print_r($this->CFG->PostVars, true));
		$pv = $this->arr2obj($this->CFG->PostVars);
		if(!$pv->title || !$pv->content){print "<p>Incorrect data for insertion"; return null;}
		$q = "
			insert into Articles set
				pdate       = '$pv->pdate',
				title       = '$pv->title',
				description = '$pv->description',
				content     = '$pv->content',
				channel     = '$pv->channel',
				CompanyId   = '$pv->CompanyId'
		";
		//dbg($q);
		$this->CFG->DB->q($q);
		return $this->CFG->DB->insId();
	}


	function showList($params){
        $lim = $params['news_limit'] ? $params['news_limit'] : 100;
        $skip = $this->uriVars['skip'] ? $this->uriVars['skip'] : 0;
        $chan = $params['channel'];

		if($lim)$limit  =" limit $lim";
		if($skip)$limit =" limit $skip, $lim";

        $chan_cond = $chan ? " and channel = $chan " : "";

        $conditions = $chan_cond;
		$q = "select * from Articles where 1=1 $conditions order by pdate desc $limit";
		$news = $this->CFG->DB->q($q);
        $ch = array(); // channels
        $match_count=0;
		while($doc = mysql_fetch_object($news)){
			$ch[$doc->channel].= $this->makeItem($doc, true);
            $match_count++;
		}
        $xml = '';
        $pg = new Pages();

        if($chan && $match_count==0)$ch[$chan]=''; // add empty element to display empty <channel> as fact
        foreach($ch as $channel => $ch_xml){
            $title = $pg->getTitle($channel);
            $xml.='
			<channel id="'.$channel.'">
				<title><![CDATA['.$title.']]></title>
				<link>http://allbanks.org/</link>
				<pubDate>'.date("Y-m-d H:i:s", time()).'</pubDate>
				<language>en</language>
				'.$ch_xml.'
			</channel>
		 ';
        }
		return $xml;
	}

	function showItem($id){
		$q = "select * from Articles where Id = $id";
		return $this->makeItem(mysql_fetch_object($res = $this->CFG->DB->q($q)));
	}

	function delItem($id){
		$this->DB->q("delete from Articles where Id = $id");
		print "<p>Item deleted</p>";
	}

	function makeItem($doc, $full=false){
		//$content = $full ? $doc->content : null;
		$CompanyName = Companies::getName($doc->CompanyId);
		$company = $CompanyName ? '<company id="'.$doc->CompanyId.'">'.$CompanyName.'</company>': '';
		return '
			<item id="'.$doc->id.'" uname="'.$doc->uname.'">
			  <title><![CDATA['.$doc->title.']]></title>
			  <description><![CDATA['.$doc->description.']]></description>
			  <content><![CDATA['.$doc->content.']]></content>
			  <pubDate pretty="'.$pretty.'">'.$doc->pdate.'</pubDate>
			  <guid>'.$doc->id.'</guid>
			  <channel>'.$doc->channel.'</channel>
			  '.$company.'
			</item>
		';
	}


	function getListQuery($q, $wide_info=false){
		$db = $this->CFG->DB;
		$qr = $db->q($q);
		while($doc = mysql_fetch_object($qr)){
			//if($wide_info)
			$source = '<content><![CDATA['.$doc->Source.']]></content>';

			//list($y,$m,$d) = explode('-', $doc->tDate,4);
			list($date, $time) = explode(" ",$doc->tDate);
			list($y,$m,$d) = explode("-", $date);
			//list($h, $m) = explode()
			$pretty = "$d.$m.$y";

			$xml.= '
				<item id="'.$doc->Id.'" paper="'.$doc->PaperId.'">
				  <title><![CDATA['.$doc->Name.']]></title>
				  <link>http://skiworld.org.ua/ua/news/show/'.$doc->PaperId.'</link>
				  <description><![CDATA['.$doc->Descr.']]></description>
				  '.$source.'
				  <pubDate pretty="'.$pretty.'">'.$doc->tDate.'</pubDate>
				  <from>'.$doc->tFrom.'</from>
				  <author>'.$doc->tFrom.'</author>
				  <guid>'.$doc->Link.'</guid>
				  <channel>'.$doc->channel.'</channel>
				</item>
			';
		}
		return $xml;
	}

	function Open($id){
		if(is_numeric($id))$where = " where News.Id = $id ";
		else if(is_string($id))$where = " where News.PaperId = '$id' ";
		else if(!$id)return false;

		$q = "
			select News.Id, tDate, tFrom, Name, PaperId, Source, Descr, Link from News 
			left join NewsLinks on DocId = News.Id
			$where
			group by News.Id
		";
		return '
			<items type="news" category="'.$catid.'">
				'.$this->getListQuery($q, true).'
			</items>
		';
	}

	function showByCategory($catid, $limit=NULL, $skip = NULL){
		//print "<h1>[showcat]</h1>";
		if(!$skip)$skip = '0';
		if(!$limit)$limit = $skip.', 20';
		if($limit)$limit_cond = " limit $limit ";
		$q = "
			select Name, tDate, tFrom, PaperId, Descr, Link, Source from News 
			left join NewsLinks on  News.Id = DocId and CaseId = $catid
			group by DocId
			order by tDate desc
			$limit_cond
		";
		//print "$q";
		return '<items type="news" category="'.$catid.'" skip="'.$skip.'">'.$this->getListQuery($q, false).'</items>';
	}

}

/*
function tnews(){
	global $sub;
	$news = new News;
	$uri=split("/",$sub);
	if(isset($uri[1])){
		if(is_numeric($uri[1])){
			return $news->showByCategory($uri[1]);
		}else if($uri[1]=="show"){
			return $news->Open();
		}
	}
}

function tnewsarchive($cat=1){
	global $sub;
	$WNews = new News;
	$WNews->Category = $cat;
	$WNews->TempFile = "news_block1.xslt";
	return $WNews->Show();
}
*/


?>