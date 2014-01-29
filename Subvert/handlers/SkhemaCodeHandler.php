<?php

namespace Jacere;

class SkhemaCodeHandler implements ICodeHandler {
	
	public function Handle($code) {
		$rm = new ReplacementManager();
		
		$code = $rm->AddRegexMatches([
			[
				'pattern' => '`({)(.)([^}]*+)(})`',
				'wrapper' => [
					'skh-del',
					'skh-sym',
					'skh-tok',
					'skh-del',
				],
				'handler' => [
					'3:' => [
						'pattern' => '`(:)([^[]++)(\[[^]]*+\])?`',
						'wrapper' => [
							'skh-sym',
							'keyword',
							'xml-att',
						]
					]
				]
			],
		], $code);
		
		return $code;
	}
}

?>