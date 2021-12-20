<?php
use JetBrains\PhpStorm\ArrayShape;

function print_r_pre(mixed $s) {
	echo '<pre>';
	print_r($s);
	echo '</pre>';
}

function getColoredOutput(string $str, string $type = 'i'): string {
	return match ($type) {
		'e' => "\033[31m$str \033[0m\n",
		's' => "\033[32m$str \033[0m\n",
		'w' => "\033[33m$str \033[0m\n",
		'i' => "\033[36m$str \033[0m\n",
		default => $str,
	};
}

function colorLog(string $str, string $type = 'i') {
	echo getColoredOutput($str, $type);
}

/**
 * Copy a file, or recursively copy a folder and its contents
 *
 * @param string $source Source path
 * @param string $dest Destination path
 * @param int $permissions New folder creation permissions
 *
 * @return      bool     Returns true on success, false on failure
 * @version     1.0.1
 * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
 * @author      Aidan Lister <aidan@php.net>
 */
function xcopy(string $source, string $dest, int $permissions = 0755): bool {
	$sourceHash = hashDirectory($source);
	// Check for symlinks
	if(is_link($source)) {
		return symlink(readlink($source), $dest);
	}

	// Simple copy for a file
	if(is_file($source)) {
		return copy($source, $dest);
	}

	// Make destination directory
	if(!is_dir($dest)) {
		mkdir($dest, $permissions);
	}

	// Loop through the folder
	$dir = dir($source);
	while(false !== $entry = $dir->read()) {
		// Skip pointers
		if($entry == '.' || $entry == '..') {
			continue;
		}

		// Deep copy directories
		if($sourceHash != hashDirectory($source . "/" . $entry)) {
			xcopy("$source/$entry", "$dest/$entry", $permissions);
		}
	}

	// Clean up
	$dir->close();
	return true;
}

// In case of coping a directory inside itself, there is a need to hash check the directory otherwise and infinite loop of coping is generated
function hashDirectory(string $directory): bool|string {
	if(!is_dir($directory)) {
		return false;
	}

	$files = array();
	$dir = dir($directory);

	while(false !== ($file = $dir->read())) {
		if($file != '.' and $file != '..') {
			if(is_dir($directory . '/' . $file)) {
				$files[] = hashDirectory($directory . '/' . $file);
			} else {
				$files[] = md5_file($directory . '/' . $file);
			}
		}
	}

	$dir->close();

	return md5(implode('', $files));
}

function explode_md(string $s, string $delimiter, bool $lastItemAsString = true): array {
	$arr = explode($delimiter, $s);
	$result = array();
	$temp =& $result;
	$count = count($arr);
	foreach($arr as $key => $val) {
		if($lastItemAsString) {
			if($key < $count - 1)
				$temp =& $temp[$val];
			else
				$temp = $val;
		} else {
			if($key < $count - 1)
				$temp =& $temp[$val];
			else {
				$temp =& $temp[$val];
				$temp = [];
			}
		}
	}

	return $result;
}

function implode_md(string $startSeparator, string $endSeparator, array $array, bool $lastItemAsString = true): string {
	$result = '';

	foreach($array as $k => $item) {
		$result .= "$startSeparator$k$endSeparator";
		$result .= (is_array($item) && !is_string($item)) ? implode_md($startSeparator, $endSeparator, $item) : "$startSeparator$k$endSeparator";
	}

	return $result;
}

function array_unique_recursive(array $arr, int $length = 0): array {
	if(array_keys($arr) === range(0, count($arr) - 2)) {
		$arr = array_unique($arr, SORT_REGULAR);
	}

	foreach($arr as $key => $item) {
		if(is_array($item) && count($item) > 10) {
			$arr[$key] = array_unique_recursive($item);
		}
	}

	return $arr;
}

