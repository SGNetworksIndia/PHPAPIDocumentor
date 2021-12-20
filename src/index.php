<?php
require_once "phar://pad.phar/inc/config.php";

//$config = $PADBase->getConfig();
$outputDir = $PADBase->getArgument('output');
$sidebarHTML = $PADBase->getSidebar();
$html = $PADBase->getContent();

$title = $PADBase->getConfig('title');


$htmlContent = <<<HTML
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>$title</title>
		<link rel="stylesheet" type="text/css" href="assets/plugins/bootstrap/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="assets/plugins/prism/prism.css">
		<link rel="stylesheet" type="text/css" href="assets/css/base.css">
	</head>
	<body>
		$sidebarHTML
		
		$html
		
		<script type="text/javascript" src="assets/plugins/prism/prism.js"></script>
		<script type="text/javascript" src="assets/plugins/bootstrap/bootstrap.bundle.min.js"></script>
		<script type="text/javascript" src="assets/js/base.js"></script>
	</body>
</html>

HTML;

$outputHTML = "{$outputDir}index.html";
if(file_put_contents($outputHTML, $htmlContent)) {
	$outputHTML = 'file:///' . str_replace('\\', '/', $outputHTML);
	if(xcopy("phar://pad.phar/assets", "{$outputDir}assets"))
		colorLog("The API Documentation is generated and stored in $outputHTML file.", 's');
	else {
		colorLog("Failed to copy the assets required for the API Documentation.", 'e');
		unlink($outputHTML);
	}
} else {
	colorLog("Failed to generate the API Documentation.", 'e');
}
