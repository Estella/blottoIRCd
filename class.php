<?php

class cliClass {
	function __construct($numeric,$clinum,$host,$fd) {
		$this->nick = "";
		$this->ident = "";
		$this->host = $host;
		$this->dhost = "";
		$this->server = $numeric;
		$this->uplink = $fd;
		$this->hops = 0;
		$this->fd = $clinum;
		$this->isServer = NULL;
		$this->cloaked = 0;
	}

//	function makePerson($nick,$ident,$hostname)
}

function find_server($fd){
	// Solve for a server pointer.
	if ($fd->hops == 0) {
		// This should only happen if we have found that this server is local. Return what we
		// were passed.
		// Re: self parent, are we the uplink? Return what we were passed.
		return $fd;
	} else {
		// Back to go; this time we find the uplink's uplink
		return find_server($fd->uplink);
	}
}

function nicenick() {
	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789[]/~(){}-_";
	for ($i=0;$i<strlen($chars);$i++) {
		yield $chars[$i];
	}
}

function firstnice() {
	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz[]/~(){}_";
	for ($i=0;$i<strlen($chars);$i++) {
		yield $chars[$i];
	}
}

function wash_nick($nick) {
	// This function washes a nickname.
	// No nickname change is ever dropped; instead, nicknames will be washed
	// of offensive characters by only allowing non offensive characters.
	foreach (firstnice() as $ch) {
		if ($nick[0] == $ch) $isnice = 1;
	}
	if (!($isnice)) return wash_nick(substr($nick,1));
	$newnick = $nick[0];
	for ($i=1;$i<strlen($nick);$i++)
	{
		foreach (nicenick() as $ch) {
			if ($nick[$i] == $ch) {
				$newnick .= $nick[$i];
			}
		}
	}
	return $newnick;
}
