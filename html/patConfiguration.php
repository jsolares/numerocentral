<?PHP
/**
*	patConfiguration
*	Class to read XML config files
*
*	@access		public
*	@version	1.4
*	@author		Stephan Schmidt <schst@php-tools.de>
*	@package	patConfiguration
*/
	class	patConfiguration
{
/**
*	table used for translation of xml special chars
*	@var	array	$xmlSpecialchars
*/
	var	$xmlSpecialchars	=	array(
										"&"		=>	"&amp;",
										"'"		=>	"&apos;",
										"\""	=>	"&quot;",
										"<"		=>	"&lt;",
										">"		=>	"&gt;"
									);
/**
*	current path as array
*	@var	array	$path
*/
	var	$path		=	array();

/**
*	array that stores configuration
*	@var	array	$conf
*/
	var	$conf		=	array();

/**
*	array that stores configuration from the current file
*	@var	array	$currentConf
*/
	var	$currentConf=	array();

/**
*	array that stores extensions
*	@var	array	$extensions
*/
	var	$extensions	=	array();

/**
*	stack of the namespaces
*	@var	array	$nsStack
*/
	var	$nsStack	=	array();

/**
*	stack of values
*	@var	array	$valStack
*/
	var	$valStack	=	array();

/**
*	current depth of the stored values, i.e. array depth
*	@var	int	$valDepth
*/
	var	$valDepth	=	1;

/**
*	current CDATA found
*	@var	string	$data
*/
	var	$data		=	"";
	
/**
*	directory where include files are located
*	@var	string	$includeDir
*/
	var	$includeDir		=	"";
	
/**
*	directory where cache files are located
*	@var	string	$cacheDir
*/
	var	$cacheDir		=	"cache";
	
/**
*	list of all files that were needed
*	@var	array	$externalFiles
*/
	var	$externalFiles		=	array();
	
/**
*	all open files
*	@var	array	$xmlFiles
*/
	var	$xmlFiles			=	array();
	
/**
*	list of tags and the default types
*	@var	array	$defaultTypes
*/
	var	$defaultTypes		=	array();

/**
*	set default types for tags
*
*	@access	public
*	@param	array	$defaultTypes
*/
	function	setDefaultTypes( $types )
	{
		$this->defaultTypes		=	$types;
	}
	
/**
*	set the directory, where all xml config files are stored
*
*	@access	public
*	@param	string	$configDir	name of the directory
*/
	function	setConfigDir( $configDir )
	{
		$this->configDir	=	$configDir;
	}
	
/**
*	set the directory, where all extensions are stored
*
*	@access	public
*	@param	string	$includeDir	name of the directory
*/
	function	setIncludeDir( $includeDir )
	{
		$this->includeDir	=	$includeDir;
	}

/**
*	set the directory, where all cache files are stored
*
*	@access	public
*	@param	string	$cacheDir	name of the directory
*/
	function	setCacheDir( $cacheDir )
	{
		$this->cacheDir	=	$cacheDir;
	}
	
/**
*	load a configuration from a cache
*	if cache is not valid, it will be updated automatically
*
*	@access	public
*	@param	string	$file	name of config file
*	@param	string	$mode	mode of the parsing ( "w" = overwrite old config, "a" = append to config )
*/	
	function	loadCachedConfig( $file, $mode = "w" )
	{
		$this->currentConf		=	array();
		$this->externalFiles	=	array();

		//	clear old values
		if( $mode == "w" )
			$this->conf		=	array();

		//	get full path
		$file	=	( $this->configDir!="" ) ? $this->configDir."/".$file : $file; 

		if( $result	=	$this->loadFromCache( $file ) )
		{
			$this->conf		=	array_merge( $this->conf, $result );
		}
		else
		{
			$this->parseXMLFile( $file );
			$this->writeCache( $file, $this->currentConf, $this->externalFiles );
		}
		return	true;
	}

/**
*	parse a configuration file
*
*	@access	public
*	@param	string	$file	name of the configuration file
*	@param	string	$mode	mode of the parsing ( "w" = overwrite old config, "a" = append to config )
*/	
	function	parseConfigFile( $file, $mode = "w" )
	{
		$this->path				=	array();
		$this->externalFiles	=	array();
		$this->currentConf		=	array();

		if( $mode == "w" )
			$this->conf		=	array();

		$file			=	( $this->configDir!="" ) ? $this->configDir."/".$file : $file; 

		$this->parseXMLFile( $file );

		return	true;
	}

/**
*	load cache
*
*	@access	private
*	@param	string	$file	filename
*	@return	mixed	$result	config on success, false otherwise
*/
	function	loadFromCache( $file )
	{
		$cacheFile	=	$this->cacheDir . "/" . md5( $file ) . ".cache";

		if( !file_exists( $cacheFile ) )
			return	false;

		$cacheTime		=	filemtime( $cacheFile );

		if( filemtime( $file ) > $cacheTime )
			return	false;

		if( !$fp = @fopen( $cacheFile, "r" ) )
			return	false;
			
		$result		=	false;
		flock( $fp, LOCK_SH );
		while( !feof( $fp ) )
		{
			$line	=	trim( fgets( $fp, 4096 ) );
			list( $action, $param )	=	explode( "=", $line, 2 );

			switch( $action )
			{
				case	"checkFile":
					if( filemtime( $param ) > $cacheTime )
					{
						flock( $fp, LOCK_UN );
						fclose( $fp );
						return	false;
					}
					break;
				case	"startCache":
					$result		=	unserialize( fread( $fp, filesize( $cacheFile ) ) );
					break 2;
				default:
					flock( $fp, LOCK_UN );
					fclose( $fp );
					return	false;
					break;
			}
		}
		
		flock( $fp, LOCK_UN );
		fclose( $fp );
		return	$result;
	}
	
/**
*	write cache
*
*	@access	private
*	@param	string	$file	filename
*	@param	array	$config	configuration
*	@param	array	$externalFiles	list of files used
*/
	function	writeCache( $file, $config, $externalFiles )
	{
		$cacheData	=	serialize( $config );
		$cacheFile	=	$this->cacheDir . "/" . md5( $file ) . ".cache";
		
		$fp			=	@fopen( $cacheFile, "w" );
		if( !$fp )
			return	false;
		flock( $fp, LOCK_EX );

		$cntFiles	=	count( $externalFiles );
		for( $i = 0; $i < $cntFiles; $i++ )
			fputs( $fp, "checkFile=".$externalFiles[$i]."\n" );

		fputs( $fp, "startCache=yes"."\n" );
		fwrite( $fp, $cacheData );
		flock( $fp, LOCK_UN );
		fclose( $fp );
		return	true;
	}
	
/**
*	write a configfile
*	format may be php or xml
*
*	@access	public
*	@param	string	$filename	name of the configfile
*	@param	string	$format		format of the config file (xml or php)
*	@param	array	$options	available options for php: varname => anyString ; available options for xml: mode => pretty
*/
	function	writeConfigFile( $filename, $format = "xml", $options = array() )
	{
		switch( $format )
		{
			case	"php":
				$content	=	$this->buildPHPConfigFile( $options );
				break;
			default:
				$content	=	$this->buildXMLConfigFile( $options );
				break;
		}
		if( $content )
		{
			$file	=	( $this->configDir!="" ) ? $this->configDir."/".$filename : $filename; 
			$fp		=	fopen( $file, "w" );
			if( $fp )
			{
				flock( $fp, LOCK_EX );
				fputs( $fp, $content );
				flock( $fp, LOCK_UN );
				fclose( $fp );
			}
		}
	}

/**
*	create an xml representation of the current config
*
*	@access	private
*	@return	string	$xml	xml representation
*/
	function	buildXMLConfigFile( $options )
	{
		$this->openTags		=	array();

		$config				=	$this->conf;
		ksort( $config );
		reset( $config );
		
		if( $options["mode"] == "pretty" )
			$options["nl"]		=	"\n";
		else
			$options["nl"]		=	"";

		$options["depth"]		=	0;
		
		$xml		=	"<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
		$xml		.=	"<configuration>".$options["nl"];
		$options["depth"]++;
		
		while( list( $key, $value ) = each( $config ) )
		{
			$path	=	explode( ".", $key );

			switch( count( $path ) )
			{
				case	0:
					$this->niceDie( "buildXMLConfigFile", "configValue without name found!" );
					break;
				default:
					$openNew		=	array();

					$tag			=	array_pop( $path );

					$start			=	max( count( $path ), count( $this->openTags ) );
					
					for( $i=( $start-1 ); $i>=0; $i-- )
					{
						if( $path[$i] != $this->openTags[$i] )
						{
							if( $this->openTags[$i] )
							{
								array_pop( $this->openTags );
								$options["depth"]--;
								if( $options["mode"] == "pretty" )
									$xml	.=	str_repeat( "\t", $options["depth"] );
								$xml	.=	"</path>".$options["nl"];
							}
							if( $path[$i] )
								array_push( $openNew, $path[$i] );
						}
					}
							
					while( $path = array_pop( $openNew ) )
					{
						array_push( $this->openTags, $path );
						$xml	.=	str_repeat( "\t", $options["depth"] );
						$xml	.=	"<path name=\"".$path."\">".$options["nl"];
						$options["depth"]++;
					}

					$xml	.=	$this->createTag( $tag, $value, $options );
					
					break;
			}
		}

		//	close all open tags
		while( $open = array_pop( $this->openTags ) )
		{
			$options["depth"]--;
			$xml	.=	str_repeat( "\t", $options["depth"] );
			$xml	.=	"</path>".$options["nl"];
		}
		$xml		.=	"</configuration>";

		return	$xml;
	}

/**
*	create configValue tag
*
*	@access	private
*	@param	string	$name 	name attribute of the tag
*	@param	mixed	$value 	value of the tag
*	@return	string	$tag	xml representation of the tag
*/
	function	createTag( $name, $value, $options )
	{
		if( $name )
			$atts["name"]	=	$name;

		if( is_bool( $value ) )
		{
			$atts["type"]	=	"bool";
			$value			=	$value ? "true" : "false";
		}
		elseif( is_float( $value ) )
			$atts["type"]	=	"float";
		elseif( is_int( $value ) )
			$atts["type"]	=	"int";
		elseif( is_array( $value ) )
			$atts["type"]	=	"array";
		elseif( is_string( $value ) )
			$atts["type"]	=	"string";

		$tag	=	"";
		if( $options["mode"] == "pretty" )
			$tag	.=	str_repeat( "\t", $options["depth"] );
		$tag	.=	"<configValue";

		if( is_array( $atts ) )
		{
			reset( $atts );
			while( list( $att, $val ) = each( $atts ) )
				$tag	.=	" ".$att."=\"".$val."\"";
		}
		
		if( !$value )
		{
			$tag	.=	"/>".$options["nl"];
		}
		else
		{
			$tag	.=	">";

			if( is_array( $value ) )
			{
				$options["depth"]++;

				reset( $value );
				$tag	.=	$options["nl"];
				while( list( $key, $val ) = each( $value ) )
				{
					if( is_int( $key ) )
						unset( $key );
					$tag	.=	$this->createTag( $key, $val, $options );
				}

				$options["depth"]--;
				if( $options["mode"] == "pretty" )
					$tag	.=	str_repeat( "\t", $options["depth"] );
			}
			else
				$tag	.=	$this->replaceXMLSpecialchars( $value );

			$tag	.=	"</configValue>".$options["nl"];
		}
		return	$tag;
	}

/**
*	create a php representation of the current config
*
*	@access	private
*	@return	string	$php	php representation
*/
	function	buildPHPConfigFile( $options )
	{
		$varname			=	isset( $options["varname"] ) ? $options["varname"] : "config";

		$config				=	$this->conf;
		ksort( $config );
		reset( $config );

		$php		=	"<?PHP\n// Configuration generated by patConfiguration\n\n";
		$php		.=	"\$".$varname." = array();\n";
		
		while( list( $key, $value ) = each( $config ) )
		{
			$php	.=	$this->getPHPConfigLine( "\$".$varname."[\"".$key."\"]", $value );
		}

		$php		.=	"?>";
		return	$php;
	}

/**
*	build one line in the php config file
*
*	@access	private
*	@param	string	$prefix		variable name and array index
*	@param	mixed	$value		value of the config option
*	@return	string	$line		on line of php code
*/
	function	getPHPConfigLine( $prefix, $value )
	{
		if( is_bool( $value ) )
			$value			=	$value ? "true" : "false";
		elseif( is_string( $value ) )
			$value			=	"\"".addslashes( $value )."\"";

		if( is_array( $value ) )
		{
			$line		=	$prefix." = array();\n";;
			reset( $value );
			while( list( $key, $val ) = each( $value ) )
			{
				if( !is_int( $key ) )
					$key	=	"\"".$key."\"";
				$line	.=	$this->getPHPConfigLine( $prefix."[".$key."]", $val );
			}
			return	$line;
		}
		else
			return	$prefix." = ".$value.";\n";
	}
	
/**
*	add an extension
*
*	@access	public
*	@param	object patConfigExtension	&$ext	extension that should be added
*	@param	string						$ns		namespace for this extension (if differs from default ns)
*/	
	function	addExtension( &$ext, $ns = "" )
	{
		if( $ns == "" )
			$ns	=	$ext->getDefaultNS();

		$ext->setConfigReference( $this );
			
		$this->extensions[$ns]	=	&$ext;
	}

/**
*	handle start element
*	if the start element contains a namespace calls the eppropriate handler
*	
*	@param	int		$parser		resource id of the current parser
*	@param	string	$name		name of the element
*	@param	array	$attributes	array containg all attributes of the element
*/
	function	startElement( $parser, $name, $attributes )
	{
		//	separate namespace and local name
		$tag	=	explode( ":", $name );

		//	check if namespace exists and an extension for the ns exists
		if( count( $tag ) == 2 && isset( $this->extensions[$tag[0]] ) )
		{
			array_push( $this->nsStack, $tag[0] );
			$this->extensions[$tag[0]]->startElement( $parser, $tag[1], $attributes );
		}
		//	No namespace => handle internally
		else
		{
			array_push( $this->nsStack, false );
			switch( strtolower( $name ) )
			{
				//	configuration
				case	"configuration":
					break;

				//	define
				case	"define":
					if( !isset( $attributes["type"] ) )
						$attributes["type"]		=	"string";

					//	define a new tag
					if( isset( $attributes["tag"] ) )
					{
						$tag	=	$attributes["tag"];
						if( !isset( $attributes["name"] ) )
						{
							$tagName		=	$attributes["tag"];
							$nameAttribute	=	NULL;
						}
						else
						{
							switch( $attributes["name"] )
							{
								case	"_none":
									$tagName	=	NULL;
									$nameAttribute	=	NULL;
									break;
								case	"_attribute":
									$tagName		=	"_attribute";
									$nameAttribute	=	$attributes["attribute"];
									break;
								default:
									$tagName		=	$attributes["name"];
									$nameAttribute	=	NULL;
									break;
							}
						}

						$this->defaultTypes[$tag]	=	array(
																"type"	=>	$attributes["type"],
																"name"	=>	$tagName
															);
						if( $nameAttribute )
							$this->defaultTypes[$tag]["nameAttribute"]	=	$nameAttribute;

						$this->lastDefindedTag	=	$tag;
					}
					elseif( isset( $attributes["attribute"] ) )
					{
						$tag	=	$this->lastDefindedTag;
						if( !isset( $this->defaultTypes[$tag]["attributes"] ) || !is_array( $this->defaultTypes[$tag]["attributes"] ) )
							$this->defaultTypes[$tag]["attributes"]	=	array();
						
						$this->defaultTypes[$tag]["attributes"][$attributes["attribute"]]	=	$attributes["type"];
					}
					break;

				case	"getconfigvalue":
					$this->appendData( $this->getConfigValue( $attributes["path"] ) );
					break;
					
				//	extension
				case	"extension":
					if( isset( $attributes["file"] ) )
					{
						$fpath	=	( $this->includeDir ) ? $this->includeDir."/".$attributes["file"] : $attributes["file"];
						include_once( $fpath );
					}
					if( isset( $attributes["name"] ) )
					{
						if( class_exists( $attributes["name"] ) )
						{
							//	create new extension
							$ext	=	new	$attributes["name"];
	
							//	get namespace
							if( isset( $attributes["ns"] ) )
								$ns	=	$attributes["ns"];
							else
								$ns	=	$ext->getDefaultNS();
					
							//	add extension
							$ext->setConfigReference( $this );
							$this->extensions[$ns]	=	$ext;
						}
					}
					break;

				case	"xinc":
					//	include a single file
					if( isset( $attributes["href"] ) )
					{
						$file		=	$this->getFullPath( $attributes["href"] );
						
						##
						array_push( $this->externalFiles, $file );
						$this->parseXMLFile( $file );
					}
					//	include a directory
					elseif( isset( $attributes["dir"] ) )
					{
						if( !isset( $attributes["extension"] ) )
							$attributes["extension"]	=	"xml";

						$dir		=	$this->getFullPath( $attributes["dir"] );
						$files		=	$this->getFilesInDir( $dir, $attributes["extension"] );
						reset( $files );
						foreach( $files as $file )
						{
							array_push( $this->externalFiles, $file );
							$this->parseXMLFile( $file );
						}
					}
					
					break;

				//	path
				case	"path":
					$this->addToPath( $attributes["name"] );
					break;

				//	Config Value Tag found
				case	"configvalue":

					//	store name and type of value
					$val	=	@array(	"type"		=>	$attributes["type"],
										"name"		=>	$attributes["name"] );
										
					$this->valDepth	=	array_push( $this->valStack, $val );
					break;

				//	any other tag found
				//	=> use as path
				default:
					if( isset( $this->defaultTypes[$name] ) )
					{
						$type		=	$this->defaultTypes[$name]["type"];

						$tagName	=	$this->defaultTypes[$name]["name"];
						if( $tagName == "_attribute" )
						{
							$tagName	=	$attributes[$this->defaultTypes[$name]["nameAttribute"]];
						}
						
						//	store name and type of value
						$val	=	array(	"type"		=>	$type,
											"name"		=>	$tagName );
											
						if( isset( $this->defaultTypes[$name]["attributes"] ) && is_array( $this->defaultTypes[$name]["attributes"] ) )
						{
							$value	=	array();
							foreach( $this->defaultTypes[$name]["attributes"] as $name => $type )
							{
								if( isset( $attributes[$name] ) )
									$value[$name]	=	$this->convertValue( $attributes[$name], $type );
							}
							$val["value"]	=	$value;
						}

						$this->valDepth	=	array_push( $this->valStack, $val );
					}
					else
					{
						$this->addToPath( $name );
					}
					break;
			}
		}
	}

	
/**
*	handle end element
*	if the end element contains a namespace calls the eppropriate handler
*	
*	@param	int		$parser		resource id of the current parser
*	@param	string	$name		name of the element
*/
	function	endElement( $parser, $name )
	{
		//	remove namespace from stack
		array_pop( $this->nsStack );

		//	separate namespace and local name
		$tag	=	explode( ":", $name );

		//	check if namespace exists and an extension for the ns exists
		if( count( $tag ) == 2 && isset( $this->extensions[$tag[0]] ) )
		{
			$this->extensions[$tag[0]]->endElement( $parser, $tag[1] );
		}
		//	No namespace => handle internally
		else
		{
			switch( strtolower( $name ) )
			{
				//	configuration / extension
				case	"configuration":
				case	"getconfigvalue":
				case	"extension":
					break;

				case	"define":
					break;

				//	path
				case	"path":
					$this->removeLastFromPath();
					break;

				//	config value
				case	"configvalue":

					//	get last name and type
					$val	=	array_pop( $this->valStack );
									
					//	decrement depth, as one tag was removed from
					//	stack
					$this->valDepth--;

					//	if no value was found (e.g. other tags inside)
					//	use CDATA that was found between the tags
					if( !isset( $val["value"] ) )
						$val["value"]	=	$this->getData();
						
					$this->setTypeValue( $val["value"], $val["type"], $val["name"] );
					
					break;

				//	Any other tag
				default:
					if( isset( $this->defaultTypes[$name] ) )
					{
						//	get last name and type
						$val	=	array_pop( $this->valStack );
										
						//	decrement depth, as one tag was removed from
						//	stack
						$this->valDepth--;
	
						//	if no value was found (e.g. other tags inside)
						//	use CDATA that was found between the tags
						if( !isset( $val["value"] ) )
							$val["value"]	=	$this->getData();
							
						$this->setTypeValue( $val["value"], $val["type"], $val["name"] );
					}
					else
					{
						//	if data was found => store it
						if( $data = $this->getData() )
							$this->setTypeValue( $data );

						//	shorten path
						$this->removeLastFromPath();
					}
					break;		
			}
		}
	}
	
/**
*	handle character data
*	if the character data was found between tags using namespaces, the appropriate namesapce handler will be called
*	
*	@param	int		$parser		resource id of the current parser
*	@param	string	$data		character data, that was found		
*/
	function	characterData( $parser, $data )
	{
		if( trim( $data ) )
			$this->data	.=	$data;
	}

/**
*	add element to path
*
*	@access	private
*	@param	string	$key	element that should be appended to path
*/
	function	addToPath( $key )
	{
		array_push( $this->path, $key );
	}
	
/**
*	remove last element from path
*
*	@access	private
*/
	function	removeLastFromPath()
	{
		array_pop( $this->path );
	}

/**
*	set value for the current path
*
*	@access	private
*	@param	mixed	$value	value that should be set
*/
	function	setValue( $value )
	{
		$string	=	implode( ".", $this->path );
		$this->conf[$string]			=	$value;

		$this->currentConf[$string]		=	$value;
	}

/**
*	returns the current data between the open tags
*	data can be anything, from strings, to arrays or objects
*
*	@access	private
*	@return	mixed	$value	data between text
*/

