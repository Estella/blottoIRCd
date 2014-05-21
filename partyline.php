<?php

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
	unset($args[0]);
	foreach ($args as $arg) {
		$ret[] = $arg;
	}
	if ($line[$serv-1] != "") {
		$ret[] = $line[$serv-1];
	}
	return $ret;
}

$sock = stream_socket_server("tcp://127.0.0.1:9991");
//error_reporting(0);
global $le, $conf, $cliptr;
$conf = new stdClass();
require_once("./ircd.conf");
require_once("./class.php");
$cliptr = new stdClass();
$socks = array($sock);
while (true) {
	$r = $socks;
	stream_select($r,$w = NULL,$e = NULL,$t = NULL, $ut = NULL);

	if ($r) {
		foreach ($r as $collar => $dog) {
			if ($dog == $sock) {
				$socks[] = $newsock = stream_socket_accept($sock);
				$cliptr->clients[$conf->me["numeric"]][(int)$newsock] = new cliClass($conf->me["numeric"],$newsock,stream_socket_get_name($newsock,1),$newsock);
				continue;
			}
			if (feof($dog)) unset($le[$dog]);
			$get = fgets($dog,1026);
			if ($get === FALSE) {
				unset($socks[$collar]);
			}
			$get = trim($get);
			$line = protocolParse($get);
			call_user_func(($le[$dog]["type"] == "server")?array($conf->proto,$line[0]):array($conf->cliproto,$line[0]),$dog,$line);
		}
	}
}
