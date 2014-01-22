<?php

namespace Jacere;

require_once(__dir__.'/ReplacementManager.php');
require_once(__dir__.'/CodeFormatter.php');

class Subvert {
	
	private $m_text;
	private $m_enableCodeFormatting;
	
	//private $m_references;
	//private $m_footnotes;
	// inline images/links?
	
	private function __construct($text, $enable_code_formatting) {
		$this->m_text = $text;
		$this->m_enableCodeFormatting = $enable_code_formatting;
	}
	
	public static function Parse($text, $enable_code_formatting = false) {
		$parser = new self($text, $enable_code_formatting);
		return $parser->Evaluate();
	}
	
	private function ParseExtra($extra) {
		$attributes = [];
		$classes = [];
		if ($extra) {
			$parts = explode(' ', $extra);
			foreach ($parts as $part) {
				if ($part[0] == '.') {
					$classes[] = substr($part, 1);
				}
				else if ($part[0] == '#') {
					$attributes['id'] = substr($part, 1);
				}
			}
		}
		if (count($classes)) {
			$attributes['class'] = implode(' ', $classes);
		}
		return $attributes;
	}
	
	private function GetAttributeString($match, $attributes, $extra = NULL) {
		$attribute_pairs = ($extra && isset($match[$extra])) ? $this->ParseExtra($match[$extra]) : [];
		
		foreach ($attributes as $name => $required) {
			if ($required || strlen($attributes[$name])) {
				$attribute_pairs[$name] = $match[$name];
			}
		}
		
		$attribute_strings = [];
		foreach ($attribute_pairs as $name => $value) {
			$attribute_strings[] = sprintf(' %s="%s"', $name, $value);
		}
		return implode('', $attribute_strings);
	}
	
	private function Normalize() {
		// normalize line endings
		if (strpos($this->m_text, "\r") !== false) {
			$this->m_text = str_replace("\r\n", "\n", $this->m_text);
			$this->m_text = str_replace("\r", "\n", $this->m_text);
		}
	}
	