	function	getData()
	{
		$data		=	$this->data;
		//	delete the data before returning it
		$this->data	=	NULL;
		return	$data;
	}

/**
*	append Data to the current data
*
*	@param	mixed	$data	data to be appended
*/
	function	appendData( $data ) 
	{
		if( is_string( $this->data ) )
		{
			//	append string
			if( is_string( $data ) )
				$this->data		.=		$data;
			else
				$this->data		=		array( $this->data, $data );
		}
		elseif( is_array( $this->data ) )
		{
			//	append string
			if( is_array( $data ) )
				$this->data	=	array_merge( $this->data, $data );
			else
				$this->data[]		=		$data;
		}
		else
			$this->data				=		$data;	
	}
	
/**
*	convert a value to a certain type ans set it for the current path
*
*	@access	private
*	@param	mixed	$value	value that should be set
*	@param	string	$type	type of the value (string, bool, integer, double)
*/
	function	setTypeValue( $value, $type = "leave", $name = "" )
	{
		//	convert value
		$value	=	$this->convertValue( $value, $type );

		//	check, if there are parent values
		//	insert current value into parent array
		if( count( $this->valStack ) > 0 )
		{
			if( $name )
				$this->valStack[($this->valDepth-1)]["value"][$name]	=	$value;
			else
				$this->valStack[($this->valDepth-1)]["value"][]			=	$value;
		}

		//	No valuestack
		else
		{
			if( $this->nsStack[( count( $this->nsStack )-1 )] )
				$this->appendData( $value );
			else
			{
				if( $name )
					$this->addToPath( $name );
	
				$this->setValue( $value );
	
				if( $name )
					$this->removeLastFromPath();

				//	clear all found CDATA
				$this->data	=	"";
			}
		}
	}

/**
*	convert a string variable to any type
*
*	@access	private
*	@param	string	$value	value that should be converted
*	@param	string	$type	type of the value (string, bool, integer, double)
*	@return	mixed	$value
*/	
	function	convertValue( $value, $type = "string" )
	{
		switch( $type )
		{
			//	string
			case	"string":
				settype( $value, "string" );
				break;

			//	boolean
			case	"boolean":
			case	"bool":
				if( $value == "true" || $value == "yes" || $value == "on" )
					$value	=	true;
				else
					$value	=	false;
				break;

			//	integer
			case	"integer":
			case	"int":
				settype( $value, "integer" );
				break;

			//	double
			case	"float":
			case	"double":
				settype( $value, "double" );
				break;

			//	array
			case	"array":
				if( !is_array( $value ) )
				{
					if( trim( $value ) )
						$value	=	array( $value );
					else
						$value	=	array();
				}
				break;
		}
		return	$value;
	}
	
/**
*	returns a configuration value
*	if no path is given, all config values will be returnded in an array
*
*	@access	public
*	@param	string	$path	path, where the value is stored
*	@return	mixed	$value	value
*/
	function	getConfigValue( $path = "" )
	{
		if( $path == "" )
			return	$this->conf;

		if( strstr( $path, "*" ) )
		{
			$path		=	str_replace( ".", "\.", $path )."$";
			$path		=	"^".str_replace( "*", ".*", $path )."$";
			$values		=	array();
			reset( $this->conf );
			while( list( $key, $value ) = each( $this->conf ) )
			{
				if( eregi( $path, $key ) )
					$values[$key]	=	$value;
			}
			return	$values;
		}

		//	check wether a value of an array was requested
		if( $index	= strrchr( $path, "[" ) )
		{
			$path		=	substr( $path, 0, strrpos( $path, "[" ) );
			$index		=	substr( $index, 1, ( strlen( $index ) - 2 ) );
			$tmp		=	$this->getConfigValue( $path );
			
			return	$tmp[$index];
		}
		
		if( isset( $this->conf[$path] ) )
			return	$this->conf[$path];
		
		return	false;
	}
	
/**
*	set a config value
*	*
*	@access	public
*	@param	string	$path	path, where the value will be stored
*	@param	mixed	$value	value to store
*/
	function	setConfigValue( $path, $value, $type = "leave" )
	{
		$this->conf[$path]			=	$this->convertValue( $value, $type );
		$this->currentConf[$path]	=	$this->convertValue( $value, $type );
	}
	
/**
*	sets several config values
*
*	@access	public
*	@param	array	$values		assoc array containg paths and values
*/
	function	setConfigValues( $values )
	{
		if( !is_array( $values ) )
			return	false;
		reset( $values );
		while( list( $path, $value ) = each( $values ) )
			$this->setConfigValue( $path, $value );
	}
	
/**
*	clears a config value
*	if no path is given, the complete config will be cleared
*
*	@access	public
*	@param	string	$path	path, where the value is stored
*/
	function	clearConfigValue( $path = "" )
	{
		if( $path == "" )
		{
			$this->conf		=	array();
			return	true;
		}
		
		if( strstr( $path, "*" ) )
		{
			$path		=	str_replace( ".", "\.", $path )."$";
			$path		=	"^".str_replace( "*", ".*", $path )."$";
			$values		=	array();
			reset( $this->conf );
			while( list( $key, $value ) = each( $this->conf ) )
			{
				if( eregi( $path, $key ) )
					unset( $this->conf[$key] );
			}
			return	true;
		}

		if( !isset( $this->conf[$path] ) )
			return	false;
			
		unset( $this->conf[$path] );
		return	true;
	}


/*
*	parse an external entity
*
*	@param	int		$parser				resource id of the current parser
*	@param	string	$openEntityNames	space-separated list of the names of the entities that are open for the parse of this entity (including the name of the referenced entity)
*	@param	string	$base				currently empty string
*	@param	string	$systemId			system identifier as specified in the entity declaration
*	@param	string	$publicId			publicId, is the public identifier as specified in the entity declaration, or an empty string if none was specified; the whitespace in the public identifier will have been normalized as required by the XML spec
*/

