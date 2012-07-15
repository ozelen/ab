<?php

/**
* @author Sanches Breathless
* @copyright 2008
* @RSi php5
*/

class rsi
{
public $RSi;
public $Time;
public $lg;
private $S;
private $E;
private $Size;


function rsi(){
	global $DebugText;
	$this->lg = &$DebugText; //array();
}

private function getmicrotime()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

public function stop()
{
	$this->Size = ob_get_length();
	//ob_flush();
	$this->E = $this->getmicrotime(1);
	$this->RSi = round(($this->Size /($this->E - $this->S))/1024,2);
	$this->Time = round($this->E - $this->S,3);
}

public function start()
{
	$this->S = $this->getmicrotime(1);
	//ob_start();
}

public function msg($content, $header=""){
	global $DebugText;
	$this->stop();
	$ind = count($this->lg)+1;
	if($header == "textarea")$txt='<textarea style="width:500px; height:300px">'.$content.'</textarea>';
	else $txt = $header." * [$ind] [".$content."] Time: ".$this->Time." sec.; Size: ".$this->Size."; Memory: ".round(memory_get_usage(true)/1024/1024,4)." mb";
	//print "<!-- [$ind] [$header] [$content] -->\n";
	//print "<p> $txt </p>\n";
	
	$this->lg[$ind] = $txt;
}

public function showLog(){
	//print "<fieldset><legend>Log:</legend>".join("<br/>", $this->lg)."</fieldset>";
	dbg("LOG:\n".join("\n", $this->lg));
}

};

?>