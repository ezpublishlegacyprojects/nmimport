<?php

include_once( "kernel/common/template.php" );
include_once( "extension/nmimport/classes/import.php" );

// initate objects
$tpl 		=& templateInit();
$http 		=& eZHTTPTool::instance();
$import 	= new import;

// enable logging
$import->loggingEnabled = true;


// if the form was submitted
if($http->hasPostVariable('Import'))
{
	// if the first line should be ignored
	if($http->postVariable('IgnoreFirstLine') == 1)
	{
		$import->labelLines = 1;
	}
	
	// set import format
	$import->setImportFormat($http->postVariable('ImportFormat'));
	
	// parse data
	$import->parseData($http->postVariable('ImportData'));
	
	// validate data
	$import->validateData();
	
	// if one or more validation error exists
	if(count($import->error) > 0)
	{
		// set errors in template
		$tpl->setVariable('errors', $import->error);
	}
	// if validation succeeded
	else
	{
		// import data
		$import->startImport();
		
		// store warnings and success msgs
		$tpl->setVariable("warnings", $import->warnings);
		$tpl->setVariable("success", $import->success);
	}
}

$tpl->setVariable("import_format_list", $import->importFormats());

$Result['content'] =& $tpl->fetch( "design:import/input.tpl" );
$Result['path'] = array( array( 'text' => "Import input", 'url' => false ) );

?>