	function	externalEntity( $parser, $openEntityNames, $base, $systemId, $publicId )
	{
		if( $systemId )
		{
			$file	=	( $this->configDir!="" ) ? $this->configDir."/".$systemId : $systemId; 
			array_push( $this->externalFiles, $file );
			$this->parseXMLFile( $file );
		}
		return	true;
	}

/**
*	calculates the full path of a file that should be included
*
*	@access	private
*	@param	string	$path
*	@return	string	$fullPath
*/
	function	getFullPath( $path )
	{
		if( strncmp( $path, "/", 1 ) != 0 )
		{
			if( !empty( $this->xmlFiles ) )
			{
				$currentFile	=	$this->xmlFiles[( count( $this->xmlFiles ) - 1 )];
				$fullPath		=	dirname( $currentFile ) . "/" . $path;
			}
		}
		//	absolute path
		else
		{
			$path		=	substr( $path, 1 );

			if( !empty( $this->configDir ) )
				$fullPath	=	$this->configDir."/". $path;
			else
				$fullPath	=	$path;
		}
		
		$realPath	=	realpath( $fullPath );
		if( empty( $realPath ) )
			$this->niceDie( "getFullPath", "Could not resolve full path for path: '".$path."' - please check the path syntax." );
		
		return	$realPath;
	}

/**
*	get all files in a directory
*
*	@access	private
*	@param	string	$dir
*	@param	string	$ext	file extension
*/
	function	getFilesInDir( $dir, $ext )
	{
		$files	=	array();
		if( !$dh = dir( $dir ) )
			return	$files;
			
		while( $entry = $dh->read() )
		{
			if( $entry == "." || $entry == ".." )
				continue;
			if( is_dir( $dir . "/" . $entry ) )
				continue;
			if( strtolower( strrchr( $entry, "." ) ) != ".".$ext )
				continue;
			array_push( $files, $dir. "/" . $entry );
		}

		return	$files;
	}
	
/**
*	parse an external xml file
*
*	@param	string	$file	filename, without dirname
*/