function array_search_path(array $array, string|int $searchKey = '', bool $asArray = true, bool $asArrayChain = true, bool $lastItemAsString = false): bool|array {
	//create a recursive iterator to loop over the array recursively
	$iter = new RecursiveIteratorIterator(
		new RecursiveArrayIterator($array),
		RecursiveIteratorIterator::SELF_FIRST);

	//loop over the iterator
	foreach($iter as $key => $value) {
		//if the key matches our search
		if($key === $searchKey || $value === $searchKey) {
			//add the current key
			$keys = array($key);
			//loop up the recursive chain
			for($i = $iter->getDepth() - 1; $i >= 0; $i--) {
				//add each parent key
				array_unshift($keys, $iter->getSubIterator($i)->key());
			}
			//return our output array
			if(!$asArray)
				return array('path' => implode('.', $keys), 'value' => $value);
			elseif(!$asArrayChain)
				return array('path' => $keys, 'value' => $value);
			elseif($lastItemAsString)
				return array('path' => explode_md(implode('.', $keys) . ".$value", '.'), 'value' => $value);
			else
				return array('path' => explode_md(implode('.', $keys) . ".$value", '.', false), 'value' => $value);
		}
	}
	//return false if not found
	return false;
}



#[ArrayShape(['baseDir' => "mixed|string", 'input' => "mixed|string", 'output' => "mixed|string", 'filters' => "array|mixed"])]
function getArguments(): array {
	$args = getopt('i:o:f:c:ch', ['input:', 'output:', 'filters:', 'config:', 'chained']);
	$result = [
		'baseDir' => '',
		'input' => '',
		'output' => '',
		'filters' => [
			'excludes' => [
				'extensions' => [],
			],
			'includes' => [
				'extensions' => ['php'],
			],
		],
		'config' => []
	];
	foreach($args as $arg => $value) {
		switch($arg) {
			case 'i':
			case 'input':
				if(empty($value)) {
					colorLog("Missing input directory" . PHP_EOL, 'e');
					exit(1);
				}
				$v = rtrim($value, ' \t\n\r\0\x0B\\/');
				$v = realpath($v) . DIRECTORY_SEPARATOR;
				$result['baseDir'] = $v;
				$result['input'] = $v;
				break;
			case 'o':
			case 'output':
				if(empty($value)) {
					$v = "{$result['baseDir']}padocs";
					colorLog("Missing output directory. Setting output directory to $v" . PHP_EOL, 'w');
				}
				$v = rtrim($value, ' \t\n\r\0\x0B\\/');
				if(!file_exists($v)) {
					colorLog("Creating output directory: $v" . PHP_EOL);
					mkdir($v, 0777, true);
				}
				$result['output'] = $v . DIRECTORY_SEPARATOR;
				break;
			case 'f':
			case 'filters':
				if(empty($value)) {
					colorLog("No filters applied. Scanning for all files." . PHP_EOL);
				}
				$v = $value;
				$result['filters'] = $v;
				break;
			case 'c':
			case 'config':
				if(empty($value) || !file_exists($value)) {
					colorLog("No configuration specified. Skipping." . PHP_EOL,'w');
				} else {
					$v = json_decode(file_get_contents($value));
					$result['config'] = $v;
				}
				break;
		}
	}

	if(!in_array('f', $args) && !in_array('filters', $args))
		colorLog("No filters specified. Applying default filters, scanning for files only with *.php extension." . PHP_EOL);

	return $result;
}

function error_handler(int $errno, string $errstr, string $errfile, int $errline, array $context = null) {
	if(!(error_reporting() & $errno)) {
		return false;
	}
	//$errstr = htmlspecialchars($errstr);
	$errfile = str_replace('\\', '/', $errfile) . ":$errline";
	switch($errno) {
		case E_PARSE || E_STRICT || E_ERROR || E_USER_ERROR:
			colorLog("PHP Error: $errstr in file:///$errfile\n", 'e');
			exit(1);

		case E_WARNING || E_USER_WARNING:
			colorLog("PHP Warning: $errstr in file:///$errfile\n", 'w');
			break;

		case E_NOTICE || E_USER_NOTICE:
			colorLog("PHP Notice: [$errno] $errstr in file:///$errfile\n", 'i');
			break;

		default:
			echo "Unknown error type: [$errno] $errstr in file:///$errfile\n";
			break;
	}

	/* Don't execute PHP internal error handler */
	return true;
}

set_error_handler('error_handler', E_ALL);