	private function Evaluate() {
		$this->Normalize();
		
		$text = $this->m_text;
		
		$references = [];
		$footnotes = [];
		$escape_sequence_map = [];
		
		$rm = new ReplacementManager();
		
		// encode escape sequences
		// (I tried using a ReplacementManager for this, but it was slower)
		if (strpos($text, '\\') !== false) {
			$escape_sequences = ['\\\\', '\~', '\`', '\*', '\_', '\{', '\}', '\[', '\]', '\(', '\)', '\>', '\#', '\+', '\-', '\.', '\!'];
			foreach ($escape_sequences as $index => $escape_sequence) {
				if (strpos($text, $escape_sequence) !== false) {
					$code = "\x1A".'\\'.$index;
					$text = str_replace($escape_sequence, $code, $text);
					$escape_sequence_map[$code] = $escape_sequence[1];
				}
			}
		}
		
		// extract abbreviations
		/*if (preg_match_all('/^\*?+\[(?<short>[^]]++)\]:[\t ]++(?<long>[^\n]++)$/m', $text, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				// save
				$rm->Add($match[0], '');
			}
			$text = $rm->Replace($text);
		}*/
		
		// find code blocks
		if (strpos($text, '~~~') !== false || strpos($text, '```') !== false) {
			if (preg_match_all('/^[\t ]*+\K([~`]{3})(?: ?{(?<extra>[^}]++)})?+[^\n]*+(?<code>.+?)^[\t ]*+\1/ms', $text, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $match) {
					$code = $match['code'];
					$code = str_replace("\t", '    ', $code);
					
					// adjust whitespace alignment using first line as a guide
					$code = trim($code, "\n");
					$leading_whitespace_len = strlen($code) - strlen(ltrim($code, "\t "));
					if ($leading_whitespace_len > 0) {
						$leading_whitespace = substr($code, 0, $leading_whitespace_len);
						$code_lines = explode("\n", $code);
						foreach ($code_lines as &$line) {
							if ($line !== '' && strncmp($line, $leading_whitespace, $leading_whitespace_len) !== 0) {
								$skip_whitespace_handling = true;
								break;
							}
							$line = substr($line, $leading_whitespace_len);
						}
						if (!$skip_whitespace_handling) {
							$code = implode("\n", $code_lines);
						}
					}
					
					$extra = isset($match['extra']) ? explode(' ', $match['extra']) : NULL;
					
					if ($this->m_enableCodeFormatting) {
						$element = CodeFormatter::Format($code, $extra);
					}
					else {
						$element = '<pre><code>'.$code.'</code></pre>';
					}
					$rm->Add($match[0], $element, 'block');
				}
				$text = $rm->Replace($text);
			}
		}
		
		// find inline code
		if (strpos($text, '`') !== false) {
				if (preg_match_all('/`(?<code>[^`]++)`/', $text, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $match) {
					$element = sprintf('<code>%s</code>', htmlentities($match['code'], ENT_NOQUOTES));
					$rm->Add($match[0], $element);
				}
				$text = $rm->Replace($text);
			}
		}
		
		// removable lines
		if (strpos($text, ']:') !== false) {
			
			// extract footnotes
			if (strpos($text, '[^') !== false) {
				if (preg_match_all('/^[\t ]*+\[\^(?<id>[^]]++)\]:[\t ]++(?<text>.*)$/m', $text, $matches, PREG_SET_ORDER)) {
					foreach ($matches as $match) {
						// check for duplicates?
						$footnotes[strtolower($match['id'])] = [
							'text' => $match['text']
						];
						$rm->Add($match[0], '');
					}
					$text = $rm->Replace($text);
				}
			}
			
			// extract references
			if (preg_match_all('/^[\t ]*+\[(?<id>[^]]++)\]:[\t ]++(?<url>[^\n\t ]++)[\t ]*+(?:"(?<title>[^"]*+)")?$/m', $text, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $match) {
					// check for duplicates?
					$references[strtolower($match['id'])] = [
						'url'   => $match['url'],
						'title' => isset($match['title']) ? $match['title'] : '',
					];
					$rm->Add($match[0], '');
				}
				$text = $rm->Replace($text);
			}
		}
		
		// images
		if (strpos($text, '![') !== false) {
			
			// find inline images
			if (preg_match_all('/!\[(?<alt>[^][]*+)\]\((?<src>[^) ]++)(?: "(?<title>[^"]++)")?+\)(?:{(?<extra>[^}]++)})?+/', $text, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $match) {
					$attr_str = $this->GetAttributeString($match, [
						'src' => true,
						'alt' => true,
						'title' => false,
					], 'extra');
					$element = sprintf('<img%s />', $attr_str);
					$rm->Add($match[0], $element);
				}
				$text = $rm->Replace($text);
			}
			
			// find ref images
			if (preg_match_all('/!\[(?<alt>[^][]*+)\]\[(?<id>[^]]++)\](?:{(?<extra>[^}]++)})?+/', $text, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $match) {
					$ref = $references[$match['id']];
					$match['src'] = $ref['url'];
					$match['title'] = $ref['title'];
					$attr_str = $this->GetAttributeString($match, [
						'src' => true,
						'alt' => true,
						'title' => false,
					], 'extra');
					$element = sprintf('<img%s />', $attr_str);
					$rm->Add($match[0], $element);
				}
				$text = $rm->Replace($text);
			}
		}
		
		// find inline links
		if (preg_match_all('/\[(?<text>[^][]*+)\]\((?<href>[^) ]++)(?: "(?<title>[^"]++)")?+\)/', $text, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$attr_str = $this->GetAttributeString($match, [
					'href' => true,
					'title' => false,
				]);
				$element = sprintf('<a%s>%s</a>', $attr_str, $match['text']);
				$rm->Add($match[0], $element);
			}
			$text = $rm->Replace($text);
		}
		
		// find ref links
		if (preg_match_all('/\[(?<text>[^][]*+)\] ?+\[(?<id>[^]]*+)\]/', $text, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$id = $match['id'];
				// implicit link name
				empty($id) and $id = strtolower($match['text']);
				$ref = $references[$id];
				$match['href'] = $ref['url'];
				$match['title'] = $ref['title'];
				$attr_str = $this->GetAttributeString($match, [
					'href' => true,
					'title' => false,
				]);
				$element = sprintf('<a%s>%s</a>', $attr_str, $match['text']);
				$rm->Add($match[0], $element);
			}
			$text = $rm->Replace($text);
		}
		
		// find footnote links
		if ($footnotes) {
			if (preg_match_all('/\[\^(?<id>[^]]++)\]/', $text, $matches, PREG_SET_ORDER)) {
				$i = 1;
				foreach ($matches as $match) {
					$id = $match['id'];
					if (!isset($footnotes[$id])) {
						continue;
					}
					if (!isset($footnotes[$id]['index'])) {
						$footnotes[$id]['index'] = $i;
					}
					$element = sprintf('<sup id="fnref-%1$d"><a href="#fn-%1$d" class="footnote-ref">%1$d</a></sup>', $i);
					$rm->Add($match[0], $element);
					++$i;
				}
				$text = $rm->Replace($text);
			}
		}
		
		// find horizontal rule
		if (preg_match_all('/^( ?+\*){3}$/m', $text, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$element = '<hr />';
				$rm->Add($match[0], $element);
			}
			$text = $rm->Replace($text);
		}
		
		// header underline characters?
		// =-`:'"~^_*+#<>
		// or just these for now?
		// =-*
		
		// header underlines
		if (preg_match_all('/^(?<text>[^\s].*+)\n(?<underline>[-=*]{4,})$/m', $text, $matches, PREG_SET_ORDER)) {
			// map chars to h1-6 (as available) by order of first occurrance
			// also, the algorithm should have an input specifying the header level to start at
			// (e.g. this document will be included in another document, and only h3-6 are available to avoid confusion)
			foreach ($matches as $match) {
				if (strlen($match['underline']) < strlen($match['text'])) {
					continue;
				}
				$element = sprintf('<h%1$d>%2$s</h%1$d>', 1, $match['text']);
				$rm->Add($match[0], $element, 'block');
			}
			$text = $rm->Replace($text);
		}
		
		// remove extra line terminators and whitespace on empty lines
		$text = preg_replace('/\n\s+\n/', "\n\n", $text);
		
		// parse
		$lines = explode("\n", $text);
		$elements = $this->ParseBlockElements($lines);
		$text = $this->RenderElements($elements);
		
		// restore extracted content
		$text = $rm->Reconstitute($text);
		// restore nested content
		$text = $rm->Reconstitute($text);
		
		// restore escaped characters
		$text = strtr($text, $escape_sequence_map);
		
		// append footnotes
		if (count($footnotes)) {
			$footnote_list = '';
			foreach ($footnotes as $id => $footnote) {
				if (isset($footnote['index'])) {
					$footnote_list .= sprintf('<li id="fn-%2$d"><p>%1$s<a href="#fnref-%2$d" class="footnote-backref">&#8617;</a></p></li>', $footnote['text'], $footnote['index']);
				}
			}
			
			$text .= sprintf('<div class="footnotes"><hr /><ol>%s</ol></div>', $footnote_list);
		}
		
		return $text;
	}
	
