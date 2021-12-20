<?php
$phpAPIDOcParamRegex = '/(@api[\w]+|@api)\s+({[\w]+})?\s?([\/\w]+)?\s?(.*)?([\s\S]*?)(?=.+(\s+\*\s+@)|.\*\/|$)/';
$phpAPIDOcParamRegexes = [
	'@api' => '/^\s?\*\s?@(api)\s+{(\w+)}\s+([\/\.\w]+)\s+(.*)$/m',
	'@apiGroup' => '/^\s?\*\s?@(apiGroup)\s+([\w\\\]+)/m',
	'@apiVersion' => '/^\s?\*\s?@(apiVersion)\s+([.\d]+)/m',
	'@apiUse' => '/^\s?\*\s?@(apiUse)\s+([\w\d\\\]+)/m',
	'@apiPermission' => '/^\s?\*\s?@(apiPermission)\s+([\w\d\\\]+)/m',
	'@apiName' => '/^\s?\*\s?@(apiName)\s+([\w\d]+)/m',
	'@apiDescription' => '/^\s?\*\s?@(apiDescription)\s+([\s\S]*?)(?=^\s*\*\s?@.*?|\s*\*\/$)/m',
	//'@apiBody' => '/^\s?\*\s?@(apiBody)\s+\{(\w+)}\s+(\[?(\w+)\=?([\w\d\-\_\.]+)?\]?:?([\w\d\-\_\.]+)?)\s+([\s\S]*?)(?=^.+\s*\*\s?@.*?|.+\s*\*\/$)/m',
	'@apiBody' => '/^\s?\*\s?@(apiBody)\s+\{(\w+)}\s+(\[?([\w\-]+)\=?((\"([\w\d\s\-\_\.\+]+\@)\")|([\w\d\-\_\.\+\@]+))?\]?(:(\"([\w\d\s\-\_\.\,\+\@]+)\")|:([\w\d\-\_\.\,\+\@]+))?)\s+([\s\S]*?)(?=^.+\s*\*\s?@.*?|.+\s*\*\/$)/m',
	'@apiExample' => '/^\s?\*\s?@(apiExample)\s+\{(\w+)}\s+(.*$)?\s+([\s\S]*?)(?=^.\s*\*\s*@.*?|\s*\*\/)/m',
	'@apiDefineBlock' => '/^\s?\*\s?@(apiDefine)\s+([\s\S]*?)(?=^\s*\*\s?@apiDefine.*?|\s*\*\/$)/m',
	'@apiDefine' => '/^\s?\*\s?@(apiDefine)\s+([\w\d\\\]+)/m',
	//'@apiHeader' => '/^\s?\*\s?@(apiHeader)\s+{(\w+)}\s+(\[?([\w\d\-\_]+)\=?([^\s]+)?\]?)\s+([\s\S]*?)(?=^.+\s*\*\s?@.*?|.+\s*\*\/$)/m',
	'@apiHeader' => '/^\s?\*\s?@(apiHeader)\s+\{(\w+)}\s+(\[?([\w\-]+)\=?((\"([\w\d\s\-\_\.\+\@\/]+)\")|([\w\d\-\_\.\+\@\/]+))?\]?(:(\"([\w\d\s\-\_\.\,\+\@]+)\")|:([\w\d\-\_\.\,\+\@]+))?)\s+([\s\S]*?)(?=^.+\s*\*\s?@.*?|.+\s*\*\/$)/m',
	'@apiHeaderExample' => '/^[^\S\r\n]+\*[^\S\r\n]*@(apiHeaderExample)\s+\{(\w+)}\s+(\[?([\w\d\-\_]+)\=?([^\s]+)?\]?)\s+([\s\S]*?)(?=^.\s*\*\s*@.*?|\s*\*\/)/m',
	'@apiError' => '/^[^\S\r\n]+\*[^\S\r\n]*@(apiError)\s+(.*)([\s\S]*?)(?=^.\s*\*\s*@.*?|\s*\*\/)/m',
	'@apiSuccess' => '/^[^\S\r\n]+\*[^\S\r\n]*@(apiSuccess)\s+(.*)([\s\S]*?)(?=^.\s*\*\s*@.*?|\s*\*\/)/m',
	'@apiErrorExample' => '/^[^\S\r\n]+\*[^\S\r\n]*@(apiErrorExample)\s+(\{([\w\d\-\_]+)\})?(.*$)?\s+([\s\S]*?)(?=^.\s*\*\s*@.*?|\s*\*\/)/m',
	'@apiSuccessExample' => '/^[^\S\r\n]+\*[^\S\r\n]*@(apiSuccessExample)\s+(\{([\w\d\-\_]+)\})?(.*$)?\s+([\s\S]*?)(?=^.\s*\*\s*@.*?|\s*\*\/)/m',
	'@apiExamples' => '/^[^\S\r\n]+\*[^\S\r\n]*@(apiExamples)\s+\[([\w]+:.*;?)\]/m',

	'whitespaces' => '/((?=^)(\s*))|((\s*)(?>$))/m',
	'whitespace' => '/((?=^)( ))|(( )(?>$))/m',
	'6spaces' => '/((?=^)(      ))|((      )(?>$))/m'
];

$apiParamsWithMultipleValues = [
	'api',
	'apiBody',
	'apiExample',
	'apiHeader',
	'apiHeaderExample',
	'apiError',
	'apiSuccess',
	'apiErrorExample',
	'apiSuccessExample'
];

require_once "phar://pad.phar/inc/functions.php";
require_once "phar://pad.phar/inc/PADBase.php";

$PADBase = new PADBase();
