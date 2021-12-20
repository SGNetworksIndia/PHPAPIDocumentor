<?php

class PADBase {
	private array $args;
	private stdClass $config;
	private array $bin;

	private string $input, $baseDir;
	private array $filters;

	private stdClass $apiContent;
	private array $apiGroups, $files;

	private string $sidebar, $content;

	private array $samplesLanguages = [
		'http' => 'http',
		'java' => [
			'java',
			'unirest',
		],
		'js' => [
			'jq',
			'javascript',
			'jquery',
		],
		'json' => 'json',
		'jsonp' => 'jsonp',
		'php' => [
			'php_curl',
			'php',
		],
		'xml' => 'xml',
		'bash' => [
			'curl',
			'bash',
			'sh',
			'terminal',
		],
		'batch' => [
			'batch',
			'bat',
			'cmd',
		],
	];

	public function __construct() {
		$this->args = getArguments();
		$this->config = (array_key_exists('config', $this->args)) ? $this->args['config'] : new stdClass();

		$this->input = $this->args['input'];
		$this->baseDir = $this->args['baseDir'];
		$this->filters = $this->args['filters'];
		$this->files = $this->getFiles($this->input, $this->filters);
		$this->bin = (!empty($this->config->bin)) ? $this->getBin($this->config->bin) : [];
		if($this->generatePADSpecification($this->input)) {
			$this->apiContent = $this->getAPIContent($this->baseDir);
			$this->apiGroups = $this->getAPIGroups($this->apiContent);
			$this->sidebar = $this->getSidebarHTML($this->apiGroups, false);
			$this->content = $this->getContentHTML();
		}
	}

	/**
	 * @param string $type
	 *
	 * @return string
	 */
	private function getSampleLanguage(string $type): string {
		$type = strtolower($type);
		foreach($this->samplesLanguages as $key => $value) {
			if(is_array($value) && in_array($type, $value))
				return $key;
			elseif(is_string($value) && in_array($type, $this->samplesLanguages))
				return $type;
		}

		return 'http';
	}

	private function getFiles(string $dir, array $filters = []): array {
		$result = [];
		$dir = realpath($dir);

		if(file_exists($dir) && is_readable($dir)) {
			$dirs = scandir($dir);

			unset($dirs[array_search('.', $dirs, true)]);
			unset($dirs[array_search('..', $dirs, true)]);

			$includes = (array_key_exists('includes', $filters)) ? $filters['includes'] : [];
			$excludes = (array_key_exists('excludes', $filters)) ? $filters['excludes'] : [];
			$includedExtensions = (array_key_exists('extensions', $includes)) ? $includes['extensions'] : [];
			$excludedExtensions = (array_key_exists('extensions', $excludes)) ? $excludes['extensions'] : [];

			if(count($dirs) > 0) {
				foreach($dirs as $path) {
					$filename = $dir . DIRECTORY_SEPARATOR . $path;
					if(is_dir($filename)) {
						$newResult = $this->getFiles($filename, $filters);
						$result = array_merge($result, $newResult);
						if(!in_array($filename, $newResult))
							continue;
					}
					if(is_file($filename)) {
						$ext = pathinfo($filename, PATHINFO_EXTENSION);
						if(!empty($includedExtensions) && !empty($excludedExtensions)) {
							if(in_array($ext, $includedExtensions) && !in_array($ext, $excludedExtensions)) {
								$result[] = $filename;
							}
						} elseif(!empty($includedExtensions) && empty($excludedExtensions)) {
							if(in_array($ext, $includedExtensions)) {
								$result[] = $filename;
							}
						} elseif(empty($includedExtensions) && !empty($excludedExtensions)) {
							if(!in_array($ext, $excludedExtensions)) {
								$result[] = $filename;
							}
						}
					}
				}
			}
		} else {
			colorLog("The directory \"$dir\" is not readable.", 'w');
		}

		return $result;
	}

