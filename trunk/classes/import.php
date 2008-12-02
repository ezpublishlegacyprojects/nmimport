<?php

include_once( "lib/ezutils/classes/ezini.php" );
include_once( 'kernel/classes/workflowtypes/event/ezpaymentgateway/ezpaymentlogger.php' );

class import
{
	var $ini;
	
	var $labelLines;
	
	var $importFormat;
	
	var $formatINI;
	
	var $formatData;
	
	var $error;
	
	var $loggingEnabled;
	
	var $logger;
	
	function import()
	{
		// prepare error container
		$this->error = array();
		
		// initiate objects
		$this->ini =& eZINI::instance( 'import.ini' );	
		
		// amount of label lines (lines to ignore when reading data)
		$this->labelLines = 0;
		
		// disable logging by default
		$this->loggingEnabled = false;
		
		// prepare logger (in case we need it)
		$this->logger =& eZPaymentLogger::CreateForAdd( 'var/log/nmimport.log' );  
	}
	
	function importFormats()
	{
		$importFormats = $this->ini->variable( 'Import', 'ImportFormats' );
		return $importFormats;
	}
	
	function setImportFormat($importFormat)
	{
		$this->importFormat = $importFormat;
		
		$this->getImportFormatData();
	}
	
	function getImportFormatData()
	{
		$this->formatINI = eZINI::instance( 'importformat_' . $this->importFormat . '.ini' );
		
		$columns = array();
		foreach($this->formatINI->variable( 'Import', 'Columns' ) as $colData)
		{
			$colArray 		= explode(";", $colData);
			$key 			= $colArray[0] - 1;
			$columns[$key]	= $colArray[1];
		}
		
		$this->formatData = array(	'parent_node_id'	=> $this->formatINI->variable( 'Import', 'ParentNodeID' ), 
									'class_identifier'	=> $this->formatINI->variable( 'Import', 'ClassIdentifier' ), 
									'columns'			=> $columns, 
									'creator_object_id'	=> $this->formatINI->variable( 'Import', 'CreatorObjectID' ), 
									'section_id'		=> $this->formatINI->variable( 'Import', 'SectionID' ), 
									'title_attribute'	=> $this->formatINI->variable( 'Import', 'TitleAttribute' ));
									
		
	}
	
	function parseData(&$data)
	{
		// split data into lines
		$data 	= trim($data);
		$lines 	= explode("\n", $data);
		
		// for each line
		foreach($lines as $l => $line)
		{
			// if it's not a label line
			if($l >= $this->labelLines)
			{
				// split data into fields
				$line 	= trim($line);
				$fields = explode("\t", $line);
				
				// for each field
				$dataSet = array();
				foreach($fields as $k => $field)
				{
					// get field identifier
					$identifier = $this->formatData['columns'][$k];
					
					// append to dataset
					$dataSet[$identifier] = $field;
				}
				
				// append data set to data
				$this->data[] = $dataSet;	
			}
		}
	}
	
	// can be added later
	function validateData()
	{
		return true;
	}
	
	function logMsg($msg, $error = false)
	{
		if($this->loggingEnabled)
		{
			$this->logger->writeTimedString( $msg );
		}
		
		if($error)
		{
			$this->errorExists = true;
			exit();
		}
		
		$this->msgs[] = $msg;
	}
	
	function startImport()
	{
		$this->logMsg('===========START IMPORT===========');
		
		// for each line
		foreach($this->data as $lineNo => $lineData)
		{
			$this->importSingle($lineNo, $lineData);
		}
		
		$this->logMsg('===========IMPORT SUCCESSFULLY FINISHED===========');
	}
	
	function importSingle($lineNo, $lineData)
	{
		// fetch class
		$class 	=& eZContentClass::fetchByIdentifier( $this->formatData['class_identifier'] );

		// instantiate the object with the user id and put it in a section
		$object =& $class->instantiate( $this->formatData['creator_object_id'], $this->formatData['section_id'] );
		
		// assign node
		$nodeAssignment =& eZNodeAssignment::create( 	array( 	'contentobject_id' => $object->attribute( 'id' ),
																'contentobject_version' => $object->attribute( 'current_version' ),
																'parent_node' => $this->formatData['parent_node_id'],
																'sort_field' => 2,
																'sort_order' => 0,
																'is_main' => 1
															 )
												   );
		$nodeAssignment->store();
		
		
		// create a new version
		// TODO: should not be hard coded to 1
		$versionNo = 1;
		$version =& $object->version( $versionNo );

		// set the version status to draft
		$version->setAttribute( 'status', EZ_VERSION_STATUS_DRAFT );
		$version->store();
		
		// get title attribute
		$titleAttribute = $this->formatData['title_attribute'];
		
		// set object name
		$object->setAttribute( 'name', $lineData[$titleAttribute]); // set object name

		// get object attributes			
		$dataMap =& $version->dataMap();

		// for each column
		foreach($this->formatData['columns'] as $attributeIdentifier)
		{
			// get attribute
			// TODO: Expand to handle other datatypes than just plan text
			$dataMap[$attributeIdentifier]->setAttribute( 'data_text', $lineData[$attributeIdentifier] );
			$dataMap[$attributeIdentifier]->store();
		}

		// store content object
		$object->store();

		// publish content object
		$operationResult = eZOperationHandler::execute( 'content', 'publish', array( 'object_id' => $object->attribute( 'id' ),
																 					'version' => $object->attribute('current_version') ) );
		
		$msg = 'The object  ' . $lineData[$titleAttribute] . ' was successfully stored.';
		$lineNo++;
		$this->logMsg('Line #' . $lineNo . ': ' . $msg);
		$this->success[$lineNo][] = $msg;
	}
}

?>