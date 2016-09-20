<?php
/**
 * ${dec}
 *
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 3
 * @author  QTIƎE <Qti3eQti3e@Gmail.com>
 */

namespace core\ws;


class user {
	public $socket;
	public $id;
	public $headers = array();
	public $handshake = false;

	public $handlingPartialPacket = false;
	public $partialBuffer = "";

	public $sendingContinuous = false;
	public $partialMessage = "";

	public $hasSentClose = false;

	function __construct($id, $socket) {
		$this->id = $id;
		$this->socket = $socket;
	}
}