<?php

namespace Jacere\Subvert;

class CodeFormatter {
	
	public static function Format($code, $handlers) {
		
		$showGutter = false;
		
		$code = htmlentities($code, ENT_NOQUOTES);
		//$code = str_replace("\t", '&#9;', $code);
		//$code = str_replace(" ", '&nbsp;', $code);
		
		if ($handlers) {
			$code = SyntaxHighlighter::Execute($code, $handlers);
		}
		
		$code_lines = explode("\n", $code);
		$code_line_numbers = [];
		foreach ($code_lines as $i => &$line) {
			if (empty($line)) {
				$line = '&#8203;';
			}
			//$line = sprintf('<div>%1$s</div>', $line);
			$line = sprintf('<div><code>%1$s</code></div>', $line);
			//$line = sprintf('%1$s', $line);
			$code_line_numbers[] = sprintf('<div>%1$s</div>', ($i + 1));
		}
		$code = implode('', $code_lines);
		//$code = implode("\n", $code_lines);
		
		$gutter = '';
		if ($showGutter) {
			$code_line_numbers_str = implode('', $code_line_numbers);
			$placeholder = str_repeat('&nbsp;', strlen((string)count($code_lines)));
			$gutter = <<<EOS
			<td><div class="gutter">{$code_line_numbers_str}</div><div class="gutter-placeholder">{$placeholder}</div></td>
EOS;
		}

		$code = <<<EOS
		<pre><code><table>
			<tr>
				{$gutter}
				<td><div class="code">{$code}</div></td>
			</tr>
		</table></code></pre>
EOS;
	
	return $code;
	}
	
	/*public static function Format($code, $Handlers) {
		
		$code = htmlentities($code, ENT_NOQUOTES);
		
		if ($Handlers) {
			$code = SyntaxHighlighter::Execute($code, $Handlers);
		}
		
		$code_lines = explode("\n", $code);
		$code_line_numbers = [];
		foreach ($code_lines as $i => &$line) {
			if (empty($line)) {
				$line = '&nbsp;';
			}
			$line = sprintf('<div>%1$s</div>', $line);
			$code_line_numbers[] = sprintf('<div>%1$s</div>', ($i + 1));
		}
		$code = implode('', $code_lines);
		
		$gutter = '';
		if (true) {
			$code_line_numbers_str = implode('', $code_line_numbers);
			$placeholder = str_repeat('&nbsp;', strlen((string)count($code_lines)));
			$gutter = <<<EOS
			<td><div class="gutter">{$code_line_numbers_str}</div><div class="gutter-placeholder">{$placeholder}</div></td>
EOS;
		}

		$code = <<<EOS
		<pre><code><table>
			<tr>
				{$gutter}
				<td><div class="code">{$code}</div></td>
			</tr>
		</table></code></pre>
EOS;
	
	return $code;
	}*/
}

?>