	function	parseXMLFile( $file )
	{
		$parser	= $this->createParser();
		
		if( !( $fp = @fopen( $file, "r" ) ) )
			$this->niceDie( "parseXMLFile", "Could not open XML file '".$file."'." );
			
		array_push( $this->xmlFiles, $file );

		flock( $fp, LOCK_SH );

		while( $data = fread( $fp, 4096 ) )
		{
		    if ( !xml_parse( $parser, $data, feof( $fp ) ) )
			{
				$message	=	sprintf(	"XML error: %s at line %d in file $file",
											xml_error_string( xml_get_error_code( $parser ) ),
											xml_get_current_line_number( $parser ) );
				$this->niceDie( "parseXMLFile", $message );
    		}
		}

		array_pop( $this->xmlFiles );
		
		flock( $fp, LOCK_UN );
		xml_parser_free( $parser );
	}
	
/**
*	create a parser
*
*	@return	object	$parser
*/

	function	createParser()
	{
		//	init XML Parser
		$parser	=	xml_parser_create();
		xml_set_object( $parser, $this );
		xml_set_element_handler( $parser, "startElement", "endElement" );
		xml_set_character_data_handler( $parser, "characterData" );
		xml_set_external_entity_ref_handler( $parser, "externalEntity" );

		xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, false );

