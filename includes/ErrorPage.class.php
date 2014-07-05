<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2014 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

class ErrorPage extends Page {
	private $error_msg;
	private $error_code;

	public function __construct($error_message, $error_code = 0) {
		$this->error_msg = $error_message;
		$this->error_code = $error_code;
		$this->id = 0;
		$this->handleErrorCode();
	}
	
	private function handleErrorCode() {
		switch ($this->error_code) {
			default:
				break;
			case 404:
				header("HTTP/1.0 404 Not Found");
				break;
		}
	}
	
	public function getContent() {
		return null;
	}
	
	public function getNotifications() {
		return $this->error_msg;
	}
}