	private function ParseBlockElements($lines) {
		
		$elements = [];
		
		$strings = [];
		
		$lines_count = count($lines);
		for ($i = 0; $i < $lines_count; $i++) {
			$line = $lines[$i];
			$trim = ltrim($line);
			
			// blockquote
			if (strncmp($trim, '>>', 2) === 0) {
				$line_whitespace_len = strlen($line) - strlen($trim);
				$line_whitespace = substr($line, 0, $line_whitespace_len);
				$end = $line_whitespace.'<<';
				$end_len = $line_whitespace_len + 2;
				$lines_nested = [];
				
				// find end
				while ((++$i < $lines_count) && strncmp(($line = $lines[$i]), $end, $end_len) !== 0) {
					$lines_nested[] = $line;
				}
				
				$elements_nested = $this->ParseBlockElements($lines_nested);
				
				if (count($strings)) {
					$elements[] = $strings;
					$strings = [];
				}
				$elements[] = [
					'type' => 'blockquote',
					'elements' => $elements_nested,
				];
			}
			// list item
			else if (strlen($trim) > 2 && $trim[0] === '*' && ctype_space($trim[1])) {
				$line_whitespace_len = strlen($line) - strlen($trim);
				$line_whitespace = substr($line, 0, $line_whitespace_len);
				
				$lines_nested = [
					substr($trim, 1)
				];
				
				while ((++$i < $lines_count) && $line === '' || (strncmp(($line = $lines[$i]), $line_whitespace, $line_whitespace_len) === 0 && strlen($line) > $line_whitespace_len && ctype_space($line[$line_whitespace_len]))) {
					$lines_nested[] = $line;
				}
				--$i;
				
				$elements_nested = $this->ParseBlockElements($lines_nested);
				
				$element = [
					'type' => 'listitem',
					'elements' => $elements_nested,
				];
				
				if (count($strings)) {
					$elements[] = $strings;
					$strings = [];
				}
				
				$end = end($elements);
				if (is_array($end) && isset($end['type']) && $end['type'] === 'list') {
					$element_parent = array_pop($elements);
				}
				else {
					$element_parent = [
						'type' => 'list',
						'elements' => [],
					];
				}
				$element_parent['elements'][] = $element;
				array_push($elements, $element_parent);
			}
			// text (to be subdivided later)
			else {
				$strings[] = $line;
			}
		}
		
		if (count($strings)) {
			$elements[] = $strings;
			$strings = [];
		}
		
		return $elements;
	}
	
