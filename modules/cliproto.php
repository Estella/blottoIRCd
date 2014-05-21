<?php

class cli {
	function __construct () {
		return;
	}
	function sendto_one($fd,$msg) {
		global $conf, $cliptr;
		$message = sprintf(":%s!%s@%s %s",$msg["src"]->nick,$msg["src"]->ident,($msg["src"]->cloaked)?$msg["src"]->dhost:$msg["src"]->host,$msg["text"]);
		fwrite(STDOUT,$message."\n");
		if ($fd->server == NULL) return;
		if ($fd->server != $conf->me["numeric"]) {
			$conf->proto->sts_msg(find_server($fd),$msg);
			return;
		}
		fwrite($fd->fd,$message."\r\n");
		return;
	}
	function sendto_one_local_fromserv($fd,$msg) {
		global $conf, $cliptr;
		fwrite($fd,":".$msg["src"]." ".$msg["text"]."\r\n");
		return;
	}
	function sendnum($numeric,$fd) {
		switch ($numeric) {
			case "462":
				$this->sendto_one_local_fromserv($fd,array("text"=>"462 * :You may not reregister"));
			break;
		}
	}
	function findbynick($nick) {
		global $conf, $cliptr;
		if ($cliptr->lnicks[$nick]) return $cliptr->lnicks[$nick];
		else {
			foreach ($cliptr->clients as $server => $servnicks) {
				foreach ($servnicks as $ptr => $obj) {
					if ($obj->nick == $nick) return $obj;
				}
			}
		}
	}
	function PRIVMSG($fd,$line) {
		global $conf, $cliptr;
		if ($cliptr->clients[$conf->me["numeric"]][$fd]->isRegistered == 0) {
			$this->sendto_one_local_fromserv($fd,array("src"=>$conf->me["name"],"text"=>"NOTICE * :To send messages you must register"));
			return;
		}
		if ($line[1][0] == "#") {
			return; // Currently we don't support channels :/
		}
		$this->sendto_one($this->findbynick($line[1]),array("src"=>$cliptr->clients[$conf->me["numeric"]][(int)$fd],"text"=>sprintf("PRIVMSG %s :%s",$line[1],$line[2])));
	}
	function NOTICE($fd,$line) {
		global $conf, $cliptr;
		if ($cliptr->clients[$conf->me["numeric"]][$fd]->isRegistered == 0) {
			$this->sendto_one_local_fromserv($fd,array("src"=>$conf->me["name"],"text"=>"NOTICE * :To send messages you must register"));
		}
		if ($line[1][0] == "#") {
			return; // Currently we don't support channels :/
		}
		$this->sendto_one($this->findbynick($line[1]),array("src"=>$cliptr->clients[$conf->me["numeric"]][(int)$fd],"text"=>sprintf("NOTICE %s :%s",$line[1],$line[2])));
	}
	function CAP($fd,$line) {
		global $conf, $cliptr;
		$this->sendto_one_local_fromserv($fd,array("src"=>$conf->me["name"],"text"=>"NOTICE * :This server doesn't support capability negotiation. Sorry :/"));
	}
	function USER($fd,$line) {
		global $conf, $cliptr;
		if ($cliptr->clients[$conf->me["numeric"]][$fd]->isRegistered != 0) return; // User should not issue /user by themselves
		$cliptr->clients[$conf->me["numeric"]][$fd]->ident = $line[1];
		if (($cliptr->clients[$conf->me["numeric"]][$fd]->nick != "") and ($cliptr->clients[$conf->me["numeric"]][$fd]->isRegistered == 0)) {
			$this->sendto_one_local_fromserv($fd,array("src"=>$conf->me["name"],"text"=>"001 ".$cliptr->clients[$conf->me["numeric"]][$fd]->nick." :Welcome to the Internet Relay Network"));
			$this->sendto_one_local_fromserv($fd,array("src"=>$conf->me["name"],"text"=>"002 ".$cliptr->clients[$conf->me["numeric"]][$fd]->nick." :Your host is ".$conf->me["name"]." running a PHP IRCd written by j4jackj"));
			$this->sendto_one_local_fromserv($fd,array("src"=>$conf->me["name"],"text"=>"005 ".$cliptr->clients[$conf->me["numeric"]][$fd]->nick." CLIENTVER=2.1 :are my supported features"));
			$this->sendto_one_local_fromserv($fd,array("src"=>$conf->me["name"],"text"=>"NOTICE * :Yes, this really is an IRC2.1-level server."));
			$cliptr->clients[$conf->me["numeric"]][$fd]->isRegistered = 1;
			$cliptr->lnicks[$cliptr->clients[$conf->me["numeric"]][$fd]->nick] = $cliptr->clients[$conf->me["numeric"]][$fd];
		}
	}
	function NICK($fd,$line) {
		global $conf, $cliptr;
		if (wash_nick($line[1]) == "") return; // Obviously the nick change didn't go thru.
		$cliptr->clients[$conf->me["numeric"]][$fd]->nick = wash_nick($line[1]);
		if (($cliptr->clients[$conf->me["numeric"]][$fd]->ident != "") and ($cliptr->clients[$conf->me["numeric"]][$fd]->isRegistered == 0)) {
			$this->sendto_one_local_fromserv($fd,array("src"=>$conf->me["name"],"text"=>"001 ".$cliptr->clients[$conf->me["numeric"]][$fd]->nick." :Welcome to the Internet Relay Network"));
			$this->sendto_one_local_fromserv($fd,array("src"=>$conf->me["name"],"text"=>"002 ".$cliptr->clients[$conf->me["numeric"]][$fd]->nick." :Your host is ".$conf->me["name"]." running a PHP IRCd written by j4jackj"));
			$this->sendto_one_local_fromserv($fd,array("src"=>$conf->me["name"],"text"=>"005 ".$cliptr->clients[$conf->me["numeric"]][$fd]->nick." CLIENTVER=2.1 :are my supported features"));
			$this->sendto_one_local_fromserv($fd,array("src"=>$conf->me["name"],"text"=>"NOTICE * :Yes, this really is an IRC2.1-level server."));
			$cliptr->clients[$conf->me["numeric"]][$fd]->isRegistered = 1;
			$cliptr->lnicks[$cliptr->clients[$conf->me["numeric"]][$fd]->nick] = $cliptr->clients[$conf->me["numeric"]][$fd];
		}
		//$this->sendto_neighbors($cliptr[$conf->me["numeric"]][$fd],"NICK ".wash_nick($line[1]));
	}
	function PING($fd,$line) {
		global $conf, $cliptr;
		$this->sendto_one_local_fromserv($fd,array("src"=>$conf->me["name"],"text"=>sprintf("PONG %s :%s", $line[1], $line[2])));
	}
}