	private function getBin(string $file): array {
		global $phpAPIDOcParamRegexes, $apiParamsWithMultipleValues;
		$file = realpath($file);

		$result = [];

		if(file_exists($file)) {
			$content = file_get_contents($file);
			if(preg_match_all($phpAPIDOcParamRegexes['@apiDefineBlock'], $content, $matches)) {
				$apiDefineBlocks = $matches[0];
				$apiDefineKeys = $matches[1];
				$apiDefineValues = $matches[2];
				foreach($apiDefineBlocks as $k => $apiDefineBlock) {
					if(preg_match($phpAPIDOcParamRegexes['@apiDefine'], $apiDefineBlock, $matches)) {
						$apiDefinedName = $matches[2];
						$result[$apiDefinedName] = [];
						if(preg_match_all('/(@api[\w]+)|(@api)/', $apiDefineBlock, $matches)) {
							$phpAPIDocBlockParams = $matches[0];
							foreach($phpAPIDocBlockParams as $phpAPIDocBlockParam) {
								$regex = $phpAPIDOcParamRegexes[$phpAPIDocBlockParam];
								if(preg_match_all($regex, $apiDefineBlock, $matches)) {
									$paramNames = $matches[1];
									$paramValues = $matches[2];
									//print_r($matches);

									foreach($paramNames as $k => $paramName) {
										$paramName = trim($paramName);
										if(in_array($paramName, $apiParamsWithMultipleValues)) {
											$value = [];

											switch($paramName) {
												case 'apiHeader':
													$value['required'] = [];
													$value['optional'] = [];
													foreach($matches[3] as $j => $match) {
														$name = trim($matches[4][$j]);
														$type = trim($paramValues[$j]);
														$defaultValue = trim($matches[5][$j], '"');
														$exampleValue = trim($matches[9][$j], ':"');
														$description = trim($matches[13][$j]);

														if(preg_match('/\[([\w\d]+)]/', $match)) {
															$value['optional'][$name] = [];
															$value['optional'][$name]['type'] = $type;
															if(!empty($defaultValue))
																$value['optional'][$name]['default'] = $defaultValue;
															if(!empty($description))
																$value['optional'][$name]['description'] = $description;
															if(!empty($exampleValue))
																$value['optional'][$name]['example'] = $exampleValue;
														} else {
															$value['required'][$name] = [];
															$value['required'][$name]['type'] = $type;
															if(!empty($defaultValue))
																$value['required'][$name]['default'] = $defaultValue;
															if(!empty($description))
																$value['required'][$name]['description'] = $description;
															if(!empty($exampleValue))
																$value['required'][$name]['example'] = $exampleValue;
														}
													}
													break;
												case 'apiHeaderExample':
													$types = $matches[2];
													$titles = $matches[3];
													$samples = $matches[6];

													foreach($titles as $j => $title) {
														$type = $types[$j];
														$sample = $samples[$j];
														$value[$type] = [];
														$value[$type]['title'] = trim($title);
														$value[$type]['sample'] = trim(str_replace('*', '', $sample));
													}
													break;
												case 'apiSuccessExample':
												case 'apiErrorExample':
													$types = $matches[3];
													$titles = $matches[4];
													$samples = $matches[5];

													foreach($titles as $j => $title) {
														$type = $types[$j];
														$sample = $samples[$j];
														$sample = str_replace('*', '', $sample);
														$sample = preg_replace($phpAPIDOcParamRegexes['whitespace'], '', $sample);

														$value[$type] = [];
														$value[$type]['title'] = trim($title);
														$value[$type]['sample'] = $sample;
													}
													break;
												case 'apiError':
												case 'apiSuccess':
													//$types = $matches[2];
													$title = $matches[2][0];
													$description = $matches[3][0];
													$value['title'] = trim($title);
													$value['description'] = trim(str_replace('*', '', $description));
													break;
											}
											$result[$apiDefinedName][$paramName] = $value;
										} else {
											if($paramName == 'apiUse') {
												foreach($matches[2] as $j => $usedApiName) {
													$usedApi = $result[$usedApiName];
													$result[$apiDefinedName] = array_merge($result[$apiDefinedName], $usedApi);
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return $result;
	}

	private function sortAPIParamArray(array &$names) {
		if(in_array('@apiBody', $names)) {
			$keys = array_keys(array_intersect($names, ['@apiBody']));
			foreach($keys as $k => $key) {
				$val = $names[$key];
				unset($names[$key]);
				array_unshift($names, $val);
			}
		}
		if(in_array('@apiUse', $names)) {
			$keys = array_keys(array_intersect($names, ['@apiBody']));
			foreach($keys as $k => $key) {
				$val = $names[$key];
				unset($names[$key]);
				array_unshift($names, $val);
			}
		}
		if(in_array('@apiPermission', $names)) {
			$key = array_search('@apiPermission', $names);
			$val = $names[$key];
			unset($names[$key]);
			array_unshift($names, $val);
		}
		if(in_array('@apiDescription', $names)) {
			$key = array_search('@apiDescription', $names);
			$val = $names[$key];
			unset($names[$key]);
			array_unshift($names, $val);
		}
		if(in_array('@apiVersion', $names)) {
			$key = array_search('@apiVersion', $names);
			$val = $names[$key];
			unset($names[$key]);
			array_unshift($names, $val);
		}
		if(in_array('@apiName', $names)) {
			$key = array_search('@apiName', $names);
			$val = $names[$key];
			unset($names[$key]);
			array_unshift($names, $val);
		}
		if(in_array('@apiGroup', $names)) {
			$key = array_search('@apiGroup', $names);
			$val = $names[$key];
			unset($names[$key]);
			array_unshift($names, $val);
		}
		if(in_array('@api', $names)) {
			$key = array_search('@api', $names);
			$val = $names[$key];
			unset($names[$key]);
			array_unshift($names, $val);
		}
		if(in_array('@apiDefine', $names)) {
			$key = array_search('@apiDefine', $names);
			$val = $names[$key];
			unset($names[$key]);
			array_unshift($names, $val);
		}

		if(in_array('@apiHeader', $names)) {
			$keys = array_keys(array_intersect($names, ['@apiExample']));
			foreach($keys as $k => $key) {
				$names[] = $names[$key];
				unset($names[$key]);
			}
		}
		if(in_array('@apiHeaderExample', $names)) {
			$keys = array_keys(array_intersect($names, ['@apiHeaderExample']));
			foreach($keys as $k => $key) {
				$names[] = $names[$key];
				unset($names[$key]);
			}
		}
		if(in_array('@apiHeaderExamples', $names)) {
			$key = array_search('@apiHeaderExamples', $names);
			$names[] = $names[$key];
			unset($names[$key]);
		}
		if(in_array('@apiErrorExample', $names)) {
			$keys = array_keys(array_intersect($names, ['@apiErrorExample']));
			foreach($keys as $k => $key) {
				$names[] = $names[$key];
				unset($names[$key]);
			}
		}
		if(in_array('@apiSuccessExample', $names)) {
			$keys = array_keys(array_intersect($names, ['@apiSuccessExample']));
			foreach($keys as $k => $key) {
				$names[] = $names[$key];
				unset($names[$key]);
			}
		}
		if(in_array('@apiExample', $names)) {
			$keys = array_keys(array_intersect($names, ['@apiExample']));
			foreach($keys as $k => $key) {
				$names[] = $names[$key];
				unset($names[$key]);
			}
		}
		if(in_array('@apiExamples', $names)) {
			$key = array_search('@apiExamples', $names);
			$names[] = $names[$key];
			unset($names[$key]);
		}
		/*if(in_array('@apiUse', $names)) {
			$keys = array_keys(array_intersect($names, ['@apiUse']));
			foreach($keys as $k => $key) {
				$names[] = $names[$key];
				unset($names[$key]);
			}
		}*/
	}

	private function generatePADSpecification(string $input): bool {
		$baseDir = $input;
		$phpAPIDocParams = $this->getPADSpecification($input);

		if(count($phpAPIDocParams) > 0) {
			$json = [];
			foreach($phpAPIDocParams as $phpAPIDocParam) {
				$name = $phpAPIDocParam['apiName'];
				$json[$name] = $phpAPIDocParam;
			}
			$content = json_encode($json, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS);
			$content = str_replace('    ', "\t", $content);
			if(!file_exists("{$baseDir}padspec.json"))
				return (bool)file_put_contents("{$baseDir}padspec.json", $content);
			else
				return true;
		}
		return false;
	}

	private function getPADSpecification(string $input): array {
		global $phpAPIDOcParamRegexes, $apiParamsWithMultipleValues;
		$baseDir = $input;
		$files = $this->files;

		$phpDocsBlocks = $phpAPIDocParams = [];

		foreach($files as $file) {
			$content = file_get_contents($file);
			if(preg_match('/\/\*\*([\S\s])*\*\//', $content, $matches)) {
				$phpDocsBlock = $matches[0];
				$phpDocsBlocks[$file] = $phpDocsBlock;
				if(preg_match_all('/(@api[\w]+)|(@api)/', $phpDocsBlock, $matches)) {
					$phpAPIDocBlockParams = $matches[0];
					$this->sortAPIParamArray($phpAPIDocBlockParams);
					foreach($phpAPIDocBlockParams as $phpAPIDocBlockParam) {
						$regex = $phpAPIDOcParamRegexes[$phpAPIDocBlockParam];
						if(preg_match_all($regex, $phpDocsBlock, $matches)) {
							$paramNames = $matches[1];
							$paramValues = $matches[2];

							foreach($paramNames as $k => $paramName) {
								$paramName = trim($paramName);
								if(in_array($paramName, $apiParamsWithMultipleValues)) {
									$value = [];

									if($paramName == 'api') {
										$type = trim($matches[2][0]);
										$endpoint = trim($matches[3][0]);
										$title = trim($matches[4][0]);
										$value['title'] = $title;
										$value['type'] = $type;
										$value['endpoint'] = $endpoint;
									} elseif($paramName == 'apiBody') {
										$value['required'] = [];
										$value['optional'] = [];
										foreach($matches[3] as $j => $match) {
											$name = trim($matches[4][$j]);
											$type = trim($paramValues[$j]);
											$defaultValue = trim($matches[5][$j], '"');
											$exampleValue = trim($matches[9][$j], ':"');
											$description = trim($matches[13][$j]);
											if(preg_match('/\[([\w\d]+)]/', $match)) {
												$value['optional'][$name] = [];
												$value['optional'][$name]['type'] = $type;
												if(!empty($defaultValue))
													$value['optional'][$name]['default'] = $defaultValue;
												if(!empty($description))
													$value['optional'][$name]['description'] = $description;
												if(!empty($exampleValue))
													$value['optional'][$name]['example'] = $exampleValue;
											} else {
												$value['required'][$name] = [];
												$value['required'][$name]['type'] = $type;
												if(!empty($defaultValue))
													$value['required'][$name]['default'] = $defaultValue;
												if(!empty($description))
													$value['required'][$name]['description'] = $description;
												if(!empty($exampleValue))
													$value['required'][$name]['example'] = $exampleValue;
											}
										}
									} elseif($paramName == 'apiExample') {
										$titles = $matches[3];
										$samples = $matches[4];

										foreach($titles as $j => $title) {
											$type = $paramValues[$j];
											$sample = $samples[$j];
											$value[$type] = [];
											$value[$type]['title'] = trim($title);
											$value[$type]['sample'] = trim(str_replace('*', '', $sample));
										}
									}
									$phpAPIDocParams[$file][$paramName] = $value;
								} else {
									$value = [];
									if($paramName == 'apiExamples') {
										$exDefStrs = explode(';', $matches[2][0]);
										foreach($exDefStrs as $exDefStr) {
											$exDefs = explode(':', $exDefStr);
											$type = $exDefs[0];
											$title = $exDefs[1];
											$value[$type] = [];
											$value[$type]['title'] = trim($title);
											$value[$type]['sample'] = $this->getAPIExampleSample($type, $phpAPIDocParams[$file]);
										}
										$phpAPIDocParams[$file]['apiExample'] = $value;
									} else
										$phpAPIDocParams[$file][$paramName] = trim($paramValues[0]);
								}
							}
						}
					}
				}
			}
		}

		return $phpAPIDocParams;
	}

	private function getAPIExampleSample(string $language, array $apiDoc): string {
		$exLangs = [
			'http',
			'js',
			'jq',
			'java',
			'php_curl',
			'curl',
			'javascript',
			'jquery',
			'php',
		];
		$language = strtolower($language);
		$nl = "\r\n";

		$result = '';

		$api = $apiDoc['api'];
		$apiUse = $apiDoc['apiUse'];
		$apiBin = $this->bin[$apiUse];
		$apiBody = $apiDoc['apiBody'];
		$apiHeader = $apiBin['apiHeader'];

		if(in_array($language, $exLangs) && !empty($apiBody)) {
			$host = $this->config->url;
			$http2 = (bool)$this->config->http2;
			$httpVersion = ($http2) ? 'HTTP/2' : 'HTTP/1.1';
			$method = $api['type'];
			$endpoint = $api['endpoint'];

			$host = rtrim($host, ' \t\n\r\0\x0B/');
			$endpoint = ltrim($endpoint, ' \t\n\r\0\x0B/');

			$url = "$host/$endpoint";

			$method = strtoupper($method);
			$headers = $reqBody = [
				'encoded' => [],
				'decoded' => [],
			];

			foreach($apiHeader as $isRequired => $header) {
				foreach($header as $headerName => $headerDef) {
					$type = $headerDef['type'];
					$description = (!array_key_exists('description', $headerDef)) ? '' : $headerDef['description'];
					$default = (!array_key_exists('default', $headerDef)) ? '' : $headerDef['default'];
					$exValue = (!array_key_exists('example', $headerDef)) ? $default : $headerDef['example'];
					if($isRequired == 'optional') {
						$exValue = (empty($exValue)) ? $default : $exValue;
						$exValueEnc = urlencode($exValue);
						if(!empty($exValue)) {
							$headers[$headerName] = $exValue;
							$reqBody['encoded'][$headerName] = $exValueEnc;
							$reqBody['decoded'][$headerName] = $exValue;
						}
					} else {
						$exValueEnc = urlencode($exValue);
						$defaultEnc = urlencode($default);
						$headers['encoded'][$headerName] = (empty($exValue) && !empty($default)) ? $defaultEnc : $exValueEnc;
						$headers['decoded'][$headerName] = (empty($exValue) && !empty($default)) ? $default : $exValue;
					}
				}
			}

			foreach($apiBody as $isRequired => $body) {
				foreach($body as $fieldName => $fieldDef) {
					$type = $fieldDef['type'];
					$description = (!array_key_exists('description', $fieldDef)) ? '' : $fieldDef['description'];
					$default = (!array_key_exists('default', $fieldDef)) ? '' : $fieldDef['default'];
					$exValue = (!array_key_exists('example', $fieldDef)) ? $default : $fieldDef['example'];
					if($isRequired == 'optional') {
						$exValue = (empty($exValue)) ? $default : $exValue;
						$exValueEnc = urlencode($exValue);
						if(!empty($exValue)) {
							$reqBody['encoded'][$fieldName] = $exValueEnc;
							$reqBody['decoded'][$fieldName] = $exValue;
						}
					} else {
						$exValueEnc = urlencode($exValue);
						$defaultEnc = urlencode($default);
						$reqBody['encoded'][$fieldName] = (empty($exValue) && !empty($default)) ? $defaultEnc : $exValueEnc;
						$reqBody['decoded'][$fieldName] = (empty($exValue) && !empty($default)) ? $default : $exValue;
					}
				}
			}

			switch($language) {
				case 'http':
					$result .= "$method $url$endpoint $httpVersion\n";

					foreach($apiHeader as $isRequired => $header) {
						foreach($header as $headerName => $headerDef) {
							$type = $headerDef['type'];
							$description = (!array_key_exists('description', $headerDef)) ? '' : $headerDef['description'];
							$default = (!array_key_exists('default', $headerDef)) ? '' : $headerDef['default'];
							$exValue = (!array_key_exists('example', $headerDef)) ? $default : $headerDef['example'];
							if($isRequired == 'optional') {
								$exValue = (empty($exValue)) ? $default : $exValue;
								if(!empty($exValue))
									$result .= "\t$headerName: $exValue\n";
							} else
								$result .= (empty($exValue) && !empty($default)) ? "\t$headerName: $default\n" : "\t$headerName: $exValue\n";
						}
					}

					$result .= "\n";
					$fields = [];
					foreach($apiBody as $isRequired => $body) {
						foreach($body as $fieldName => $fieldDef) {
							$type = $fieldDef['type'];
							$description = (!array_key_exists('description', $fieldDef)) ? '' : $fieldDef['description'];
							$default = (!array_key_exists('default', $fieldDef)) ? '' : $fieldDef['default'];
							$exValue = (!array_key_exists('example', $fieldDef)) ? $default : $fieldDef['example'];
							$exValue = urlencode($exValue);
							if($isRequired == 'optional') {
								$exValue = (empty($exValue)) ? $default : $exValue;
								if(!empty($exValue))
									$fields[] = "$fieldName=$exValue";
							} else
								$fields[] = (empty($exValue) && !empty($default)) ? "$fieldName=$default" : "$fieldName=$exValue";
						}
					}
					$fieldStr = implode('&', $fields);
					$result .= "\t$fieldStr";
					break;
				case 'jq':
				case 'jquery':
					$builder = [
						'url' => $url,
						'type' => $method,
						'timeout' => 0,
						'headers' => $headers['decoded'],
						'data' => $reqBody['decoded'],
					];

					$build = json_encode($builder, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
					$build = str_replace('    ', "\t", $build);
					$result .= "let settings = $build\n\n";
					$result .= "$.ajax(settings).done(function(response) {\n";
					$result .= "\tconsole.log(response);\n";
					$result .= "});\n";
					$result .= "\n";
					break;
				case 'java':
					$method = strtolower($method);
					$result .= "import com.mashape.unirest.http.;\n";
					$result .= "import java.io.;\n";
					$result .= "\n";
					$result .= "public class main {\n";
					$result .= "\tpublic static void main(String []args) throws Exception {\n";
					$result .= "\t\tUnirest.setTimeouts(0, 0);\n";
					$result .= "\t\tHttpResponse<String> response = Unirest.$method(\"$url\")\n";

					foreach($headers['decoded'] as $name => $value) {
						$result .= "\t\t\t\t\t\t\t\t\t\t\t   .header(\"$name\", \"$value\")\n";
					}
					foreach($reqBody['decoded'] as $name => $value) {
						$result .= "\t\t\t\t\t\t\t\t\t\t\t   .field(\"$name\", \"$value\")\n";
					}

					$result .= "\t\t\t\t\t\t\t\t\t\t\t   .asString();\n";
					$result .= "\n";
					$result .= "\t\tSystem.out.println(response.getBody());\n";
					$result .= "\t}\n";
					$result .= "}\n";
					$result .= "\n";
					break;
				case 'php_curl':
				case 'php':
					$phpCURLVersionTXT = ($http2) ? 'CURL_HTTP_VERSION_2_1' : 'CURL_HTTP_VERSION_1_1';
					$result .= "<?php\n";
					$result .= "\$curl = curl_init();\n";
					$result .= "curl_setopt_array(\$curl, [\n";
					$result .= "\tCURLOPT_URL => '$url',\n";
					$result .= "\tCURLOPT_RETURNTRANSFER => true,\n";
					$result .= "\tCURLOPT_ENCODING => '',\n";
					$result .= "\tCURLOPT_MAXREDIRS => 10,\n";
					$result .= "\tCURLOPT_TIMEOUT => 0,\n";
					$result .= "\tCURLOPT_FOLLOWLOCATION => true,\n";
					$result .= "\tCURLOPT_HTTP_VERSION => $phpCURLVersionTXT,\n";
					$result .= "\tCURLOPT_CUSTOMREQUEST => '$method',\n";
					$result .= "\tCURLOPT_POSTFIELDS => '";
					foreach($reqBody['encoded'] as $name => $value) {
						$result .= "$name=$value&";
					}
					$result = rtrim($result, '&') . "',\n";
					$result .= "\tCURLOPT_HTTPHEADER => [\n";
					foreach($headers['decoded'] as $name => $value) {
						$result .= "\t\t'$name: $value',\n";
					}
					$result = rtrim($result, ',');
					$result .= "\t]\n";
					$result .= "]);\n";
					$result .= "\n";
					$result .= "\$response = curl_exec(\$curl);\n";
					$result .= "curl_close(\$curl);\n";
					$result .= "echo \$response;\n";
					$result .= "\n";
					//$result = htmlspecialchars($result);
					break;
				case 'curl':
					$result .= "curl --location --request $method \"$url\"";
					foreach($headers['decoded'] as $name => $value) {
						$result .= " --header \"$name: $value\"";
					}
					$result .= " --data-urlencode \"";
					foreach($reqBody['encoded'] as $name => $value) {
						$result .= "$name=$value&";
					}
					$result = rtrim($result, '&') . "\"";
					break;
			}
		}

		return $result;
	}

	private function getAPIContent(string $baseDir) {
		$filePath = "{$baseDir}padspec.json";
		return (file_exists($filePath)) ? json_decode(file_get_contents($filePath)) : new stdClass();
	}

	private function getAPIGroups(stdClass $content): array {
		$apiGroups = [];
		foreach($content as $k => $apiDoc) {
			$api = $apiDoc->api;
			$endpoint = $api->endpoint;
			$apititle = $api->title;
			$apiGroup = $apiDoc->apiGroup;
			$apiTitle = $apiDoc->apiGroup;
			$apiName = (!empty($apiDoc->apiName)) ? $apiDoc->apiName : str_replace('/', '', $endpoint);
			$group = (!empty($apiGroup)) ? $apiGroup : str_replace('/', '\\', $endpoint);
			$group = "$group\\$apititle\\$apiName";
			//$apiGroupLinks = explode('\\',"$apititle\\$apiName");
			$apiGroupLinks[$k] = $apiName;
			$g = explode_md($group, '\\');
			$apiGroups = array_replace_recursive($apiGroups, $g);
		}
		return $apiGroups;
	}

	private function getGroupContent(string $baseDir): array {
		$content = $this->apiContent;
		$apiGroups = $apiGroupLinks = [];

		$groupContent = [];
		foreach($content as $k => $apiDoc) {
			$api = $apiDoc->api;
			$endpoint = $api->endpoint;
			$apititle = $api->title;
			$apiGroup = $apiDoc->apiGroup;
			$apiTitle = $apiDoc->apiGroup;
			$apiName = (!empty($apiDoc->apiName)) ? $apiDoc->apiName : str_replace('/', '', $endpoint);
			$group = (!empty($apiGroup)) ? $apiGroup : str_replace('/', '\\', $endpoint);
			$group = "$group\\$apititle\\$apiName";
			//$apiGroupLinks = explode('\\',"$apititle\\$apiName");
			$apiGroupLinks[$k] = $apiName;
			/*$g = explode_md($group, '\\');
			$apiGroups = array_replace_recursive($apiGroups, $g);*/
			//$apiGroups = array_replace($apiGroups, explode('\\', $group));
			/*foreach($apiGroups as $group) {

			}*/
			//print_r_pre($keyPath);
			$groups = explode('\\', $group);
			foreach($groups as $j => $group) {
				if(!in_array($group, $apiGroups))
					$apiGroups[] = $group;
			}
		}
		if(count($apiGroups) > 0) {
			foreach($apiGroups as $k => $apiGroup) {
				if(property_exists($content, $apiGroup)) {
					$groupContent[$k] = $content->$apiGroup;
				} else {
					$groupContent[$k] = $apiGroup;
				}
			}
		}

		return $groupContent;
	}

	private function getGroupLinks(string $baseDir): array {
		$content = json_decode(file_get_contents("{$baseDir}padspec.json"));
		$apiGroupLinks = [];

		foreach($content as $k => $apiDoc) {
			$api = $apiDoc->api;
			$endpoint = $api->endpoint;
			$apititle = $api->title;
			$apiName = (!empty($apiDoc->apiName)) ? $apiDoc->apiName : str_replace('/', '', $endpoint);
			$apiGroupLinks[$k] = explode('\\', "$apititle\\$apiName");
			//$apiGroupLinks[$k] = $apiName;
		}

		return $apiGroupLinks;
	}

	private function buildMenu(array $menu, bool $chained = true): string {
		$html = "";
		$addedGroups = [];
		foreach($menu as $k => $item) {
			$name = (is_string($item)) ? $item : $k;

			$addedGroups[] = $name;
			$occurance = (array_search($name, $addedGroups)) ? "-" . array_search($name, $addedGroups) : "";
			$key = (!$chained && in_array($name, $addedGroups)) ? "$name$occurance" : $name;

			$html .= "<ul class='pad-list-group'>";
			$html .= "<li class='pad-list-item'><a href='#$key'>$k</a></li>";
			//$html .= (is_string($item)) ? "<li class='pad-list-item'><a href='#$key'>$name</a></li>" : "<li class='pad-list-item'><a href='#$key'>$name</a></li>";
			if(is_array($item)) {
				$html .= $this->buildMenu($item, $k);
			}
			$html .= "</ul>";
		}
		return $html;
	}

	private function getSidebarHTML(array $menu, bool $chained = true): string {
		$html = "<nav class='toc'>" . $this->buildMenu($menu, $chained);
		$html .= '<svg class="toc-marker" width="200" height="200" xmlns="http://www.w3.org/2000/svg">';
		$html .= '	<path stroke="#444" stroke-width="3" fill="transparent" stroke-dasharray="0, 0, 0, 1000" stroke-linecap="round" stroke-linejoin="round" transform="translate(-0.5, -0.5)" />';
		$html .= '</svg>';
		$html .= '</nav>';
		return $html;
	}

	private function getGroupHTML(stdClass $content): string {
		$api = $content->api;
		$title = $api->title;
		$apiType = $api->type;
		$apiEndpoint = $api->endpoint;

		$name = $content->apiName;
		$description = $content->apiDescription;

		/** @var \stdClass $apiExample */
		$apiExample = $content->apiExample;

		$url = (property_exists($this->config, 'url')) ? $this->config->url : '';
		$url = rtrim($url, ' \t\n\r\0\x0B/');
		$apiCall = "$url$apiEndpoint";

		$html = "<section id='$name'>";
		$html .= "<h2>$title</h2>";
		$html .= "<p>$description</p>";
		$html .= "<p><pre><code class='language-http'>$apiType $apiCall</code></pre></p>";
		$html .= "</section>";

		return $html;
	}

	private function getSectionsChain(array $groupPath, string $groupName, string $groupHTML, int $parentKey = null, bool $isParent = true): array {
		$chain = [];
		$paths = array_search_path($groupPath, $groupName, true, true, true)['path'];
		$count = count($paths, COUNT_RECURSIVE);
		$temp =& $chain;
		if($count > 0) {
			$i = 0;
			foreach($paths as $k => $path) {
				$arr = [];
				if($k == $groupName || $path == $groupName) {
					if(is_string($path)) {
						$arr[$path] = $groupHTML;

						$temp[$k] =& $arr;
					} else {
						$temp[$k] =& $groupHTML;
					}
				} elseif(is_array($path)) {
					$newChain = $this->getSectionsChain($path, $groupName, $groupHTML, false);
					$ch = array_replace_recursive($chain, $newChain);
					$temp[$k] =& $ch;
				}
				/*if(is_string($path)) {
					$arr[$path] = $groupHTML;

					$temp[$k] =& $arr;
				}*/
				$i++;
			}
		}
		return $chain;
	}

	private function getSectionsHTML(array $groupChain, stdClass $content, bool $chained = true, bool $isParent = true): string {
		$html = '';
		$url = (property_exists($this->config, 'url')) ? $this->config->url : '';
		$url = rtrim($url, ' \t\n\r\0\x0B/');
		$title = $description = $apiType = $apiEndpoint = '';
		$apiExample = $apiBody = $apiHeader = $apiGroup = $apiSuccessExample = $apiErrorExample = null;
		$i = 0;
		static $addedGroupsStatic = [];
		$addedGroups = [];
		foreach($groupChain as $name => $item) {
			if(property_exists($content, $name)) {
				$apiType = $content->$name->api->type;
				$apiEndpoint = $content->$name->api->endpoint;
				$title = $content->$name->api->title;
				$description = $content->$name->apiDescription;
				$apiGroup = $content->$name->apiGroup;
				/** @var \stdClass $apiExample */
				$apiExample = $content->$name->apiExample;
				$apiBody = $content->$name->apiBody;
				$apiUse = $content->$name->apiUse;
				$apiBin = json_decode(json_encode($this->bin[$apiUse], JSON_NUMERIC_CHECK));
				$apiHeader = $apiBin->apiHeader;
				$apiErrorExample = $apiBin->apiErrorExample;
				$apiSuccessExample = $apiBin->apiSuccessExample;
			}
			$addedGroupsStatic[] = $name;
			$addedGroups[] = $name;
			$occurance = (array_search($name, $addedGroups)) ? "-" . array_search($name, $addedGroups) : "";
			$key = (!$chained && in_array($name, $addedGroups)) ? "$name$occurance" : $name;
			if(is_array($item)) {
				$count = count($item, COUNT_RECURSIVE);
				$firstClass = ($isParent) ? ' pad-group-start' : '';
				if($count > 1 || $title == $name) {
					$html .= "<section id='$key' class='pad-no-content$firstClass'>";
					$html .= "<h2>$name</h2>";
					if(!$chained)
						$html .= "</section>";
				}
				$html .= $this->getSectionsHTML($item, $content, $chained, false);
			}
			if(is_string($item)) {
				//$html .= $item;
				$apiGroup .= "\\$title";
				$endClass = (!$isParent) ? ' pad-group-end' : '';
				$isParent = true;
				$apiCall = "$url$apiEndpoint";

				$breadcrumbHTML = "<nav style='--bs-breadcrumb-divider: \">\";' aria-label='breadcrumb'>";
				$breadcrumbHTML .= "<ol class='breadcrumb'>";
				foreach(explode('\\', $apiGroup) as $agkey => $addedGroup) {
					$bcActiveClass = ($addedGroup == $key) ? ' active' : '';
					$breadcrumbHTML .= "<li class='breadcrumb-item$bcActiveClass'><a href='#'>$addedGroup</a></li>";
				}
				$breadcrumbHTML .= "</ol>";
				$breadcrumbHTML .= "</nav>";

				$html = "<section id='$key' class='pad-has-content$endClass'>";
				//$html .= $breadcrumbHTML;
				$html .= "<h2>$title</h2>";
				$html .= "<p>$description</p>";
				$html .= "<pre data-type='$apiType' data-dependencies='http'><span class='badge http-method-$apiType'>$apiType</span><code class='language-http'>$apiCall</code></pre>";

				if(!empty($apiExample)) {
					$ci = 1;
					$padceTabHTML = $padceTabContentHTML = '';
					foreach($apiExample as $callType => $callExample) {
						$callType = strtolower($callType);
						$callLanguage = $this->getSampleLanguage($callType);
						$callTitle = $callExample->title;
						$callSample = htmlspecialchars($callExample->sample);
						$padceTabHTML .= "<li class='nav-item' role='presentation'>";
						$padceTabHTML .= "<button class='nav-link" . (($ci == 1) ? ' active' : '') . "' data-bs-toggle='tab' data-bs-target='#pad-ce-tab-$key-$callType' role='tab' aria-controls='pad-ce-tab-$key-$callType' aria-selected='" . (($ci == 1) ? 'true' : 'false') . "'>$callTitle</button>";
						$padceTabHTML .= "</li>";
						$padceTabContentHTML .= "<div class='tab-pane fade" . (($ci == 1) ? ' show active' : '') . "' id='pad-ce-tab-$key-$callType' role='tabpanel' aria-labelledby='pad-ce-tab-$key-$callType'>";
						$padceTabContentHTML .= "<pre class='line-numbers' data-type='$callLanguage'><code class='language-$callLanguage'>$callSample</code></pre>";
						$padceTabContentHTML .= "</div>";
						$ci++;
					}
					$html .= "<div class='pad-call-examples'>";
					$html .= "<h3 class='pad-sub-section-heading'>Call Example</h3>";
					$html .= "<ul class='nav nav-tabs' id='padceTab' role='tablist'>";
					$html .= $padceTabHTML;
					$html .= "</ul>";
					$html .= "<div class='tab-content' id='myTabContent'>";
					$html .= $padceTabContentHTML;
					$html .= "</div>";
					$html .= "</div>";
				}

				$apiHeaderRequired = $apiHeader->required;
				$apiHeaderOptional = $apiHeader->optional;
				if(!empty($apiHeaderRequired) || !empty($apiHeaderOptional)) {
					$html .= "<div class='pad-request-body'>";
					$html .= "<h3 class='pad-sub-section-heading'>Request Headers</h3>";
					$html .= "<table class='table table-bordered table-striped table-hover'>";
					$html .= "<thead>";
					$html .= "<tr>";
					$html .= "<th>Name</th>";
					$html .= "<th>Type</th>";
					$html .= "<th>Description</th>";
					$html .= "</tr>";
					$html .= "</thead>";
					$html .= "<tbody>";
					if(!empty($apiHeaderRequired)) {
						$padrbHTML = '';
						foreach($apiHeaderRequired as $fieldName => $fieldBody) {
							$fieldType = $fieldBody->type;
							$fieldDescription = (property_exists($fieldBody, 'description')) ? $fieldBody->description : '';
							$fieldDefault = (property_exists($fieldBody, 'default')) ? $fieldBody->default : '';
							$fieldDefaultValue = (!empty($fieldDefault)) ? "<p class='pad-rb-default'>Default value: <span class='pad-rb-default-value'>$fieldDefault</span></p>" : '';

							$padrbHTML .= "<tr>";
							$padrbHTML .= "<td class='pad-rb-name'>$fieldName<span class='badge bg-danger'>Required</span></td>";
							$padrbHTML .= "<td class='pad-rb-type'>$fieldType</td>";
							$padrbHTML .= "<td class='pad-rb-description'>$fieldDescription$fieldDefaultValue</td>";
							$padrbHTML .= "</tr>";
						}

						$html .= $padrbHTML;
					}
					if(!empty($apiHeaderOptional)) {
						$padrbHTML = '';
						foreach($apiHeaderOptional as $fieldName => $fieldBody) {
							$fieldType = $fieldBody->type;
							$fieldDescription = (property_exists($fieldBody, 'description')) ? $fieldBody->description : '';
							$fieldDefault = (property_exists($fieldBody, 'default')) ? $fieldBody->default : '';
							$fieldDefaultValue = (!empty($fieldDefault)) ? "<p class='pad-rb-default'>Default value: <span class='pad-rb-default-value'>$fieldDefault</span></p>" : '';

							$padrbHTML .= "<tr>";
							$padrbHTML .= "<td class='pad-rb-name'>$fieldName<span class='badge bg-secondary'>Optional</span></td>";
							$padrbHTML .= "<td class='pad-rb-type'>$fieldType</td>";
							$padrbHTML .= "<td class='pad-rb-description'>$fieldDescription$fieldDefaultValue</td>";
							$padrbHTML .= "</tr>";
						}

						$html .= $padrbHTML;
					}

					$html .= "</tbody>";
					$html .= "</table>";
					$html .= "</div>";
				}

				$apiBodyRequired = $apiBody->required;
				$apiBodyOptional = $apiBody->optional;
				if(!empty($apiBodyRequired) || !empty($apiBodyOptional)) {
					$html .= "<div class='pad-request-body'>";
					$html .= "<h3 class='pad-sub-section-heading'>Request Body</h3>";
					$html .= "<table class='table table-bordered table-striped table-hover'>";
					$html .= "<thead>";
					$html .= "<tr>";
					$html .= "<th>Name</th>";
					$html .= "<th>Type</th>";
					$html .= "<th>Description</th>";
					$html .= "</tr>";
					$html .= "</thead>";
					$html .= "<tbody>";
					if(!empty($apiBodyRequired)) {
						$padrbHTML = '';
						foreach($apiBodyRequired as $fieldName => $fieldBody) {
							$fieldType = $fieldBody->type;
							$fieldDescription = (property_exists($fieldBody, 'description')) ? $fieldBody->description : '';
							$fieldDefault = (property_exists($fieldBody, 'default')) ? $fieldBody->default : '';
							$fieldDefaultValue = (!empty($fieldDefault)) ? "<p class='pad-rb-default'>Default value: <span class='pad-rb-default-value'>$fieldDefault</span></p>" : '';

							$padrbHTML .= "<tr>";
							$padrbHTML .= "<td class='pad-rb-name'>$fieldName<span class='badge bg-danger'>Required</span></td>";
							$padrbHTML .= "<td class='pad-rb-type'>$fieldType</td>";
							$padrbHTML .= "<td class='pad-rb-description'>$fieldDescription$fieldDefaultValue</td>";
							$padrbHTML .= "</tr>";
						}

						$html .= $padrbHTML;
					}
					if(!empty($apiBodyOptional)) {
						$padrbHTML = '';
						foreach($apiBodyOptional as $fieldName => $fieldBody) {
							$fieldType = $fieldBody->type;
							$fieldDescription = (property_exists($fieldBody, 'description')) ? $fieldBody->description : '';
							$fieldDefault = (property_exists($fieldBody, 'default')) ? $fieldBody->default : '';
							$fieldDefaultValue = (!empty($fieldDefault)) ? "<p class='pad-rb-default'>Default value: <span class='pad-rb-default-value'>$fieldDefault</span></p>" : '';

							$padrbHTML .= "<tr>";
							$padrbHTML .= "<td class='pad-rb-name'>$fieldName<span class='badge bg-secondary'>Optional</span></td>";
							$padrbHTML .= "<td class='pad-rb-type'>$fieldType</td>";
							$padrbHTML .= "<td class='pad-rb-description'>$fieldDescription$fieldDefaultValue</td>";
							$padrbHTML .= "</tr>";
						}

						$html .= $padrbHTML;
					}

					$html .= "</tbody>";
					$html .= "</table>";
					$html .= "</div>";
				}

				if(!empty($apiErrorExample)) {
					$ci = 1;
					$padereTabHTML = $padereTabContentHTML = '';
					foreach($apiErrorExample as $callType => $callExample) {
						$callType = strtolower($callType);
						$callLanguage = $this->getSampleLanguage($callType);
						$callTitle = $callExample->title;
						$callSample = htmlspecialchars($callExample->sample);
						$padereTabHTML .= "<li class='nav-item' role='presentation'>";
						$padereTabHTML .= "<button class='nav-link" . (($ci == 1) ? ' active' : '') . "' data-bs-toggle='tab' data-bs-target='#pad-ce-tab-$key-$callType' role='tab' aria-controls='pad-ce-tab-$key-$callType' aria-selected='" . (($ci == 1) ? 'true' : 'false') . "'>$callTitle</button>";
						$padereTabHTML .= "</li>";
						$padereTabContentHTML .= "<div class='tab-pane fade" . (($ci == 1) ? ' show active' : '') . "' id='pad-ce-tab-$key-$callType' role='tabpanel' aria-labelledby='pad-ce-tab-$key-$callType'>";
						$padereTabContentHTML .= "<pre class='line-numbers' data-type='$callLanguage'><code class='language-$callLanguage'>$callSample</code></pre>";
						$padereTabContentHTML .= "</div>";
						$ci++;
					}
					$html .= "<div class='pad-response-error-examples'>";
					$html .= "<h3 class='pad-sub-section-heading'>Error Response</h3>";
					$html .= "<ul class='nav nav-tabs' id='padereTab' role='tablist'>";
					$html .= $padereTabHTML;
					$html .= "</ul>";
					$html .= "<div class='tab-content' id='myTabContent'>";
					$html .= $padereTabContentHTML;
					$html .= "</div>";
					$html .= "</div>";
				}
				
				if(!empty($apiSuccessExample)) {
					$ci = 1;
					$padsreTabHTML = $padsreTabContentHTML = '';
					foreach($apiSuccessExample as $callType => $callExample) {
						$callType = strtolower($callType);
						$callLanguage = $this->getSampleLanguage($callType);
						$callTitle = $callExample->title;
						$callSample = htmlspecialchars($callExample->sample);
						$padsreTabHTML .= "<li class='nav-item' role='presentation'>";
						$padsreTabHTML .= "<button class='nav-link" . (($ci == 1) ? ' active' : '') . "' data-bs-toggle='tab' data-bs-target='#pad-ce-tab-$key-$callType' role='tab' aria-controls='pad-ce-tab-$key-$callType' aria-selected='" . (($ci == 1) ? 'true' : 'false') . "'>$callTitle</button>";
						$padsreTabHTML .= "</li>";
						$padsreTabContentHTML .= "<div class='tab-pane fade" . (($ci == 1) ? ' show active' : '') . "' id='pad-ce-tab-$key-$callType' role='tabpanel' aria-labelledby='pad-ce-tab-$key-$callType'>";
						$padsreTabContentHTML .= "<pre class='line-numbers' data-type='$callLanguage'><code class='language-$callLanguage'>$callSample</code></pre>";
						$padsreTabContentHTML .= "</div>";
						$ci++;
					}
					$html .= "<div class='pad-response-success-examples'>";
					$html .= "<h3 class='pad-sub-section-heading'>Success Response</h3>";
					$html .= "<ul class='nav nav-tabs' id='padereTab' role='tablist'>";
					$html .= $padsreTabHTML;
					$html .= "</ul>";
					$html .= "<div class='tab-content' id='myTabContent'>";
					$html .= $padsreTabContentHTML;
					$html .= "</div>";
					$html .= "</div>";
				}

				$html .= "</section>";
			}
			if($chained && is_array($item)) {
				$count = count($item, COUNT_RECURSIVE);
				if($count > 1 || $title == $name) {
					$html .= "</section>";
				}
			}
		}
		return $html;
	}

	private function getContentHTML(): string {
		$content = $this->apiContent;
		$groupPath = $this->apiGroups;

		$html = '<main class="pad-content">';
		$html .= '<article class="contents">';
		$chain = [];
		foreach($content as $k => $apidoc) {
			$api = $apidoc->api;
			$groupName = $apidoc->apiName;
			$groupHTML = $this->getGroupHTML($apidoc);
			$chain = array_replace_recursive($chain, $this->getSectionsChain($groupPath, $groupName, $groupHTML));
		}
		$html .= $this->getSectionsHTML($chain, $content, false);
		$html .= '</article>';
		$html .= '</main>';

		return $html;
	}

	/**
	 * @return string
	 */
	public function getSidebar(): string {
		return $this->sidebar;
	}

	/**
	 * @return string
	 */
	public function getContent(): string {
		return $this->content;
	}

	/**
	 * @return array
	 */
	public function getArgs(): array {
		return $this->args;
	}

	/**
	 * @param string|null $key
	 *
	 * @return \stdClass|string|int|bool
	 */
	public function getConfig(string $key = null): stdClass|string|int|bool {
		return (!empty($key) && property_exists($this->config, $key)) ? $this->config->$key : $this->config;
	}

	public function getArgument(string $arg): string {
		return (array_key_exists($arg, $this->args)) ? $this->args[$arg] : "";
	}

}