<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Виктория
 * Date: 24.10.11
 * Time: 15:31
 * To change this template use File | Settings | File Templates.
 */


class SiteMap extends Modules{
	private $Pages;
	function SiteMap(){
		$this->Pages = new Pages();
		$this->Edit = new EditPages();
		$this->inc();
	}

	function Exec($name, $params, $templ=NULL){
		//print "[$name]";
		//print "exec [$name] <br />";
		switch($name){
			case "menu" 	: return $this->menu($params); break;
			case "pagelist"	: return $this->pageList($this->uriVars['pageid']); break;
			case "edit" 	: return $this->pageEdit($this->uriVars['pageid']); break;
			case "delete" 	: return $this->Pages->delNode($this->uriVars['pageid']); break;
			case 'postdata'	: return $this->pageShow($this->Edit->PostData()); break;
			case 'newpage'	: return $this->Edit->newPage($name, $rozdil); break;
			case 'blocklist': return $this->blockList($this->uriVars['pageid']); break;
			case 'editblock': echo $this->blockEdit($this->uriVars['pageid'], $this->uriVars['blockid']); // json format
		}
	}
	function menu($params){
		parse_str($params, $p_arr);
		$page = $p_arr['page'] ? $p_arr['page'] : $this->$this->uriVars['pageid'];
		$xml='<menu page="'.$p_arr['page'].'">'.$p_arr['name'].'</menu>';
		return $xml;
	}

    function pageShow($id){
        if(!$id){print "id undefined [$id]"; return;}
        return $this->pageQuery(array('where' => " where pg.Id = $id "));
    }

	function pageList($node){
        $node = $node ? $node : 0;
        return $this->pageQuery(
            array(
                 'where'        => " where pg.Rozdil = $node ",
                 'attributes'   => ' mode="list" node="'.$node.'" '
            )
        );
    }

	function pageQuery($params){
		$where = $params['where'];
		$q = "
			select pg.Name, pg.Id, Title, Source, count(chl.Id) as Children
			from Pages pg
				left join PageData pd on pg.Id = pd.PageId and Lang = '".$this->CFG->lang."'
				left join Pages chl on chl.Rozdil = pg.Id
			$where
			group by pg.Id
		";
		$pgl = $this->CFG->DB->q($q);
		//dbg($q);
		while($p = mysql_fetch_object($pgl)){
			$xml.='
				<page id="'.$p->Id.'" children="'.$p->Children.'">
					<name>'.$p->Name.'</name>
					<title><![CDATA['.$p->Title.']]></title>
					'.$this->Pages->langList($p->Id).'
				</page>
			';
		}
		$xml='<pagelist '.$params['attributes'].'>'.$xml.'</pagelist>';
		//dbg($p);
		return $xml;
	}



	public function pageEdit($pageid){
		if(!$pageid)return false;
		$q = "select * from Pages left join PageData on PageId = Id and Lang = '".$this->CFG->lang."' where Id = $pageid";
		$pg = $this->CFG->DB->q($q);
		//dbg($q);
		$pages = new Pages();
		if($p = mysql_fetch_object($pg)){
			$xml = '
				<page-edit id="'.$pageid.'">
					<uri>'.$pages->getURI($pageid).'</uri>
					<name>'.$p->Name.'</name>
					<title><![CDATA['.$p->Title.']]></title>
					<seo-title><![CDATA['.$p->SeoTitle.']]></seo-title>
					<params><![CDATA['.$p->Params.']]></params>
					<source><![CDATA['.$p->Source.']]></source>
					<alias><![CDATA['.$p->AliasOf.']]></alias>
					<params>'.$p->Params.'</params>
					<template>'.$p->Template.'</template>
					'.$this->Pages->langList($pageid).'
				</page-edit>
			';
			// dbg($xml);
			return $xml;
		}
	}



	function blockList($id){
		if(!$id)return false;
		$blk = $this->CFG->DB->q("select * from Blocks where DocId = $id");
		while($b = mysql_fetch_object($blk)){
			$xml.='
				<block id="'.$b->Id.'" pageid="'.$b->DocId.'">
					<name>'.$b->Name.'</name>
					<place>'.$b->Place.'</place>
					<params>'.$b->Parameters.'</params>
				</block>
			';
		}
		return '<block-list pageid="'.$id.'">'.$xml.'</block-list>';
	}

	function blockEdit($pageid, $blockid){
		$db = $this->CFG->DB;
		$pv = $this->CFG->PostVars;
		$name	= $pv['name'];
		$place	= $pv['place'];
		if(!$pageid || $blockid===NULL)return '{"result":"error", "descr":"Incorrect conditions"}';
		if(!$name)return '{"result":"error", "descr":"Invalid block name"}';
		if($blockid === '0'){
			$db->q("insert into Blocks set DocId = $pageid, Name = '$name', Place = '$place'");
			$id = $db->insId();
			return '{"id":'.$id.', "name":"'.$name.'", "place":"'.$place.'", "result": "ok", "descr": "Block ['.$id.'] created for Page ['.$pageid.']"}';
		}else{
			print "$blockid != 0";
			if(!mysql_fetch_object($db->q("select * from Blocks where Id = $blockid and DocId = $pageid")))return '{"result":"error", "descr":"No page-block link ['.$pageid.', '.$blockid.']"}';
			$db->q("update table Blocks set Name = '$name', Place = '$place' where DocId = $pageid");
			return '{"id":'.$blockid.', "result": "ok", "descr": "Block ['.$blockid.'] updated"}';
		}
	}

}

?>