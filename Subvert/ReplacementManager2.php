<?php

namespace Jacere;

class ReplacementManager2 {
	
	private $m_map;
	private $m_replacements;
	private $m_index;
	
	public function __construct() {
		$this->m_map = [];
		$this->m_replacements = [];
		$this->m_index = 0;
	}
	
	public function Reconstitute($text) {
		$text = strtr($text, $this->m_map);
		return $text;
	}
	
	public function AddReplacedValue($replacement) {
		if ($replacement === '') {
			return '';
		}
		if (isset($this->m_replacements[$replacement])) {
			return $this->m_replacements[$replacement];
		}
		
		$code = "\x1A".'$'.$this->m_index;
		$this->m_map[$code] = $replacement;
		$this->m_index++;
		$this->m_replacements[$replacement] = $code;
		
		return $code;
	}
	
	public function AddRegexMatchesBasic($pattern, $replacement, $text, $handlers = NULL) {
		$text = preg_replace_callback($pattern, function ($match) use ($replacement, $handlers) {
			
			$output = '';
			
			$classes = [];
			if (is_array($replacement)) {
				$i = 0;
				foreach ($replacement as $class) {
					$classes[++$i] = $class;
				}
			}
			else {
				$classes[0] = $replacement;
			}
			
			foreach ($classes as $key => $class) {
				if (!isset($match[$key])) {
					continue;
					//break;
				}
				$match_str = $match[$key];
				
				// somehow, I want this to work on sub-matches, not just the whole string
				if (is_array($handlers)) {
					// get valid handlers
					$current_handlers = [];
					foreach ($handlers as $handler_key => $handler_val) {
						if (is_int($handler_key)) {
							$current_handlers[] = $handler_val;
						}
						if (is_string($handler_key) && is_numeric($handler_key[0])) {
							if (($pos = strpos($handler_key, ':')) !== false) {
								$handler_key_index = (int)substr($handler_key, 0, $pos);
								if ($handler_key_index === $key) {
									$handler_key_str = substr($handler_key, $pos + 1);
									if (!empty($handler_key_str)) {
										$current_handlers[$handler_key_str] = $handler_val;
									}
									else {
										$current_handlers[] = $handler_val;
									}
								}
							}
						}
						else {
							$current_handlers[$handler_key] = $handler_val;
						}
					}
					$match_str = $this->AddRegexMatches($current_handlers, $match_str);
				}
				
				// wrap the replacement pattern around each line
				$element = $match_str;
				if (!empty($class)) {
					$match_lines = explode("\n", $match_str);
					$match_lines_output = [];
					foreach ($match_lines as $line) {
						$match_lines_output[] = sprintf('<code class="%s">%s</code>', $class, $line);
					}
					$element = implode("\n", $match_lines_output);
				}
				$output .= $this->AddReplacedValue($element);
			}
			return $output;
		}, $text);
		return $text;
	}
	
	public function AddRegexMatches(array $handlers, $text) {
		foreach ($handlers as $key => $handler) {
			if (is_int($key) || (!empty($key) && preg_match($key, $text))) {
				$text = $this->AddRegexMatchesBasic(
					$handler['pattern'],
					$handler['wrapper'],
					$text,
					(isset($handler['handler']) ? $handler['handler'] : NULL)
				);
			}
		}
		return $this->Reconstitute($text);
	}
}

?>