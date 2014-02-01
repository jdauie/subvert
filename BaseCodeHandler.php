<?php

namespace Jacere;

require_once(__dir__.'/CodePattern.php');

interface ICodeHandler {
	public function Handle($code);
}

class BaseCodeHandler implements ICodeHandler {
	
	private $m_patterns;
	
	public function Handle($code, $options = NULL) {
		
		if ($this->m_patterns === NULL) {
			$this->m_patterns = $this->GetPatterns();
		}
		
		$rm = new ReplacementManager2();
		$code = $rm->ProcessPatterns($this->m_patterns, $code);
		return $code;
	}
	
	public function Probe($code) {
		return true;
	}
}

?>