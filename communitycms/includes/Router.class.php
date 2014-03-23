<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2014 Stephen Just
 * @author stephenjust@gmail.com
 * @package CommunityCMS.main
 */

require_once(ROOT.'includes/HTTPErrors.class.php');
require_once(ROOT.'includes/controllers/Controller.class.php');

/**
 * Description of Router
 *
 * @author Stephen
 */
class Router {
	private $rules = array();
	private $url = '/';
	
	public function addRule($url_pattern, Controller $controller) {
		$this->rules[] = array($url_pattern, $controller);
	}
	
	public function process() {
		if (!empty($_GET['url'])) $this->url = $_GET['url'];
		
		$this->urlMatcher($this->url);
	}
	
	private function urlMatcher($url) {
		$trimmed_url = trim($url, " /\t");
		$url_tokens = explode("/", $trimmed_url);
		
		// Check url against each rule
		foreach ($this->rules AS $rule) {
			$controller = $rule[1];
			$trimmed_rule = trim($rule[0], " /\t");
			$rule_tokens = explode("/", $trimmed_rule);
			if (count($url_tokens) !== count($rule_tokens)) continue;
			
			// Parse each token
			$parameters = array();
			$fail = false;
			foreach (array_combine($rule_tokens, $url_tokens) AS $rule => $url) {
				if ($rule === $url) { continue;}
				if (strlen($rule) === 0 && $rule !== $url) {$fail = true; break;}
				if (strlen($rule) > 0 && $rule[0] !== "?" && $rule !== $url) {$fail = true; break; }
				
				// Do matching
				$matches = array();
				preg_match("~^\\?\\(<([a-z\\-]+)>(.*)\\)$~i", $rule, $matches);
				if (count($matches) === 0) throw new Exception("Invalid routing rule $rule!");
				$param_name = $matches[1];
				$match_regex = $matches[2];
				
				$url_matches = array();
				if (preg_match("~^{$match_regex}$~i", $url, $url_matches)) {
					$parameters[$param_name] = $url_matches[0];
				} else {
					$fail = true;
					break;
				}
			}
			if (!$fail) {
				$controller->setParameters($parameters);
				$controller->run();
				return;
			}
		}
		// No successful match - that's a 404 error
		HTTPErrors::throw404();
	}
}

?>
