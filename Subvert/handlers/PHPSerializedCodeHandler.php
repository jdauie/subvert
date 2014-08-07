<?php

namespace Jacere;

require_once(__dir__.'/../BaseCodeHandler.php');

class PHPSerializedCodeHandler extends BaseCodeHandler {
	
	public function GetPatterns() {
		return [
			// this doesn't work because strings are not escaped
			/*new DelimitedCodePattern(
				SyntaxHighlighter::REGEX_STRING_DOUBLE,
				'php-str'
			),*/
			new BasicCodePattern(
				//'/[sibaON](?=[:;])/s',
				'/[sibaON][:](\d+(:|(?=;)))*/s',
				'keyword'
			),
		];
	}
}

?>