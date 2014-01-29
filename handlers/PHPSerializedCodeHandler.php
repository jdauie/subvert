<?php

namespace Jacere;

class PHPSerializedCodeHandler implements ICodeHandler {
	
	public function Handle($code) {
		$rm = new ReplacementManager2();
		
		// strings
		$code = $rm->AddRegexMatchesBasic(
			SyntaxHighlighter::REGEX_STRING_DOUBLE,
			'de-emphasize',
			$code
		);
		
		$code = $rm->AddRegexMatchesBasic(
			'/[sibaON](?=[:;])/s',
			'keyword',
			$code
		);
		
		$code = $rm->Reconstitute($code);
		
		return $code;
	}
}

?>