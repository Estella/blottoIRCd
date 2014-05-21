<?php

global $sock, $conf, $p;
$sock = array();
$p = new stdClass();
$conf = new stdClass();

require_once("./send.php");
require_once("./conf.php");

function protocolParse($line) {
	$lina = " ";
	$lina .= $line;
	if ($lina[1] == ":") $serv = 3;
	else $serv = 2;
	$line = explode(" :",$lina,$serv);
	$args = explode(" ",$line[$serv-2]);
	if ($serv == 3) {
		$ret["src"] = $args[0];
	}
	foreach ($args as $arg) {
		if ($arg == $ret["src"]) continue;
		$ret[] = $arg;
	}
	if ($line[$serv-1] != "") {
		$ret[] = $line[$serv-1];
	}
	return $ret;
}

function checksock($type) {
	global $sock;
	// This function selects all the streams inside $sock[$type]
	// for activity within the next 10 ms so as to create the illusion
	// that the IRCd isn't blocking :P
	// and returns those which are active.
	$socks = $sock[$type];
	$w = $e = NULL;
	stream_select($socks, $w, $e, 0, 10000);
	return $socks;
}

function addsock($type,$s) {
	global $sock;
	$sock[$type][] = $s;
}

function delsock($type,$k) {
	global $sock;
	unset($sock[$type][$k]);
}

function byebye($reason) {
	sendto_all_local($p->me->sname, "NOTICE", sprintf(" * :*** Notice -- Server is going down. %s",$reason));
	//if ($p->linkable == true) sendto_snomask_all("R","I am being shot by my administrator: {$reason}")
	die("/DIE called because: {$reason}");
}
