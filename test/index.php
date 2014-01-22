<?php

namespace Jacere;

define('APP_PATH', __dir__);
define('LIB_TEST_PATH', APP_PATH.'/../test/lib');
define('LIB_SUBVERT_PATH', APP_PATH.'/..');
define('LIB_SKHEMA_PATH', LIB_SUBVERT_PATH.'/../skhema');
define('LIB_BRAMBLE_PATH', LIB_SUBVERT_PATH.'/../bramble');

define('TEST_DISPLAY', false);
define('TEST_ITERATIONS', 1);

require_once(LIB_SKHEMA_PATH.'/Stopwatch.php');

$sw = Stopwatch::StartNew(sprintf('Subvert: %d iteration(s)', TEST_ITERATIONS));

require_once(LIB_SUBVERT_PATH.'/Subvert.php');
require_once(LIB_SKHEMA_PATH.'/Template.php');
require_once(LIB_TEST_PATH.'/Parsedown/Parsedown.php');
require_once(LIB_TEST_PATH.'/PHPMarkdownLib/Michelf/Markdown.php');
require_once(LIB_TEST_PATH.'/PHPMarkdownLib/Michelf/MarkdownExtra.php');

function GetTestFiles($file_patterns) {
	$file_groups = [];
	foreach ($file_patterns as $key => $pattern) {
		$file_groups[$key] = glob($pattern);
	}
	
	foreach ($file_groups as $key => &$files) {
		if (count($files) > 1) {
			$contents = [];
			$hash = md5($file_patterns['subvert']);
			$tmp = APP_PATH.'/.tmp.'.$hash;
			foreach ($files as $file) {
				$title = pathinfo($file, PATHINFO_FILENAME);
				$contents[$file] = $title."\n".str_repeat('=', strlen($title))."\n".file_get_contents($file);
			}
			file_put_contents('.tmp.'.$hash, implode("\n\n", $contents));
			$files = [$tmp];
		}
	}
	return $file_groups;
}

$testIterations = TEST_ITERATIONS;
$results = [];

$tests = [
	'php-markdown-readme' => [
		'subvert' => './test1.sv',
		'markdown' => './test1.md'
	],
	'markdown-syntax' => [
		'subvert' => './test2.sv',
		'markdown' => './test2.md'
	],
	'jacere' => [
		'subvert' => LIB_BRAMBLE_PATH.'/_initdb/posts/*.md',
		'markdown' => './test3.md'
	]
];

$test_files_groups = [];
foreach ($tests as $name => $patterns) {
	$test_files_groups[$name] = GetTestFiles($patterns);
}

$sw->Save("~");

$enabled = [
	//'subvert2' => 'ReadSubvert2',
	'subvert' => 'ReadSubvert',
	//'subvert-ash' => [
	//	'func' => 'ReadSubvert',
	//	'args' => [['code_formatting' => true]],
	//],
	'parsedown' => 'ReadParsedown',
	//'php-markdown' => 'ReadMarkdown',
	//'php-markdown-extra' => 'ReadMarkdownExtra',
];

foreach ($test_files_groups as $test_name => $test_files) {
	foreach ($enabled as $key => $func) {
		$args = [];
		if (is_array($func)) {
			$args = $func['args'];
			$func = $func['func'];
		}
		$test_files_current = $test_files['subvert'];
		if (strncmp($key, 'subvert', strlen('subvert')) !== 0 && isset($test_files['markdown'])) {
			$test_files_current = $test_files['markdown'];
		}
		
		for ($i = 0; $i < $testIterations; $i++) {
			foreach ($test_files_current as $testFile) {
				$output = call_user_func_array(sprintf('%s\%s', __NAMESPACE__, $func), array_merge([$testFile], $args));
				if (!isset($results[$testFile])) {
					$results[$testFile] = [];
				}
				$results[$testFile][$key] = $output;
			}
		}
		$sw->Save("[$test_name] $key");
	}
	
	if (TEST_DISPLAY) {
		foreach ($results as $file => $output) {
			foreach ($output as $key => $value) {
				echo '<h1>['.$key.'] '.basename($file, '.md').'</h1>';
				echo $value;
			}
		}
	}
}

$sw->Stop();
echo $sw;

function ReadMarkdown($file) {
	$text = file_get_contents($file);
	return \Michelf\Markdown::defaultTransform($text);
}

function ReadMarkdownExtra($file) {
	$text = file_get_contents($file);
	return \Michelf\MarkdownExtra::defaultTransform($text);
}

function ReadParsedown($file) {
	$text = file_get_contents($file);
	$parser = \Parsedown::instance();
	return $parser->parse($text);
}

function ReadSubvert($file, array $options = NULL) {
	$text = file_get_contents($file);
	return Subvert::Parse($text, $options);
}

function ReadSubvert2($file, array $options = NULL) {
	$text = file_get_contents($file);
	return Subvert2::Parse($text, $options);
}

?>