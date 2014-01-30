<?php

namespace Jacere;

class PHPSerializedCodeHandler extends BaseCodeHandler {
	
	public function GetReplacements() {
		return [
			[
				'pattern' => SyntaxHighlighter::REGEX_STRING_DOUBLE,
				'wrapper' => 'de-emphasize',
			],
			[
				'pattern' => '/[sibaON](?=[:;])/s',
				'wrapper' => 'keyword',
			],
		];
	}
}

?>