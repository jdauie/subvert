<?php

namespace Jacere;

class XMLCodeHandler implements ICodeHandler {
	
	public function Handle($code) {
		$rm = new ReplacementManager();
		
		$code = $rm->AddRegexMatches([
			[
				'pattern' => '`(&lt;/?)([^&\s!][^&\s]*+)(.*?)(&gt;)`',
				'wrapper' => [
					NULL,
					'xml-tag',
					NULL,
					NULL,
				],
				'handler' => [
					'3:' => [
						'pattern' => '/(\s++)([^=]++)(=)(\s*"[^"]*+")/',
						'wrapper' => [
							NULL,
							'xml-att',
							NULL,
							'xml-val',
						]
					]
				]
			],
		], $code);
		
		return $code;
	}
}

?>