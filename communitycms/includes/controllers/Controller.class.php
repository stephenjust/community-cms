<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseController
 *
 * @author Stephen
 */
abstract class Controller {
	private $parameters = array();
	
	public function __construct() {
		$this->setParameterDefaults();
	}
	
	public final function setParameters($parameters) {
		$this->parameters = array_merge($this->parameters, $parameters);
	}
	
	protected final function getParameter($parameter) {
		if (!array_key_exists($parameter, $this->parameters)) {
			throw new Exception("Parameter $parameter does not exist");
		}
		return $this->parameters[$parameter];
	}
	
	abstract protected function setParameterDefaults();
	
	protected final function setParameterDefault($name, $value) {
		if (!array_key_exists($name, $this->parameters)) {
			$this->parameters[$name] = $value;
		}
	}
	
	protected final function setGetDefault($name, $value) {
		if (empty($_GET[$name])) $_GET[$name] = $value;
	}
	
	protected final function setPostDefault($name, $value) {
		if (empty($_POST[$name])) $_POST[$name] = $value;
	}
	
	abstract public function run();
}

?>