	private function RenderElements($elements, $wrap = true) {
		$str = '';
		
		foreach ($elements as $element) {
			if (isset($element['type'])) {
				$type = $element['type'];
				if ($type === 'list') {
					$str .= sprintf("\n<ul>\n%s\n</ul>\n", $this->RenderElements($element['elements']));
				}
				else if ($type === 'listitem') {
					$str .= sprintf("<li>%s</li>\n", $this->RenderElements($element['elements'], false));
				}
				else if ($type === 'blockquote') {
					$str .= sprintf("\n<blockquote>\n%s\n</blockquote>\n", $this->RenderElements($element['elements']));
				}
			}
			else {
				// break into paragraphs/block elements
				$groups = [];
				$lines = [];
				foreach ($element as $line) {
					if ($line === '') {
						if (count($lines)) {
							$groups[] = $lines;
							$lines = [];
						}
					}
					else {
						$trim = ltrim($line);
						if (strncmp($trim, "\x1A".'$_block_', 9) === 0) {
							if (count($lines)) {
								$groups[] = $lines;
								$lines = [];
							}
							$groups[] = $line;
						}
						else {
							$lines[] = $line;
						}
					}
				}
				
				if (count($lines)) {
					$groups[] = $lines;
					$lines = [];
				}
				
				foreach ($groups as $group) {
					if (is_string($group)) {
						$str .= sprintf("\n%s\n", $group);
					}
					else {
						$group_str = implode("\n", $group);
						if ($wrap) {
							$group_str = sprintf("\n<p>%s</p>\n", $group_str);
						}
						
						// find emphasis
						if (strpos($group_str, '*') !== false) {
							/*$group_str = preg_replace_callback('/(\*{1,2})(?<text>[^*]++)\1/', function ($matches) {
									if (isset($matches[1][1])) {
										return sprintf('<strong>%s</strong>', $matches['text']);
									}
									else {
										return sprintf('<em>%s</em>', $matches['text']);
									}
								}, $group_str);*/
							
							/*$group_str = preg_replace([
								'/(\*{2})(?<text>[^*]++)\1/',
								'/(\*{1})(?<text>[^*]++)\1/'
							], [
								'<strong>\2</strong>',
								'<em>\2</em>'
							], $group_str);*/
							
							// somehow, this is faster than preg_replace approaches
							if (preg_match_all('/(\*{1,2}+)(?<text>[^*]++)\1/', $group_str, $matches, PREG_SET_ORDER)) {
								foreach ($matches as $match) {
									if (isset($match[1][1])) {
										$element = sprintf('<strong>%s</strong>', $match['text']);
									}
									else {
										$element = sprintf('<em>%s</em>', $match['text']);
									}
									$group_str = str_replace($match[0], $element, $group_str);
								}
							}
						}
						
						$str .= $group_str;
					}
				}
			}
		}
		
		return $str;
	}
}

?>