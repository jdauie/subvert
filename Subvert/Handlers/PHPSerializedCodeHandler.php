<?php

namespace Jacere\Subvert\Handlers;

use Jacere\Subvert\BaseCodeHandler;
use Jacere\Subvert\BasicCodePattern;

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