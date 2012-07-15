<?

// XML to HTML
class xmlOut{
	var $Source;
	var $Template;
	var $TempFile;
	var $Width;
	var $Heigth;
	var $CFG;
	
	function xmlOut(){
		global $caCFG;
		$this->CFG = $caCFG;
		//$this->TempFile="temp1.xslt";
		$this->Source=null;
		$this->Width="100%";
		$this->Height=20;
		$this->rsi = new rsi();
		//$this->setParams();
	}

	function setParams(){
		$fname=$this->CFG->TempDir.$this->TempFile;
		//print "[$fname]";
		if(file_exists($fname))$this->Template=file_get_contents($fname);
		else print "<p><strong>CallAct Error</strong> opening template file [$fname]<br>Path:[$fname]</p>";
	}
	
	function Show($debug=false){
		$this->rsi->start();
		//$debug = 1;
		if($debug)print '<textarea style="width:500px;height:300px">'.$this->Source.'</textarea><textarea style="width:500px;height:300px">'.$this->Template.'</textarea>';
		if(!$this->Template)return "<p>Template not declared</p>";
		if(!$this->Source)return "<p>XML not found</p>";
		$domxml = new DOMDocument();
		$domxml->loadXML($this->Source);
		$this->Template;
		$domxsl = new DOMDocument();
		$domxsl->loadXML($this->Template);
		$xsl = new XSLTProcessor();
		$xsl->importStylesheet($domxsl);
		$res = $xsl->transformtoXML($domxml); 
		$result = $this->deCDATA($res);
		$this->rsi->msg("xslt: ".$this->TempFile);
		return $result;
	}
	
	function deCDATA($document){
		$replace = array (
						  "&",
						  "<",
						  ">"
		);
		$search = array (
						 "'&(amp|#38);'i",
						 "'&(lt|#60);'i",
						 "'&(gt|#62);'i"
		);
		$text = preg_replace ($search, $replace, $document);
		return $text;
	}
	
	function aClean($str){
		$result=preg_replace("/&rsquo;/","'",$str);
		return $result;
	}
	
	function qShow($xml, $temp, $debug=false){
		//print "[$temp]";
		$this->TempFile=$temp;
		$this->Source=$xml;
		//if(!preg_match("/".$this->xmlHeader."/", $xml))$xml = $this->xmlHeader.$xml;
		$this->setParams();
		$html = $this->Show($debug);
		return $html;//."<textarea>$xml</textarea>";
	}

	function xml2array($xmlstring){
		$xml = simplexml_load_string($xmlstring);
		$json = json_encode($xml);
		$array = json_decode($json,TRUE);
		return $array;
	}

	function array2xml(){

	}

	function obj2xml($obj, $root_node="root"){
		$converter = new Obj2xml($root_node);
		return $converter->toXml($obj);
	}

}


class Obj2xml {
    var $xmlResult;
    function __construct($rootNode){
        $this->xmlResult = new SimpleXMLElement("<$rootNode></$rootNode>");
    }
        
    private function iteratechildren($object,$xml){
        foreach ($object as $name=>$value) {
            if (is_string($value) || is_numeric($value)) {
                $xml->$name=$value;
            } else {
                $xml->$name=null;
                $this->iteratechildren($value,$xml->$name);
            }
        }
    }

    function toXml($object) {
        $this->iteratechildren($object,$this->xmlResult);
        return $this->xmlResult->asXML();
    }
}

?>