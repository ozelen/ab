<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Виктория
 * Date: 24.10.11
 * Time: 15:33
 * To change this template use File | Settings | File Templates.
 */


class Obj extends Modules{
	public $name;
	public $id;
	public $fields;
	public $table;
	public $params;
	function __construct(){
		$this->inc();
	}
	function setTable($table){$this->table = $table;}
	function postData(){

	}

	function query($params){
		if(is_array($params)){}
		$table  = $params['table']  ? $params['table']  : $this->table;
		$fields = $params['fields'] ? $params['fields'] : ' * ';
		$where  = $params['where']  ? $params['where']  : '';
		$order  = $params['order']  ? $params['order']  : '';
		$group  = $params['group']  ? $params['group']  : '';
		$limit  = $params['limit']  ? $params['limit']  : '';
		$q = "select $fields from $table $where $group $order $limit ";
//      dbg($q);
		return $this->CFG->DB->q($q);
	}

    function xmlList($sqlResult){
        $xml=null;
        while($obj = mysql_fetch_array($sqlResult)){
            //if($this->table == 'orgcom')$obj['xmldata'] = Geo::company($obj['id']);
            $obj['logo'] = Geo::logo($obj['logo']);
            $xml.=$this->fetchObj($obj);
        }
        $xml = '<objlist type="'.$this->table.'">'.$xml.'</objlist>';
        return $xml;
    }

    function showItem($params){
        $obj = mysql_fetch_array($this->query($params));
        $pages = new Pages();
        //dbg($obj['PageId']);
        $page_data = $pages->getPage($obj['PageId'], $this->CFG->lang, '');
        $xml = '
            <showItem type="'.$this->table.'" id="'.$obj['id'].'">
                '.$this->fetchObj($obj).'
                '.$page_data.'
            </showItem>
        ';
        return $xml;
    }

	function getList($params=null){
        $xml = $this->xmlList($this->query($params));
		//dbg($xml);
		return $xml;
	}

	function showList($params){
		//if(params[])
		switch($this->params['format']){
			case 'json': print $this->jsonSearch(); break;
			case 'xml': break;
			default: return $this->getList(); break;
		}
	}
	
	function fetchObj($arr){
		$xml = null;
		foreach($arr as $key=>$val){
            if(is_numeric($key) || $val === null || $val == '')continue;
            if($key == 'xmldata') $xml.= '<data>'.$val.'</data>';
            else{
                if(!is_numeric($val) && $key!='data')
                    $xml.= '<field id="'.$key.'"><![CDATA['.$val.']]></field>';
                else
                    $xml.= '<field id="'.$key.'">'.$val.'</field>';
            }

		}
		return '<obj type="'.$this->table.'">'.$xml.'</obj>';
	}

	function Exec($name, $params, $templ=NULL){
		parse_str($params, $this->params);
		switch($name){
			case "list" 	: return $this->showList($params); break;
			case "show" 	: return $this->showItem($params); break;
            case "addpage"  : print $this->addPage(); break;
		}
	}

    function addPage(){
        //print "add!!!";
        $pages = new Pages();
        $db = $this->CFG->DB;
        $uname = $this->uriVars['companyname'];
        if($id = $pages->addPage($uname, 150)){
            $title = $db->getFieldWhere('orgcom', 'name', "where uname = '$uname'");
            $pages->addData($id, $title, '', $this->CFG->lang);
            $db->q("update orgcom set PageId = $id where uname = '$uname'");
            return $id;
        }
    }
}

class Companies extends Obj{
	function Companies(){
		$this->inc();
		$this->setTable("orgcom");
	}

	function getId($uname){
		$this->CFG->DB->getFieldWhere("orgcom", "id_org", " where uname = '$uname' ");
	}

	function getName($id){
		if(!$id)return;
		$q = "
			select
				c.id_org as id,
				`name` as oldname,
				pd.Title as ItemName
			from orgcom c
				left join PageData pd on c.PageId = pd.PageId
			where id_org = $id
			limit 1
		";
		$c = fq($q);
		return $c->ItemName ? $c->ItemName : $c->oldname;
	}

    function showItem(){
		//dbg("show");
        $uname = $this->uriVars['companyname'];
        $params = array(
            'where' => " where uname = '$uname' "
        );
        return parent::showItem($params);
    }

    function getList(){
        $params = array(
            'fields'=> "
                id_org as `id`,
                id_org as `logo`,
                name, uname,
                (select Title from PageData pd where pd.PageId = c.PageId and pd.Lang = '".$this->CFG->lang."') as `title`,
                (select count(*) from dump_banks b where b.company_id = c.`id_org`) as offices,
                (select count(*) from Articles rel where rel.CompanyId = c.`id_org` ) as releases
            ",
            'table' => "orgcom c",
            'order' => 'order by offices desc'
        );
        return parent::getList($params);
    }

	function jsonSearch(){
		$str = $this->CFG->PostVars['str'];
		$str_cond = $str ? " and `name` like '$str%' or pd.Title like '$str%' " : '';
		$q = "
			select
				c.id_org as id,
				`name` as oldname,
				pd.Title as ItemName
			from orgcom c
				left join PageData pd on c.PageId = pd.PageId
			where
				1=1
				$str_cond
			group by c.id_org
			limit 20
		";
		$res = $this->CFG->DB->q($q);
		$arr = array();
		while($c = mysql_fetch_array($res)){
			$arr[]=array(
				'id' => (int)$c['id'],
				'name'=>$c['ItemName'] ? $c['ItemName'] : $c['oldname']
			);
		}
		print json_encode($arr);
	}
}

class Offices extends Obj{
	function Offices(){
		$this->inc();
		$this->setTable("dump_banks");
	}
}


?>