		return	$parser;
	}
	
	
/**
*	replace XML special chars
*
*	@param	string	$string		string, where special chars should be replaced
*	@param	array	$table		table used for replacing
*	@return	string	$string		string with replaced chars
*/

	function	replaceXMLSpecialchars( $string, $table = array() )
	{
		if( empty( $table ) )
			$table	=	&$this->xmlSpecialchars;

		$string		=	strtr( $string, $table );

		return	$string;
	}

/**
*	generates a "nice" variant of die() with a few more interesting infos
*
*	@param	string	$method		method in which the error occurred
*	@param	string	$message	the error message to display
*/
	function	niceDie( $method, $message )
	{
		echo '<html><head><style>.text{font-family:verdana;color:#000000;font-size:12px;letter-spacing:-1px;}</style></head><body class="text">';
		echo '<b class="text">patConfiguration Error:</b><p>';
		echo '<table cellpadding="1" cellspacing="0" border="0">';
		echo '	<tr>';
		echo '		<td class="text"><b>Function</b></td>';
		echo '		<td class="text">&nbsp;:&nbsp;</td>';
		echo '		<td class="text">'.$method.'</td>';
		echo '	</tr>';
		echo '	<tr valign="top">';
		echo '		<td class="text"><b>Error</b></td>';
		echo '		<td class="text">&nbsp;:&nbsp;</td>';
		echo '		<td class="text">'.$message.'</td>';
		echo '	</tr>';
		echo '</table>';
		echo '</div></body></html>';
		exit;
	}
	
}
?>
