<?PHP
//ini_set('session.use_trans_sid', 0);
//ini_set('session.use_only_cookies', 1); 
/**
 *	patUser.php 
 *
 *	@author	Stephan Schmidt <schst@php-tools.net>
 *	@author	gERD Schaufelberger <gerd@php-tools.net>
 *
 *	$Id$
 *
 *	$Log$
 */

/**
 *	error code: no user matched the query
 */
define( "patUSER_NO_USER_FOUND", 1 );

/**
 *	error code: more than one user matror  the que y
 */
define( "patUSER_NO_UNIQUE_USER_FOUND", 2 );

/**
 *	error code: function requires a user name
 */
define( "patUSER_NEED_USERNAME", 10 );

/**
 *	error code: function requires a password
 */
define( "patUSER_NEED_PASSWD", 11 );

/**
 *	error code: user already exists
 */
define( "patUSER_USER_ALREADY_EXISTS", 12 );

/**
 *	error code: password incorrect
 */
define( "patUSER_PASSWD_MISMATCH", 13 );

/**
 *	error code: login for this user was diabled
 */
define( "patUSER_LOGIN_DISABLED", 14 );

/**
 *	error code: function requires a user id
 */
define( "patUSER_NEED_UID", 20 );

/**
 *	error code: function requires data
 */
define( "patUSER_NO_DATA_GIVEN", 30 );

/**
 *	error code: no primary key was found
 */
define( "patUSER_NO_PRIMARY_FOUND", 100 );

/**
 *	error code: data matched more than one row
 */
define( "patUSER_NO_UNIQUE_PRIMARY_FOUND", 101 );

/**
 *	error code: could not identify line in table
 */
define( "patUSER_COULD_NOT_IDENTIFY_LINE", 110 );

/**
 *	error code: insert is not allowed
 */
define( "patUSER_INSERT_NOT_ALLOWED", 120 );

/**
 *	error code: delete is not allowed
 */
define( "patUSER_DELETE_NOT_ALLOWED", 121 );

/**
*	error code: no data was changed (affected rows = 0)
*/
define( "patUSER_NO_DATA_CHANGED", 130 );

/**
 *	error code: table does not exist
 */
define( "patUSER_TABLE_DOES_NOT_EXIST", 140 );

/**
 *	error code: no group matched the query
 */
define( "patUSER_NO_GROUP_FOUND", 1001 );

/**
 *	error code: no unique group matched the query
 */
define( "patUSER_NO_UNIQUE_GROUP_FOUND", 1002 );

/**
 *	error code: function requires a group name
 */
define( "patUSER_NEED_GROUPNAME", 1010 );

/**
 *	error code: group already exists (when adding a group)
 */
define( "patUSER_GROUP_ALREADY_EXISTS", 1012 );

/**
 *	error code: function requires group id
 */
define( "patUSER_NEED_GID", 1020 );

/**
 *	error code: user already is in group
 */
define( "patUSER_ALREADY_JOINED_GROUP", 1050 );

/**
 *	error code: user is not in group
 */
define( "patUSER_NOT_IN_GROUP", 1051 );

/**
 *	error code: function requires user or group id
 */
define( "patUSER_NEED_ID", 1060 );

/**
 *	error code: function requires type of supplied id (user or group)
 */
define( "patUSER_NEED_ID_TYPE", 1061 );

/**
 *	error code: query had no result
 */
define( "patUSER_NO_DB_RESULT", 2000 );

/**
 *	user management class
 *
 *	patUser is a user management class, that helps you with authentication, groups 
 *	and permission. Furthermore it is useful to manage data (stored databases) 
 *	related to users and groups. Also patUser provides user statistics.
 *
 *	CAUTION: This version is based on PEAR:DB
 *	patDbc will no longer supported! - please switch to PEAR:DB
 *	patUser 2.1.x is the last version that supports patDbc.
 *
 *	@author		Stephan Schmidt <schst@php-tools.net>
 *	@author		gERD Schaufelberger <gerd@php-tools.net>
 *	@package	patUser
 *	@class		patUser
 *	@version	2.2.3
 *
 *
 */
	class	patUser
{
   /**
	*	information about the project
	*	@var	array	$systemVars
	*/
	var	$systemVars			=	array(
										"appName"		=>	"patUser",
										"appVersion"	=>	"2.2.3",
										"author"		=>	array(
																	"Stephan Schmidt <schst@php-tools.net>",
																	"Gerd Schaufelberger <gerd@php-tools.net>"
																 )
									);

   /**
	*	error messages
	*	@var	array	$errorMessages
	*/
	var	$errorMessages	=	array(
									1		=>	"No user found.",
									2		=>	"No unique user found.",
									10		=>	"Username is required.",
									11		=>	"Password is required.",
									12		=>	"User already exists.",
									13		=>	"Passwords do not match.",
									14		=>	"You are not allowed to login.",
									20		=>	"User Id is required.",
									30		=>	"Data is needed.",
									100		=>	"No primary key value found.",
									101		=>	"No unique primary key value found.",
									110		=>	"Dataset could not be identified.",
									120		=>	"Insert is not allowed.",
									121		=>	"Delete is not allowed.",
									130		=>	"Data was not changed.",
									140		=>	"Table does not exist.",
									1001	=>	"No group found.",
									1002	=>	"No unique group found.",
									1010	=>	"Name of group is required.",
									1012	=>	"Group already exists.",
									1020	=>	"Group Id is required.",
									1050	=>	"User already is in group.",
									1051	=>	"User is not in group.",
									1060	=>	"Need user or group id.",
									1061	=>	"No id type specified.",
									2000	=>	"No database result id returned."
								);
   /**
	*	default realm for HTTP authentication
	*	@var	string	$realm
	*/
	var	$realm				=	"patUser Login Required";

   /**
	*	maximum login attempts for a session
	*	@var	integer	$maxLoginAttempts
	*/
	var	$maxLoginAttempts	=	0;

   /**
	*	flag to indicate, whether template is used for output
	*	@var	string	$realm
	*/
	var	$useTemplate	=	false;

   /**
	*	locations (table/fieldname) of fields
	*	@var	array	$fieldLocs
	*/
	var	$fieldLocs		=	array();

   /**
	*	Table that stores the authentication data
	*	@var	string	$authTable
	*/
	var	$authTable		=	"users";

   /**
	*	state of user/session: authenticated or not
	*	@var	bool	$authenticated
	*/
	var	$authenticated	=	false;

   /**
	*	Fieldnames in the athentication table
	*	@var	array	$authFields
	*/
	var	$authFields		=	array(	"primary"	=>	"uid",
									"username"	=>	"username",
									"passwd"	=>	"passwd" );

   /**
	*	Table that stores the group data
	*	@var	string	$groupTable
	*/
	var	$groupTable		=	"groups";

   /**
	*	Fieldnames in the group table
	*	@var	array	$groupFields
	*/
	var	$groupFields	=	array(	"primary"	=>	"gid",
									"name"		=>	"name" );

   /**
	*	Table that stores the user - group relations
	*	@var	string	$relTable
	*/
	var	$relTable		=	"usergroups";

   /**
	*	Fieldnames of the user-group relation table
	*	@var	array	$relFields
	*/
	var	$relFields		=	array(	"uid"		=>	"uid",
									"gid"		=>	"gid" );

   /**
	*	Table that stores the permisssions
	*	@var	string	$permTable
	*/
	var	$permTable		=	"permissions";

   /**
	*	Fieldnames in the permissions table
	*	@var	array	$permFields
	*/
	var	$permFields		=	array(	"id"		=>	"id",
									"id_type"	=>	"id_type",
									"perms"		=>	"perms" );

   /**
	*	Possible permissions
	*	@var	array	$perms
	*/
	var	$perms			=	array(	1			=>	"read",
									2			=>	"delete",
									4			=>	"modify",
									8			=>	"add" );
   /**
	*	table to convert permissions
	*	@var	array	$permsConv
	*/
	var	$permsConv		=	array();

   /**
	*	all statistic options
	*	@var	array	$stats
	*/
	var	$stats			=	array();

   /**
	*	flag to indicate whether sessions are used
	*	@var	boolean	$useSessions
	*/
	var	$useSessions	=	false;

   /**
	*	flag to indicate whether permssions are available
	*	@var	boolean	$usePermissions
	*	@see	setPermTable()
	*/
	var	$usePermissions	=	false;

   /**
	*	name of the global variable used for sessions
	*	@var	string	$sessionVar
	*/
	var	$sessionVar		=	"patUserData";

   /**
	*	name of the sequence for user ids
	*	@var	string	$userIdSequence
	*/
	var	$userIdSequence		=	"patUserSequence";

   /**
	*	variable to store the patTemplate object (false if no template is used)
	*	@var	boolean	$tmpl
	*/
	var	$tmpl			=	false;

   /**
	*	authentication handler object (false if no handler is used)
	*
	*	The authentication handler object will be used by {@see requireAuthentication()} 
	*	to recieve data used for authentication. Therefore the handler-object must implment 
	*	a method called: patUserGetAuthData().
	*
	*	Moreover patUser supports other optional methods of the auth handler object:
	*	- patUserSetUid: sends the user-id if user is logged in.
	*	- patUserSetRealm: send the realm-string when no user is logged in
	*	- patUserSetErrors: sends patUser-errors if login failed
	*
	*	@var	mixed	$tmpl
	*	@see	setAuthHandler(), reuquireAuthentication(), $useTemplate
	*/
	var	$authHandler	=	false;

   /**
	*	name of the global variable that indicates the action in the request
	*	@var	string	$actionVar
	*/
	var	$actionVar		=	"patUserAction";

   /**
	*	filename of the template used for login
	*	@var	string	$loginTemplate
	*/
	var	$loginTemplate	=	"patUserLogin.tmpl";
	
   /**
	*	filename of the template used for unauthorized users
	*	@var	string	$unauthorizedTemplate
	*/
	var	$unauthorizedTemplate	=	"patUserUnauthorized.tmpl";
	
   /**
	*	URL to redirect unauthorized users to
	*	@var	string	$unauthorizedURL
	*/
	var	$unauthorizedURL		=	false;

   /**
	*	all dbcs
	*	@var	array	$dbcs
	*/
	var	$dbcs			=	array();

   /**
	*	all tables that are used
	*	@var	array	$tables
	*/
	var	$tables			=	array();

   /**
	*	all codes of the errors that happened while processing
	*	@var	array	$errors
	*/
	var	$errors			=	array();

   /**
	*	variable names that should be ignored by getSelfUrl()
	*	@var	array	$ignoreVars
	*/
	var	$ignoreVars		=	array( "patUserAction" );

   /**
	*	whether user stats have been updated.
	*	@var	string	$statsUpdated
	*/	
	var $statsUpdated	=	false;

   /**
	*	name of function, that should be used for passwd encryption
	*	@var	string	$cryptFunction
	*/
	var	$cryptFunction	=	false;
	
   /**
    *	create new user object
	*
	*	constructor for use with PHP4
	*
	*	@access	public
	*	@param	boolean		$useSessions	flag to indicate that sessions should be used
	*	@param	string		$sessionVar		name of the session var used for sessions
	*	@param	string		$userIdSequence	name of the sequence for retrieving the next user id
	*	@see	__construct()
	*/
	function	patUser( $useSessions = true, $sessionVar = "patUserData", $userIdSequence = "patUserSequence" )
	{
		$this->__construct( $useSessions, $sessionVar, $userIdSequence );
	}
	
   /**
	*	create new user object
	*
	*	constructor of patUser
	*
	*	@access	public
	*	@param	boolean		$useSessions	flag to indicate that sessions should be used
	*	@param	string		$sessionVar		name of the session var used for sessions
	*	@param	string		$userIdSequence	name of the sequence for retrieving the next user id
	*/
	function	__construct( $useSessions = true, $sessionVar = "patUserData", $userIdSequence = "patUserSequence" )
	{
		$this->useSessions		=	$useSessions;
		$this->sessionVar		=	$sessionVar;
		$this->userIdSequence	=	$userIdSequence;

		if( $this->useSessions )
		{
			if(!isset($_SESSION))
			{
				ini_set('session.use_only_cookies', 1);
				ini_set("url_rewriter.tags","");
				ini_set('session.use_trans_sid', false);
				session_start();
			}

			//	check, whether register globals is enabled
			if( ini_get( "register_globals" ) )
			{
				session_register( $this->sessionVar );
				if( !isset( $GLOBALS[$this->sessionVar] ) )
					$GLOBALS[$this->sessionVar]		=	array();
				$this->sessionData		=	&$GLOBALS[$this->sessionVar];
			}
			//	register globals is off, session_register is useless :-(
			else
			{
				if( isset( $_SESSION ) )
				{
					if( !isset( $_SESSION[$this->sessionVar] ) )
						$_SESSION[$this->sessionVar]	=	array();
					$this->sessionData		=	&$_SESSION[$this->sessionVar];
				}
				else
				{
					if( !isset( $GLOBALS["HTTP_SESSION_VARS"][$this->sessionVar] ) )
						$GLOBALS["HTTP_SESSION_VARS"][$this->sessionVar]	=	array();
					$this->sessionData		=	&$GLOBALS["HTTP_SESSION_VARS"][$this->sessionVar];
				}
			}
		}
	}
	
   /**
	*	set authentication realm
	*
	*	@access	public
	*	@param	string	$realm	authentication realm
	*/
	function	setRealm( $realm )
	{
		$this->realm	=	$realm;
	}

   /**
	*	set maximum amount of login attempts
	*
	*	@access	public
	*	@param	integer	$maxLoginAttempts
	*/
	function	setMaxLoginAttempts( $maxLoginAttempts )
	{
		$this->maxLoginAttempts	=	$maxLoginAttempts;
	}

   /**
	*	set URL to redirect unauthorized users to
	*
	*	@access	public
	*	@param	string	$url
	*/
	function	setUnauthorizedURL( $url )
	{
		$this->unauthorizedURL	=	$url;
	}

   /**
	*	set dsn that contains authentication information
	*
	*	This method is DEPRECATED use setAuthDbc() instead.
	*	The dsn describes the database connection which will be used to recieve the 
	*	authentication date. The dsn will be used by PEAR::DB
	*
	*	@access	public
	*	@param	string	$dsn		datasource name for authorization database
	*	@param	boolean	$persistent	flag to indicate, whether a persistent connection should be established
	*	@return object	$authDbc	returns the authentication database object PEAR::DB
	*	@deprecated	since version 2.2.3, please use setAutDbc() instead
	*	@see	setAuthDbc()
	*/
	function	setAuthDsn( $dsn, $persistent = false )
	{
		$this->authDsn	=	$dsn;
		$this->authDbc	=	DB::connect( $dsn, $persistent );
		if( DB::isError( $this->authDbc ) )
		{
			return	$this->authDbc;
		}
		return	true;
	}

   /**
	*	set dbc that contains auth info
	*
	*	Set the database (PEAR:DB) object that will be used to gain access to the 
	*	authentication data
	*
	*	@access	public
	*	@param	mixed	$dbc	dsn-string or PEAR::DB object
	*	@param	boolean	$persistent	flag to indicate, whether a persistent connection should be established
	*	@return boolean	$result		true on success, DB::Error-object of initialisation failed
	*	@see setAuthDsn()
	*/
	function	setAuthDbc( $dbc, $persistent = false )
	{
		if( is_object( $dbc ) )
		{
			if( is_subclass_of( $dbc, "db_common" ) )
			{
				$this->authDsn	=	false;
				$this->authDbc	=	$dbc;
				return true;
			}

			die( "patUser fatal error: The dsn must be either a PEAR::DB-object or a dsn-string" );
		}
		// otherwise setup dbc
		$this->authDsn	=	$dbc;
		$this->authDbc	=	DB::connect( $dbc, $persistent );
		if( DB::isError( $this->authDbc ) )
		{
			return	$this->authDbc;
		}
		
		return true;
	}

   /**
	*	set tablename that contains auth info
	*
	*	This method usually is called during setup the patUser-object
	*
	*	@access	public
	*	@param	string	$table	name of the table that contains auth data
	*	@see	setAuthFields(), $authTable 
	*/
	function	setAuthTable( $table )
	{
		$this->authTable	=	$table;
	}

   /**
	*	set fieldnames that contain auth info
	*
	*	This method usually is called during setup the patUser-object
	*
	*	@access	public
	*	@param	array	$fields		assoc array containing fieldnames of the authtable
	*	@see	setAuthTable(), $authFields
	*/
	function	setAuthFields( $fields  )
	{
		if( !is_array( $fields ) )
			return	false;

		reset( $fields );
		while( list( $key, $value ) = each( $fields ) )
			$this->authFields[$key]	=	$value;
	}

   /**
	*	get fieldnames that contain auth info
	*
	*	@access	public
	*	@return	array	$fields		assoc array containing fieldnames of the authtable
	*	@see	setAuthFields(), $authFields
	*/
	function	getAuthFields( )
	{
		return $this->authFields;
	}

	
   /**
	*	set tablename that contains group info
	*
	*	This method usually is called during setup the patUser-object
	*
	*	@access	public
	*	@param	string	$table	name of the table that contains group data
	*	@see setGroupFields(), $groupTable
	*/
	function	setGroupTable( $table )
	{
		$this->groupTable	=	$table;
	}

   /**
	*	set fieldnames that contain group info
	*
	*	This method usually is called during setup the patUser-object
	*
	*	@access	public
	*	@param	array	$fields		assoc array containing fieldnames of the grouptable
	*	@see setGroupTable(), $groupFields
	*/
	function	setGroupFields( $fields  )
	{
		if( !is_array( $fields ) )
			return	false;

		reset( $fields );
		while( list( $key, $value ) = each( $fields ) )
			$this->groupFields[$key]	=	$value;
	}

   /**
	*	get fieldnames that contain group info
	*
	*	This method usually is called during setup the patUser-object
	*
	*	@access	public
	*	@return	array	$fields		assoc array containing fieldnames of the grouptable
	*	@see	setGroupFields(), $groupFields
	*/
	function	getGroupFields( )
	{
			return	$this->groupFields;
	}

   /**
	*	set tablename that contains user group relations
	*
	*	This method usually is called during setup the patUser-object
	*
	*	@access	public
	*	@param	string	$table	name of the table that contains user group relations
	*	@see setGroupRelFields(), $relTable
	*/
	function	setGroupRelTable( $table )
	{
		$this->relTable	=	$table;
	}

   /**
	*	set fieldnames that are used in the user group relations
	*
	*	This method usually is called during setup the patUser-object
	*
	*	@access	public
	*	@param	array	$fields		assoc array containing fieldnames of the user group relation table
	*	@see setGroupRelTable(), $groupRelFields
	*/
	function	setGroupRelFields( $fields  )
	{
		if( !is_array( $fields ) )
			return	false;

		reset( $fields );
		while( list( $key, $value ) = each( $fields ) )
			$this->groupRelFields[$key]	=	$value;
	}

   /**
	*	set tablename that contains permissions
	*
	*	This method usually is called during setup the patUser-object
	*
	*	@access	public
	*	@param	string	$table	name of the table that contains permissions
	*	@see 	setPermFields, $permTable
	*/
	function	setPermTable( $table )
	{
		$this->usePermissions	=	true;
		$this->permTable		=	$table;
	}

   /**
	*	get tablename that contains permissions
	*
	*	@access	public
	*	@return	string	$table	name of the table that contains permissions
	*	@see	setPermTable(), $permTable
	*/
	function	getPermTable()
	{
		return $this->permTable;
	}

	
   /**
	*	set fieldnames that are used in the permissions
	*
	*	This method usually is called during setup the patUser-object
	*
	*	@access	public
	*	@param	array	$fields		assoc array containing fieldnames of the permission table
	*	@see	$setPermTable(), $permFields
	*/
	function	setPermFields( $fields  )
	{
		$this->usePermissions	=	true;
		
		if( !is_array( $fields ) )
			return	false;

		reset( $fields );
		while( list( $key, $value ) = each( $fields ) )
			$this->permFields[$key]	=	$value;
	}

   /**
	*	set authentication handler object
	*
	*	the authentication handler supplies patUser with authentication data and handles 
	*	logins (failed and succeded). This allows patUser to work without http-auth or patTemplate. 
	*
	*	The authHandler must implement at least one method: patUserGetAuthData.
	*	This method will be called if the authentication data is needed. Optional patUser
	*	supports more methods {@see $authHandler}.
	*
	*	@access	public
	*	@param	object 	&$authHandler	Object that handles authentication data
	*	@return	boolean $result	true if authHandler seams to be ok
	*	@see $authHandler, $useTemplate
	*/
	function	setAuthHandler( &$handler )
	{
		if( !method_exists( $handler, "patUserGetAuthData" ) )
			die( "patUser fatal error: The authHandler-object requires a method named \"patUserGetAuthData\"" );

		$this->authHandler		=	&$handler;

		return true;
	}

   /**
	*	set template object
	*	the template object is used for displaying login / logout screens
	*
	*	@access	public
	*	@param	object patTemplate	&$tmpl		patTemplate Object
	*/
	function	setTemplate( &$tmpl )
	{
		$this->tmpl		=	&$tmpl;

		//	check whether template is patTemplate object
		if( get_class( $this->tmpl ) == "patTemplate" || get_parent_class( $this->tmpl ) == "patTemplate" )
			$this->useTemplate	=	true;
	}

   /**
	*	add a statistic option
	*
	*	stats include: first_login, last_login, count_logins, count_pages, time_online
	*
	*	@access	public
	*	@param	string	$statistic	name of the statistic that should be tracked
	*	@param	string	$field		name of the field that should store the stats (if it differs from the name)
	*	@see $stats
	*/
	function	addStats( $statistic, $field = "" )
	{
		if( $field == "" )
			$field		=	$statistic;

		$this->stats[$statistic]	=	$field;
	}
	
   /**
	*	set the location of a field
	*
	*	@access	public
	*	@param	string	$name		name of the field
	*	@param	string	$table		name of the table that stores the field (use "authTable" if its stored in the authtable)
	*	@param	string	$field		name of the field in the table (leave of if identical with name)
	*/
	function	addFieldLocation( $name, $table = "authTable", $field = "" )
	{
		if( $field == "" )
			$field		=	$name;

		$this->fieldLocs[$name]	=	array( "table" => $table, "field" => $field );
	}

   /**
	*	set function that is used for passwd encryption
	*
	*	@access	public
	*	@param	string	$cryptFunction	name of the encryption function
	*/
	function	setCryptFunction( $cryptFunction = "crypt" )
	{
		if( !function_exists( $cryptFunction ) )
			return	false;

		$this->cryptFunction	=	$cryptFunction;
		return	true;
	}
	
   /**
	*	authenticate a user
	*
	*	This method is used to log in. This method uses the $data-parameter and tries to 
	*	find a matching user. Furthermore it checks if the user is allowed to login (uses 
	*	the "nologin" field in the auth-table). Is the user was logged in successfully, the 
	*	statistics will be updated (if the statistic-function is enabled).
	*
	*	@access	public
	*	@param	array	$data	array containing data for authentication (username, passwd)
	*	@return	mixed	$uid	userid on success, false othwerwise
	*	@see	isAuthenticated(), requireAuthentication()
	*/
	function	authenticate( $data = array() )
	{
		if( $this->isAuthenticated() )
			return	$this->uid;

		$data[$this->authFields["passwd"]]	=	$this->encryptPasswd( $data[$this->authFields["passwd"]] );

		if( $uid = $this->identifyUser( $data ) )
		{
			if( isset( $this->authFields["nologin"] ) )
			{
				list( $data )	=	$this->getUserData( array( "uid" => $uid, "fields" => array( $this->authFields["nologin"] ) ) );
				if( $data[$this->authFields["nologin"]] )
				{
					$this->setError( patUSER_LOGIN_DISABLED );
					return	false;
				}
			}

			$this->setUid( $uid );

			if( isset( $this->stats["current_login"] ) )
			{
				$this->storeSessionValue( $this->stats["current_login"], time() );
			}
			
			//	Update statistics
			if( isset( $this->stats["last_login"] ) || isset( $this->stats["count_logins"] ) )
			{
				$set		=	array();
				if( $this->stats["last_login"] )
					$set[]	=	$this->stats["last_login"]."='".date( "Y-m-d H:i:s", time() )."'";
				if( $this->stats["count_logins"] )
					$set[]	=	$this->stats["count_logins"]."=".$this->stats["count_logins"]."+1";

				$query		=	"UPDATE ".$this->authTable." SET ".implode( ",", $set )." WHERE ".$this->authFields["primary"]."='".$this->uid."'";
				$result		=	$this->authDbc->query( $query );
				if( DB::isError( $result ) )
				{
					return	false;
				}
			}
			return	(integer) $uid;
		}
		return	false;
	}

   /**
	*	check, whether a user is already authenticated
	*
	*	if auth is done via session, all important vars will be fetched. 
	*
	*	@access	public
	*	@return	bool	$success	treu, if user is authenticed, false otherwise
	*/
	function	isAuthenticated()
	{
		if( $this->authenticated )
			return	true;

		//	using sessions to keep auth data?
		if( $this->useSessions )
		{
			$auth		=	$this->getSessionValue( "patAuthenticated" );
			//	user already authenticated
			if( $auth )
			{
				$this->authenticated	=	true;
				$this->uid				=	$this->getSessionValue( "patUid" );
				return	true;
			}
		}
		return	false;		
	}

   /**
	*	an easy way to recieve the id if the current user.
	*
	*	return	the user id of the current user
	*
	*	@access	public
	*	@return	int		$uid	user id
	*	@uid
	*/
	function	getUid()
	{
		if( $this->isAuthenticated() )
			return	(int)$this->uid;
		else
			return	false;
	}

   /**
	*	set the user id of the user
	*
	*	@access	private
	*	@param	int		$uid	user id
	*	@see $uid
	*/
	function	setUid( $uid )
	{
		$this->uid				=	$uid;
		$this->authenticated	=	true;

		if( $this->useSessions )
		{
			$this->storeSessionValue( "patAuthenticated", true );
			$this->storeSessionValue( "patUid", $this->uid );
		}
	}

   /**
	*	clear the user id
	*
	*	@access	private
	*	@see $authenticated
	*/
	function	clearUid()
	{
		unset( $this->uid );
		$this->authenticated	=	false;

		if( $this->useSessions )
		{
			$this->storeSessionValue( "patAuthenticated", false );
			$this->clearSessionValue( "patUid" );
						
			if( isset( $this->stats["current_login"] ) )
				$this->clearSessionValue( $this->stats["current_login"] );
		}
	}
	
   /**
	*	require	an authenticated user
	*
	*	Use this function, if you want to be sure, that a user is logged in. If there is no user
	*	yet, this method tries to get the authendication data (usually username and password). This 
	*	can be controlled by the "mode"-parameter. 
	*	Set mode to:
	*	- "displayLogin": use patTemplate to display a login screen and search the POST-variables for
	*	  username and password. If there is no user, the programme will exit. This si the default mode.
	*	- "exit": This very simple mode just exits the script, if no user is logged in. 
	*	- "callAuthHandler": In this case patUser sets the realm in the authHandler object and asks for the
	*	  authentication data to login user. If the user could authenticated, patUser sets the uid in the 
	*	  authHandler, otherwise it sends the errors to the authHandler. If user was already authenticated, 
	*	  patUser just sends the uid to the authHandler.
	*	
	*
	*	@access	public
	*	@param	string	$mode			what should be done if user is not authenticated (displayLogin|callAuthHandler|exit)
	*	@param	boolean	$displayOnError	flag, that indicates, whether the form should again be displayed on error
	*	@return	int		$uid			user id if user was found
	*	@see setAuthHandler()
	*/
	function	requireAuthentication( $mode = "displayLogin", $displayOnError = true )
	{
		if( $this->isAuthenticated() )
		{
			$uid	=	$this->getUid();
			
			// inform the authentication handler about the logged in user
			if( is_object( $this->authHandler ) && method_exists( $this->authHandler, "patUserSetUid" ) )
			{
				$this->authHandler->patUserSetUid( $uid );
			}
			
			return $uid;
		}

		switch( strtolower( $mode ) )
		{
			case	"displaylogin":
				$displayForm	=	false;
				
				//	get authentication data
				if( $this->useTemplate )
					$authData		=	$this->getAuthVars( "post" );
				else
				{
					if( $this->getSessionValue( "_patUserLoggedOut" ) )
					{
						$this->sendAuthHeader();
					}
					else
						$authData		=	$this->getAuthVars( "http" );
				}
				
				//	check, whether data is correct
				if( isset( $authData[$this->authFields["username"]] ) 
					|| isset(  $authData[$this->authFields["passwd"]] ) 
					|| isset(  $authData[$this->actionVar] ) )
				{
					if( strlen( $authData[$this->authFields["username"]] ) < 1 )
					{
						$displayForm	=	true;
						$this->setError( patUSER_NEED_USERNAME );
					}
					if( strlen( $authData[$this->authFields["passwd"]] ) < 1 )
					{
						$displayForm	=	true;
						$this->setError( patUSER_NEED_PASSWD );
					}
					if( !$displayForm )
					{
						$data	=	array(	$this->authFields["username"]	=>	$authData[$this->authFields["username"]],
											$this->authFields["passwd"]		=>	$authData[$this->authFields["passwd"]] );
						$uid	=	$this->authenticate( $data );
	
						if( !is_int( $uid ) )
						{				
							$displayForm	=	true;		
						}
						else
						{
							$this->storeSessionValue( "_patUserLoginAttempts", 0 );
							return	$uid;
						}
					}
				}
				else
					$displayForm		=	true;	
				
	
				//	check, whether form should be displayed
				if( $displayForm )
				{
					$loginAttempts		=	$this->getSessionValue( "_patUserLoginAttempts" );
					if( !$loginAttempts )
						$loginAttempts	=	0;

					if( $this->maxLoginAttempts > 0 )
					{
						if( $loginAttempts >= $this->maxLoginAttempts )
						{
							if( $this->unauthorizedURL )
							{
								header( "Location:".$this->unauthorizedURL );
								exit;
							}
							
							if( $this->useTemplate )
							{
								$this->tmpl->readTemplatesFromFile( $this->unauthorizedTemplate );
								$this->tmpl->addGlobalVar( "PATUSER_ACTION", $this->actionVar );
								$this->tmpl->addGlobalVar( "PATUSER_REALM", $this->realm );
								$this->tmpl->addGlobalVar( "PATUSER_LOGINATTEMPTS", $loginAttempts );
								$this->tmpl->displayParsedTemplate( "patUserUnauthorized" );
							}
							exit;
						}
					}

					$loginAttempts++;
					$this->storeSessionValue( "_patUserLoginAttempts", $loginAttempts );
					
					if( $this->useTemplate )
					{
						$form_data						=	$this->authFields;
						if( isset( $authData[ $form_data["username"] ] ) )
							$form_data["username_value"]	=	$authData[ $form_data["username"] ];
						else
							$form_data["username_value"]	=	"";
						
						if( isset( $authData[ $form_data["passwd"] ] ) )
							$form_data["passwd_value"]		=	$authData[ $form_data["passwd"] ];
						else
							$form_data["passwd_value"]		=	"";

	
						$this->tmpl->readTemplatesFromFile( $this->loginTemplate );
						$this->tmpl->addGlobalVars( $form_data, "PATUSER_" );
						$this->tmpl->addGlobalVar( "PATUSER_ACTION", $this->actionVar );
						$this->tmpl->addGlobalVar( "PATUSER_SELF", $this->getSelfUrl() );
						$this->tmpl->addGlobalVar( "PATUSER_REALM", $this->realm );
						$this->tmpl->addGlobalVar( "PATUSER_LOGINATTEMPTS", ($loginAttempts-1) );
						
	
						if( $displayOnError )
						{
							$errors	=	$this->getAllErrors();
							if( count( $errors ) )
							{
								$this->tmpl->setAttribute( "errorlist", "visibility", "visible" );
								$this->tmpl->addRows( "error", $errors, "ERROR_" );
							}
							
							if( $this->tmpl->exists( "patUserLogin" ) )
								$this->tmpl->displayParsedTemplate( "patUserLogin" );
							else
								$this->tmpl->displayParsedTemplate();
			
							exit;
						}
					}

					//	no template object, just use HTTP authentication
					else
					{
						$this->sendAuthHeader();
					}
					return	false;
				}
				break;
				
			case	"callauthhandler":
				if( !is_object( $this->authHandler ) )
				{
					die( "patUser fatal error: called requireAuthentication without callback-object; use setAuthHandler()" );
				}
				
				// realm
				if( method_exists( $this->authHandler, "patUserSetRealm" ) )
				{
					$this->authHandler->patUserSetRealm( $this->realm );
				}
				
				// get authentication data
				$authData	=	$this->authHandler->patUserGetAuthData();
				$uid		=	$this->authenticate( $authData );
				
								
				if( $uid )
				{
					// report uid to auth handler
					if( method_exists( $this->authHandler, "patUserSetUid" ) )
						$this->authHandler->patUserSetUid( $uid );
						
					return $uid;
				}
				else
				{
					// report errors to auth handler
					if( method_exists( $this->authHandler, "patUserSetErrors" ) )
						$this->authHandler->patUserSetErrors( $this->getAllErrors() );
						
					return false;
				}
				break;
			
			
			// case exit
			default:
				return false;
				exit;
				break;
		}
	}

   /**
	*	send HTTP authentication header
	*
	*	@access	private
	*/
	function	sendAuthHeader()
	{
		$this->storeSessionValue( "_patUserLoggedOut", false );
		Header( "WWW-Authenticate: Basic realm=\"" . $this->realm . "\"" );
		Header( "HTTP/1.0 401 Unauthorized" );
		exit;
	}
	
   /**
	*	import needed variables from request
	*
	*	@access	private
	*	@return	array	$authVars	needed vars for authentication
	*/
	function	getAuthVars( $mode = "post" )
	{
		$authVars	=	array();

		switch( strtolower( $mode ) )
		{
			//	use HTTP authentication
			case	"http":
				if( $this->getPHPVersion() >= 4.1 )
				{
					$authVars[$this->authFields["username"]]	=	$_SERVER["PHP_AUTH_USER"];
					$authVars[$this->authFields["passwd"]]		=	$_SERVER["PHP_AUTH_PW"];
				}
				else
				{
					global	$HTTP_SERVER_VARS;
					$authVars[$this->authFields["username"]]	=	$HTTP_SERVER_VARS["PHP_AUTH_USER"];
					$authVars[$this->authFields["passwd"]]		=	$HTTP_SERVER_VARS["PHP_AUTH_PW"];
				
				}
				break;

			case	"post":
			default:
				$varNames	=	array_values( $this->authFields );
				array_push( $varNames, $this->actionVar );
		
				reset( $varNames );
		
				//	check for PHP version
				if( $this->getPHPVersion() >= 4.1 )
				{
					foreach( $varNames as $tmp )
					{
						if( isset( $_GET[$tmp] ) )
							$authVars[$tmp]		=	$_GET[$tmp];
						elseif( isset( $_POST[$tmp] ) )
							$authVars[$tmp]		=	$_POST[$tmp];
					}
				}
				else
				{
					global	$HTTP_GET_VARS, $HTTP_POST_VARS;
					foreach( $varNames as $tmp )
					{
						if( isset( $HTTP_GET_VARS[$tmp] ) )
							$authVars[$tmp]		=	$HTTP_GET_VARS[$tmp];
						elseif( isset( $HTTP_POST_VARS[$tmp] ) )
							$authVars[$tmp]		=	$HTTP_POST_VARS[$tmp];
					}
				}
				break;
		}
		return	$authVars;
	}
	
  /**
	*	force log out of an authenticated user
	*
	*	@access	public
	*	@param	boolean	$force	if set to false, a logout screen will be displayed (will be implemented in future versions)
	*/
	function	logOut( $force = true )
	{
		if( !$this->isAuthenticated() )
			return	true;
			
		$this->clearUid();

		if( !$this->useTemplate )
		{
			$this->storeSessionValue( "_patUserLoggedOut", true );
			if( $this->getPHPVersion() >= 4.1 )
			{
				unset( $_SERVER["PHP_AUTH_USER"] );
				unset( $_SERVER["PHP_AUTH_PW"] );
			}
			else
			{
				global	$HTTP_SERVER_VARS;
				unset( $HTTP_SERVER_VARS["PHP_AUTH_USER"] );
				unset( $HTTP_SERVER_VARS["PHP_AUTH_PW"] );
			}
		}
		return	true;
	}

   /**
	*	adds a user
	*
	*	create a new user and store it basic-authentiaction data.
	*
	*	@access	public
	*	@param	array	$authData	data for the user that will be stored in the authtable (compared with authFields)
	*	@param	boolean	$login		automatically login after creation
	*	@return	int		$uid		User ID of the user
	*	@see	$authTable, $authFields
	*/
	function	addUser( $authData, $login = true )
	{
		if( !is_array( $authData ) )
		{
			$this->setError( patUSER_NO_DATA_GIVEN ); 
			return	false;
		}

		//	need username
		if( !$authData[$this->authFields["username"]] )
			$this->setError( patUSER_NEED_USERNAME );

		//	check password
		if( is_array( $authData[$this->authFields["passwd"]] ) )
		{
			//	need password
			if( !$authData[$this->authFields["passwd"]][0] )
				$this->setError( patUSER_NEED_PASSWD );
			elseif( $authData[$this->authFields["passwd"]][0] != $authData[$this->authFields["passwd"]][1] )
				$this->setError( patUSER_PASSWD_MISMATCH );

			$authData[$this->authFields["passwd"]]	=	$authData[$this->authFields["passwd"]][0];
		}
		else
			//	need password
			if( !$authData[$this->authFields["passwd"]] )
				$this->setError( patUSER_NEED_PASSWD );

		$authData[$this->authFields["passwd"]]	=	$this->encryptPasswd( $authData[$this->authFields["passwd"]] );
		
		if( count( $this->getAllErrorCodes() ) )
			return	false;
			
		//	check whether user already exists
		if( $this->identifyUser( array( $this->authFields["username"] => $authData[$this->authFields["username"]] ) ) )
		{
			$this->setError( patUSER_USER_ALREADY_EXISTS );
			return	false;
		}
		else
			$this->removeLastError();
			
		//	create query
		$set	=	array();
		while( list( $field, $value ) = each( $authData ) )
			if( in_array( $field, $this->authFields ) )
				$set[]	=	$field."='".addslashes( $value )."'";

		//	statistics (when was this user created => first login )
		if( $this->stats["first_login"] )
			$set[]	=	$this->stats["first_login"]."='".date( "Y-m-d H:i:s" )."'";

		//	get the next user id
		$uid	=	$this->authDbc->nextId( $this->userIdSequence );

		array_push( $set, $this->authFields["primary"] . "=" . $uid );
		
		$query	=	"INSERT INTO ".$this->authTable." SET ".implode( ",", $set );
		//	insert data
		$this->authDbc->query( $query );
		
		//	automatic login
		if( $uid && $login )
		{
			$this->setUid( $uid );
		}
		
		return	$uid;
	}
	
   /**
	*	store a value in the session
	*
	*	may only be used if patUser was called with $useSessions = true
	*
	*	@access	public
	*	@param	string	$name		name of the value to store
	*	@param	mixed	$val		value to store
	*	@return	bool	$success	true if the value could be stored, false otherwise
	*	@see	$useSessions, clearSessionValue(), getSessionValue()
	*/
	function	storeSessionValue( $name, $val )
	{
		if( !$this->useSessions )
			return	false;

		$this->sessionData[$name]		=	$val;
		return	true;
	}

   /**
	*	clear a value in the session
	*
	*	may only be used if patUser was called with $useSessions = true
	*
	*	@access	public
	*	@param	string	$name		name of the value to clear
	*	@return	bool	$val		the value of the removed variable
	*	@see	$useSessions, storeSessionValue(), getSessionValue()
	*/
	function	clearSessionValue( $name )
	{
		if( !$this->useSessions )
			return	false;

		$val	=	$this->getSessionValue( $name );
		unset( $this->sessionData[$name] );
		return	$val;
	}
	
	
   /**
	*	get a value that was stored in the session
	*
	*	may only be used if patUser was called with $useSessions = true
	*
	*	@access	public
	*	@param	string	$name		name of the value to retrieve
	*	@return	mixed	$val		value that was stored
	*	@see	$useSessions, clearSessionValue(), storeSessionValue()
	*/
	function	getSessionValue( $name )
	{
		if( !$this->useSessions )
			return	false;

		if( isset( $this->sessionData[$name] ) )
			return	$this->sessionData[$name];

		return "";
	}

   /**
	*	check, whether a value has been stored in the session
	*	may only be used if patUser was called with $useSessions = true
	*
	*	@access	public
	*	@param	string	$name		name of the value to retrieve
	*	@return	boolean	$isset		flag to indicate, whether valu is set
	*	@see	$useSessions
	*/
	function	issetSessionValue( $name )
	{
		if( !$this->useSessions )
			return	false;

		if( isset( $this->sessionData[$name] ) )
			return	true;
			
		return	false;
	}

	/**
	*	let patUser create a history of visited pages
	*
	*	needs session support
	*
	*	@access	public
	*	@param	int		$amount		size of the history
	*	@param	string	$title		title of the current page
	*	@return	bool	$success	true if the history could be stored, false otherwise
	*/
	function	keepHistory( $amount, $title = "" )
	{
		//	no sessions => no history
		if( !$this->useSessions )
			return	false;

		$authData	=	$this->getAuthVars();
			
		//	login => page did not change
		if( isset( $authData[$this->authFields["username"]] ) 
			|| isset( $authData[$this->authFields["passwd"]] ) 
			|| ( isset( $authData[$this->actionVar] ) && $authData[$this->actionVar] == "login" ) )
			return	true;
			
		$history	=	$this->getSessionValue( "patUserHistory" );
		
		if( is_array( $history ) )
		{
			//	only reload?
			$last		=	$history[( count($history) - 1 )]["url"];
			$self		=	$this->getSelfUrl();
			if( $last == $self )
				return	true;	

			array_push( $history, array( "url" => $self, "title" => $title ) );
			if( count( $history ) > $amount )
				for( $i = 1; $i <= $amount; $i++ )
					$history[($i-1)]	=	$history[$i];

				unset( $history[$amount] );
		}
		else
			$history	=	array( array( "url" => $this->getSelfUrl(), "title" => $title ) );

		$this->storeSessionValue( "patUserHistory", $history );
			
		return	true;
	}

   /**
	*	get the history for the current user
	*
	*	@access	public
	*	@param	integer		$pages		number of the last pages to get
	*	@return	array		$history	array containing titles and urls of the history
	*/
	function	getHistory( $pages = 0 )
	{
		$tmp	=	$this->getSessionValue( "patUserHistory" );
		if( !is_array( $tmp ) )
			return	array();
		
		if( !$pages )
			$pages	=	count( $tmp );

		if( count( $tmp ) <= $pages )
			return	$tmp;

		$history	=	array();
		for( $i=(count( $tmp ) - $pages ); $i<count( $tmp ); $i++ )
			$history[]	=	$tmp[$i];

		return	$history;
	}
	
   /**
	*	go back x pages in the history
	*	as Header( "Location:..." ) is used, there must not be any output before this is called
	*
	*	@access	public
	*	@param	integer		$pages		number of the pages to go back (use negative values)
	*/
	function	goHistory( $pages = -1 )
	{
		$history	=	$this->getSessionValue( "patUserHistory" );
		if( !is_array( $history ) )
			return	false;

		$url	=	$history[( count( $history ) + $pages )]["url"];
		
		header( "Location:".$url );
		exit;
	}
	

   /**
	*	add a dsn or dbc-object
	*
	*	you can use as many databases as you like to access data
	*
	*	@access	public
	*	@param	string	$name		unique name to access the dbc
	*	@param	mixed	$dsn		datasource name or PEAR:DB object
	*	@param	boolean	$persistent	flag to indicate whether a persistent connection should be established
	*	@return boolean	$result		true on success, DB::Error-object of initialisation failed
	*	@see	$dbcs, addDbc()
	*/
	function	addDbc( $name, $dsn, $persistent = false )
	{
		// the object is already set up
		if( is_object( $dsn ) )
		{
			if( is_subclass_of( $dsn, "DB_common" ) )
			{
				$this->dsns[$name]	=	false;
				$this->dbcs[$name]	=	$dsn;
				return true;
			}

			die( "patUser fatal error: The dsn must be either a PEAR::DB-object or a dsn-string" );
		}
	
		// otherwise setup dbc
		$this->dsns[$name]	=	$dsn;
		$this->dbcs[$name]	=	DB::connect( $dsn, $persistent );
		if( DB::isError( $this->dbcs[$name] ) )
		{
			return	$this->dbcs[$name];
		}
		return	true;
	}	

   /**
	*	add table that is used to store user data
	*	values for tabledefs:
	*	 - foreign		(required)	name of the field, that stores the foreign key
	*	 - primary		(optional)	only needed when more than one line for each user is stored in the table
	*	 - table		(optional)	name of table in database
	*	 - dbc			(optional)	dbc to use
	*	 - minentries	(optional)	minimum amount of entries in this table
	*	 - maxentries	(optional)	maximum amount of entries in this table
	*
	*	@access	public
	*	@param	string	$name		internal name of the table
	*	@param	array	$tabledef	array containing information about the table
	*/
	function	addTable( $name, $tabledef )
	{
		if( !$tabledef["table"] )
			$tabledef["table"]		=	$name;
			
		$this->tables[$name]		=	$tabledef;
	}

   /**
	*	get table definitions
	*
	*	@access	public
	*	@param	string	$name		internal name of the table
	*	@return	array	$tabledef	array containing information about the table
	*/
	function	getTableDef( $name )
	{
		if( $this->tables[$name] )
			return	$this->tables[$name];
		return	false;
	}

   /**
	*	fetch the user data from a table
	*
	*	@access	public
	*	@param	array	$options	several options, like table name, user id and fields to get
	*	@param	array	$clause		assoc array containing fields/values for the where statement
	*	@return	array	$data		user data
	*/
	function	getUserData( $options = array(), $clause = array() )
	{
		//	check if user id is given => no identification needed
		if( isset( $options["uid"] ) && !empty( $options["uid"] ) )
			$uid	=	$options["uid"];
		elseif( isset( $options[$this->authFields["primary"]] ) && !empty( $options[$this->authFields["primary"]] ) )
			$uid	=	$options[$this->authFields["primary"]];
		elseif( $this->isAuthenticated() )
			$uid	=	$this->getUid();
		else
			return	false;

		//	get table and primary key from options
		$table		=	$this->authTable;
		$uidfield	=	$this->authFields["primary"];
		if( isset( $options["table"] ) )
		{
			if( isset( $this->tables[$options["table"]]["table"] ) )
				$table		= $this->tables[$options["table"]]["table"];
			
			if( isset( $this->tables[$options["table"]]["foreign"] ) ) 
				$uidfield	=	$this->tables[$options["table"]]["foreign"];
		}

		unset( $options["uid"] );

		$fields	=	$options;
		if( isset( $options["fields"] ) ) 
			$fields		= $options["fields"];
	
		$dbc			=	&$this->getDbc( $table );

		unset( $fields["table"] );
		unset( $fields["foreign"] );
		unset( $options["table"] );
		unset( $options["fields"] );

		//	add user id to clause for select
		$clause[$uidfield]	=	 $uid;

		//	reformat clause
		$newClause			=	array();

		foreach( $clause as $field => $value )
			array_push( $newClause, array( "field" => $field, "value" => $value, "match" => "exact" ) );

		$query			=	$this->buildSelectQuery( $table, $fields, $newClause, $options );
		
		//	query database
		return 	$dbc->getAll( $query, array(), DB_FETCHMODE_ASSOC );
	}

   /**
	*	get users from any table
	*
	*	@access	public
	*	@param	array	$fields		array containing fieldnames to be fetched
	*	@param	array	$clause		array containing conditions for the where statement
	*	@param	array	$options	array containing misc options
	*/
	function	getUsers( $fields = array(), $clause = array(), $options = array() )
	{
		//	get table and primary key from options
		$table	=	$this->authTable;
		if( isset( $options["table"] ) && isset( $this->tables[$options["table"]]["table"] ) )
			$table	=	$this->tables[$options["table"]]["table"];
		
		$query		=	$this->buildSelectQuery( $table, $fields, $clause, $options );
		
		//	get the dbc for the statement
		$dbc		=&	$this->getDbc( $table );

		//	query database
		return $dbc->getAll( $query, array(), DB_FETCHMODE_ASSOC );
	}

   /**
	*	identify a user by certain fields
	*
	*	@access	public
	*	@param	array	$options	several options to identify the user
	*/
	function	identifyUser( $options )
	{
	
		//	check if user id is given => no identification needed
		if( isset( $options["uid"] ) )
			return	$options["uid"];
		if( isset( $options[$this->authFields["primary"]] ) )
			return	$options[$this->authFields["primary"]];
			
		//	get table and ...
		if( isset($options["table"] ) && isset( $this->tables[$options["table"]]["table"] ) )
			$table	=	$this->tables[$options["table"]]["table"];
		else
			$table	=	$this->authTable;

		// ...primary key from options
		if( isset( $options["table"] ) && isset( $this->tables[$options["table"]]["foreign"] ) )
			$uidfield	=	$this->tables[$options["table"]]["foreign"];
		else
			$uidfield	=	$this->authFields["primary"];

		$fields		=	isset( $options["fields"] ) ? $options["fields"] : $options;

		unset( $fields["table"] );
		unset( $fields["primary"] );
		
		$query		=	"SELECT ".$uidfield." FROM ".$table." WHERE 1";
		if( is_array( $fields ) )
			while( list( $field, $value ) = each( $fields ) )
				$query	.=	" AND ".$field."='".$value."'";

		
		//	get dbc for the query
		if( isset( $options["table"] ) && isset( $this->dbcs[$this->tables[$options["table"]]["dbc"]] ) )
			$dbc		=	&$this->dbcs[$this->tables[$options["table"]]["dbc"]];
		else
			$dbc		=	&$this->authDbc;

		//	query database
		$result			=	$dbc->query( $query );

		//	check, if only one
		if( $result->numRows() == 1 )
		{
			list( $uid )		=	$result->fetchRow( DB_FETCHMODE_ORDERED );
			return	(int)$uid;
		}
		elseif( $result->numRows() == 0 )
			$this->setError( patUSER_NO_USER_FOUND );
		else
			$this->setError( patUSER_NO_UNIQUE_USER_FOUND );

		return	false;
	}
	
   /**
	*	modify an existing user
	*
	*	@access	public
	*	@param	array	$data		new data for the user
	*	@param	array	$options	various options like mode (insert|update), uid and table
	*/
	function	modifyUser( $data, $options = array() )
	{
		if( !is_array( $data ) )
		{
			$this->setError( patUSER_NO_DATA_GIVEN ); 
			return	false;
		}

		//	check if user id is given
		if( isset( $options["uid"] ) )
			$uid	=	$options["uid"];
		elseif( isset( $options[$this->authFields["primary"]] ) )
			$uid	=	$options[$this->authFields["primary"]];
		//	No user ID => use logged in user
		elseif( $this->isAuthenticated() )
			$uid	=	$this->getUid();
		//	No user is logged in => error!
		else
		{
			$this->setError( patUSER_NEED_UID );
			return	false;
		}

		//	get table and primary key from options
		//	if no data is given, the authTable will be used
		$table		=	$this->authTable;
		$uidfield	=	$this->authFields["primary"];
		$primary	=	false;
		$minentries	=	0;
		$maxentries	=	-1;
		
		
		if( isset( $options["table"] ) )
		{
			if( isset( $this->tables[$options["table"]]["table"] ) )
				$table		= $this->tables[$options["table"]]["table"];
			
			if( isset( $this->tables[$options["table"]]["foreign"] ) ) 
				$uidfield	=	$this->tables[$options["table"]]["foreign"];
				
			if( isset( $this->tables[$options["table"]]["primary"] ) )
				$primary	=	$this->tables[$options["table"]]["primary"];
				
			if( isset( $this->tables[$options["table"]]["minentries"] ) )
				$minentries	=	$this->tables[$options["table"]]["minentries"];
			
			if( isset( $this->tables[$options["table"]]["maxentries"] ) )
				$maxentries	=	$this->tables[$options["table"]]["maxentries"];
		}

		if( $uidfield == $primary )
			$primary	=	false;
	
		// get primary if for auth-table
		if( $table == $this->authTable )
		{
			$primary	=	$this->authFields["primary"];
		
			//	modifying auth data => check it first
			if( isset( $data[$this->authFields["passwd"]] ) )
			{
			
				//	check password
				if( is_array( $data[$this->authFields["passwd"]] ) )
				{
					//	need password
					if( !$data[$this->authFields["passwd"]][0] )
						$this->setError( patUSER_NEED_PASSWD );
					elseif( $data[$this->authFields["passwd"]][0] != $data[$this->authFields["passwd"]][1] )
						$this->setError( patUSER_PASSWD_MISMATCH );
						
					$data[$this->authFields["passwd"]]	=	$data[$this->authFields["passwd"]][0];
				}
				else
				{
					if( empty( $data[$this->authFields["passwd"]] ) )
						$this->setError( patUSER_NEED_PASSWD );
				}
	
				$data[$this->authFields["passwd"]]	=	$this->encryptPasswd( $data[$this->authFields["passwd"]] );
						
				if( count( $this->getAllErrorCodes() ) )
					return	false;
			}
		}
		
		//	check, whether a value for the primary key was given
		$primaryval	=	$uid;
		if( $primary )
		{
			if( isset( $options["primary"] ) )
				$primaryval	=	$options["primary"];
			elseif( isset( $options[$primary] ) )
				$primaryval	=	$options[$primary];
			elseif( isset( $options["olddata"] ) )
				$primaryval	=	$this->getPrimaryValue( $uid, $table, $options["olddata"] );
		}
		
		$dbc		=&	$this->getDbc( $table );
		
		if( !isset( $options["mode"] ) )
			$options["mode"]	=	"update";
			
		
		switch( strtolower( $options["mode"] ) )
		{
			//	insert a new entry
			case	"insert":
				if( $maxentries != -1 && $this->countTableEntries( $table, $uid ) >= $maxentries )
				{
					$this->setError( patUSER_INSERT_NOT_ALLOWED );
					return	false;
				}

				$set	=	array();
				
				// add the userid to the query
				if( $table != $this->authTable )
					array_push( $set,  $uidfield."='".$uid."'" );
					
				while( list( $field, $value ) = each( $data ) )
					if( $field != $uidfield )
						$set[]		=	$field."='".addslashes( $value )."'";

				if( $primary )
				{
					$sequenceName	=	$this->userIdSequence . "_" . $table;
					$primaryval		=	$dbc->nextId( $sequenceName );
					array_push( $set, $primary . "=" . $primaryval );
				}

				$query		=	"INSERT INTO ".$table." SET ".implode( ",", $set );
				$dbc->query( $query );

				if( $primary )
					return	$primaryval;
					
				return	true;

				break;

			//	updating existing data
			case	"update":
			default:
				if( !$primaryval && $maxentries != 0 )
				{
					$this->setError( patUSER_COULD_NOT_IDENTIFY_LINE );
					return	false;
				}

				$set	=	array();
				while( list( $field, $value ) = each( $data ) )
					$set[]		=	$field."='".addslashes( $value )."'";

				$query		=	"UPDATE ".$table." SET ".implode( ",", $set )." WHERE ".$uidfield."='".$uid."'";
				if( $primary )
					$query	.=	" AND ".$primary."='".$primaryval."'";

				$dbc->query( $query );
				
				if( $primary )
					return	$primaryval;
					
				return	true;
				
				break;
		}
		return	false;
	}
	
   /**
	*	delete (data of) an existing user
	*
	*	@access	public
	*	@param	array	$options	options for the deletion
	*/
	function	deleteUser( $options = array() )
	{
		//	check if user id is given
		if( $options["uid"] )
			$uid	=	$options["uid"];
		elseif( $options[$this->authFields["primary"]] )
			$uid	=	$options[$this->authFields["primary"]];
		//	No user ID => use logged in user
		elseif( $this->isAuthenticated() )
			$uid	=	$this->getUid();
		//	No user is logged in => error!
		else
		{
			$this->setError( patUSER_NEED_UID );
			return	false;
		}

		//	get table and primary key from options
		//	if no data is given, the authTable will be used
		$table		=	$this->authTable;
		$uidfield	=	$this->authFields["primary"];
		$primary	=	false;
		$minentries	=	1;
		$maxentries	=	1;
		
		if( isset( $options["table"] ) )
		{
			if( isset( $this->tables[$options["table"]]["table"] ) )
				$table	=	$this->tables[$options["table"]]["table"];
				
			if( isset( $this->tables[$options["table"]]["foreign"] ) )
				$uidfield	=	$this->tables[$options["table"]]["foreign"];

			if( isset( $this->tables[$options["table"]]["primary"] ) )
				$primary	=	$this->tables[$options["table"]]["primary"];
			if( isset( $this->tables[$options["table"]]["minentries"] ) )
				$minentries	=	$this->tables[$options["table"]]["minentries"];
				
			if( isset( $this->tables[$options["table"]]["maxentries"] ) )
				$maxentries	=	$this->tables[$options["table"]]["maxentries"];
		}

		//	need an existing table
		if( !$table )
		{
			$this->setError( patUSER_TABLE_DOES_NOT_EXIST );
			return	false;
		}
		
		
		switch( $table )
		{
			//	delete user
			case	$this->authTable:
				//	Delete all additional data
				reset( $this->tables );
				while( list( $table, $data ) = each( $this->tables ) )
				{
					
					if( isset( $data["table"] ) )
					{
						$tablename	=	$data["table"];
						
						$uidfield	=	isset( $data["foreign"] ) ? $data["foreign"] : $this->authFields["primary"];
						
						$query		=	"DELETE FROM ".$tablename." WHERE ".$uidfield."='".$uid."'";
						$dbc		=&	$this->getDbc( $table );
						
						$dbc->query( $query );
					}
				}
				//	remove from all groups
				$this->removeUserFromGroup( array( "gid" => "all", "uid" => $uid ) );

				//	delete all his permissions
				$this->deletePermission( array( "id" => $uid, "id_type" => "user" ) );
				
				//	Delete from authTable
				$query	=	"DELETE FROM ".$this->authTable." WHERE ".$this->authFields["primary"]."='".$uid."'";
				$this->authDbc->query( $query );
				return	true;
				break;
			//	delete data
			default:
				if( $this->countTableEntries( $options["table"], $uid ) <= $minentries )
				{
					$this->setError( patUSER_DELETE_NOT_ALLOWED );
					return	false;
				}

				//	check, whether a value for the primary key was given
				if( $primary )
				{
					if( isset( $options["primary"] ) )
						$primaryval	=	$options["primary"];
					elseif( isset( $options[$primary] ) )
						$primaryval	=	$options[$primary];
					elseif( isset( $options["olddata"] ) )
						$primaryval	=	$this->getPrimaryValue( $uid, $table, $options["olddata"] );
				}

				$query		=	"DELETE FROM ".$table." WHERE ".$uidfield."='".$uid."'";
				if( $primary && $primaryval )
					$query	.=	" AND ".$primary."='".$primaryval."'";

				$dbc		=&	$this->getDbc( $table );
				$dbc->query( $query );
				if( $dbc->affected_rows() > 0 )
					return	true;

				$this->setError( patUSER_NO_DATA_CHANGED );
				return	false;
				
				break;
		}
		return	false;
	}
	
   /**
	*	get the value of the primary key in an additional table
	*	identifies a unique line in an additional table
	*
	*	@access	public
	*	@param	int		$uid	user id
	*	@param	string	$table	name of the table
	*	@param	array	$data	date of the line to identify
	*/
	function	getPrimaryValue( $uid, $table, $data )
	{
		if( !is_array( $data ) )
			return	false;

		//	get table and primary key from options
		//	if no data is given, the authTable will be used
		$tablename	=	isset( $this->tables[$table]["table"] ) ? $this->tables[$table]["table"] : $this->authTable;
		$uidfield	=	isset( $this->tables[$table]["foreign"] ) ? $this->tables[$table]["foreign"] : $this->authFields["primary"];
		$primary	=	isset( $this->tables[$table]["primary"] ) ? $this->tables[$table]["primary"] : $this->authFields["primary"];
		
		$where		=	array( $uidfield."='".$uid."'" );
		while( list( $field, $value ) = each( $data ) )
			$where[]	=	$field."='".$value."'";

		$query		=	"SELECT ".$primary." FROM ".$tablename." WHERE ".implode( " AND ", $where );

		$dbc		=&	$this->getDbc( $table );

		$result			=	$dbc->query( $query );

		//	check, if only one
		if( $result->numRows() == 1 )
		{
			list( $id )		=	$result->fetchRow( DB_FETCHMODE_ORDERED );
			$result->free();
			return	$id;
		}
		elseif( $result->numRows() == 0 )
			$this->setError( patUSER_NO_PRIMARY_FOUND );
		else
			$this->setError( patUSER_NO_UNIQUE_PRIMARY_FOUND );

		return	false;

	}

   /**
	*	count amount of entries for a user in a table
	*
	*	@access	public
	*	@param	string	$table	name of the table
	*	@param	integer	$uid	useri id (logged in user is used if none is given)
	*/
	function	countTableEntries( $table, $uid = 0 )
	{
		if( !$uid )
		{
			if( !$uid = $this->getUid() )
			{
				$this->setError( patUSER_NEED_UID );
				return	false;
			}
		}
		$tablename	=	isset( $this->tables[$table]["table"] ) ? $this->tables[$table]["table"] : $this->authTable;
		$uidfield	=	isset( $this->tables[$table]["foreign"] ) ? $this->tables[$table]["foreign"] : $this->authFields["primary"];
		
		$query		=	"SELECT COUNT(*) AS entries FROM ".$tablename." WHERE ".$uidfield."='".$uid."'";
		$dbc		=&	$this->getDbc( $table );
		$result		=	$dbc->query( $query );

		if( $result->numRows() == 0 )
			return	0;
		
		list( $entries )	=	$result->fetchRow( DB_FETCHMODE_ORDERED );
		$result->free();
		
		return	$entries;
	}

   /**
	*	Search for users matching certain criterias
	*
	*	@access	public
	*	@param	array	$criterias		see documentation
	*	@param	array	$options		see documentation
	*	@return	array	$users			array of users (only basic data) matching the criterias
	*/
	function	searchUsers( $criterias, $options = array() )
	{
		$matches		=	false;

		//	group criterias by dbc
		$groups		=	array();
		for( $i = 0; $i < count( $criterias ); $i++ )
		{
			$dbc			=	isset( $this->tables[$criterias[$i]["table"]]["dbc"] ) ? $this->tables[$criterias[$i]["table"]]["dbc"] : "auth";
			$groups[$dbc][]	=	$criterias[$i];
		}

		//	check for ALL or ANY
		if( strtolower( $options["conditions"] ) == "any" )
			$bind		=	" OR ";
		else
			$bind		=	" AND ";
		
		reset( $groups );
		while( list( $dbc, $criterias ) = each( $groups ) )
		{
			$dbc			=	$this->getDbc( $criterias[0]["table"] );

			$select			=	array();
			$having			=	array();
			
			$tables			=	array();
			$where			=	array();
			$uids			=	array();
			$groupBy		=	array();

			for( $i = 0; $i < count( $criterias ); $i++ )
			{
				$tablename	=	isset( $this->tables[$criterias[$i]["table"]]["table"] ) ? $this->tables[$criterias[$i]["table"]]["table"] : $this->authTable;
				$uidfield	=	isset( $this->tables[$criterias[$i]["table"]]["foreign"] ) ? $this->tables[$criterias[$i]["table"]]["foreign"] : $this->authFields["primary"];
	
				//	add the table to the list of all used tables
				if( !in_array( $tablename, $tables ) )
					$tables[]	=	$tablename;
				
				//	add the uid field to the list of all uids
				if( !in_array( $tablename.".".$uidfield, $uids ) )
					$uids[]	=	$tablename.".".$uidfield;

				if( count( $select ) == 0 )
					$select[]	=	$tablename.".".$uidfield." AS uid";

				//	use the condition in the where statement
				$var			=	"where";
				
				//	check for extras
				if( $criterias[$i]["extra"] )
				{
					switch( $criterias[$i]["extra"] )
					{
						case	"countentries";

							if( !in_array( "COUNT(".$tablename.".".$uidfield.") AS ".$tablename."entries", $select ) )
								$select[]					=	"COUNT(".$tablename.".".$uidfield.") AS ".$tablename."entries";

							$criterias[$i]["field"]		=	$tablename."entries";

							if( !in_array( $tablename.".".$uidfield, $groupBy ) )
								$groupBy[]					=	$tablename.".".$uidfield;

							//	use it in the HAVING statement
							$var						=	"having";
							break;
					}
				}
				else
					$criterias[$i]["field"]	=	$tablename.".".$criterias[$i]["field"];

				array_push( $$var, $this->buildWhereStatement( $criterias[$i]["field"], $criterias[$i]["value"], $criterias[$i]["match"] ) );
			}

			$query		=	"SELECT ".implode( ",", $select )." FROM ".implode( ",",$tables );

			if( count( $where ) )
				$query	=	$query." WHERE (".implode( $bind, $where ).")";
			
			$where		=	array();
			if( count( $uids ) > 1 )
			{
				for( $j = 0; $j < ( count( $uids ) - 1 ); $j++ )
				{
					for( $k = ( $j + 1 ); $k < count( $uids ); $k++ )
						$where[]	=	$uids[$j]."=".$uids[$k];
				}
				$query	=	$query." AND (".implode( $bind, $where ).")";
			}

			if( count( $groupBy ) > 0 )
				$query	=	$query." GROUP BY ".implode( ",", $groupBy )." HAVING ".implode( $bind, $having ) ;

			//	send query and retrive all user ids as array
			$tmp		=	$dbc->getCol( $query );

			if( !is_array( $matches ) )
				$matches	=	$tmp;
			else
			{
				//	check for ALL or ANY
				if( strtolower( $options["conditions"] ) == "any" )
					$matches	=	array_merge( $matches, $tmp );
				else
					$matches	=	array_intersect( $matches, $tmp );
			}
		}

		if( is_array( $matches ) )
			return	array_unique( $matches );

		return	array();
	}

   /**
	*	fetch the value of a field
	*
	*	@access	public
	*	@param	string	$field	name of the table
	*	@param	integer	$uid	user id, if no uid is givven the authenticated user will be used	
	*	@return	mixed	$value	value of the field
	*/
	function	getField( $field, $uid = 0 )
	{
		if( !$uid )
		{
			if( !$uid	=	$this->getUid() )
			{
				$this->setError( patUSER_NEED_UID );
				return	false;
			}
		}

		if( !$this->fieldLocs[$field] )
			return	false;
		
		//	get table and primary key from options
		$table		=	isset( $this->tables[$this->fieldLocs[$field]["table"]]["table"] ) ? $this->tables[$this->fieldLocs[$field]["table"]]["table"] : $this->authTable;
		$uidfield	=	isset( $this->tables[$this->fieldLocs[$field]["table"]]["foreign"] ) ? $this->tables[$this->fieldLocs[$field]["table"]]["foreign"] : $this->authFields["primary"];
		
		$query		=	"SELECT ".$this->fieldLocs[$field]["field"]." FROM ".$table." WHERE ".$uidfield."='".$uid."'";
		
		$dbc		=	&$this->getDbc( $this->fieldLocs[$field]["table"] );
		
		//	query database
		$result			=	$dbc->query( $query );

		if( $result->numRows() == 0 )
			return	false;

		if( $result->numRows() == 1 )
		{
			list( $value )	=	$result->fetchRow( DB_FETHCMODE_ORDERED );
			$result->free();
		}
		else
		{
			$value	=	array();
			while(	list( $tmp )	=	$result->fetchRow( DB_FETCHMODE_ORDERED ) )
				$value[]		=	$tmp;
		}
		return	$value;
	}

   /**
	*	get groups from the grouptable
	*
	*	@access	public
	*	@param	array	$fields		array containing fieldnames to be fetched
	*	@param	array	$clause		array containing conditions for the where statement
	*	@param	array	$options	array containing misc options
	*/
	function	getGroups( $fields = array(), $clause = array(), $options = array() )
	{
		$query			=	$this->buildSelectQuery( $this->groupTable, $fields, $clause, $options );

		//	query database
		$data			=	$this->authDbc->getAll( $query, array(), DB_FETCHMODE_ASSOC );
		return	$data;
	}

   /**
	*	fetch the group data from group table
	*
	*	@access	public
	*	@param	array	$fields		fields to get
	*	@param	array	$clause		params to identify the group
	*	@return	array	$data		data of the group
	*/
	function	getGroupData( $fields = array(), $clause = array() )
	{
		if( !$gid = $this->identifyGroup( $clause ) )
			return	false;
		
		if( is_array( $fields ) && count( $fields ) > 0 )
			$fields	=	implode( ",", $fields );
		else
			$fields	=	"*";
		
		$query		=	"SELECT ".$fields." FROM ".$this->groupTable." WHERE ".$this->groupFields["primary"]."='".$gid."'";
	
		//	query database
		return 	$this->authDbc->getAll( $query, array(), DB_FETCHMODE_ASSOC );
	}
	
	
   /**
	*	identify a group by certain fields
	*
	*	@access	public
	*	@param	array	$fields			several fields to identify the group
	*	@param	boolean	$createError	if set to false, no error will be added to errorlist if no group was found
	*/
	function	identifyGroup( $fields, $createError = true )
	{
		$where		=	array( "1" );

		if( is_array( $fields ) )
		{
			reset( $fields );
			while( list( $field, $value ) = each( $fields ) )
				$where[]	=	$field."='".$value."'";
		}

		//	query database
		$query			=	"SELECT ".$this->groupFields["primary"]." FROM ".$this->groupTable." WHERE ".implode( " AND ", $where );
		$result			=	$this->authDbc->query( $query );

		//	check, if only one
		if( $result->numRows() == 1 )
		{
			$group		=	$result->fetchRow( DB_FETCHMODE_ASSOC );
			return (int) $group[$this->groupFields["primary"]];
			
		}
		elseif( $result->numRows() == 0 )
			if( $createError )
				$this->setError( patUSER_NO_GROUP_FOUND );
		else
			if( $createError )
				$this->setError( patUSER_NO_UNIQUE_GROUP_FOUND );

		return	false;
	}
	

   /**
	*	modify an existing group
	*
	*	@access	public
	*	@param	array	$olddata	old data of the group
	*	@param	array	$newdata	new data of the group
	*/
	function	modifyGroup( $olddata, $newdata )
	{
		if( !is_array( $olddata ) || !is_array( $newdata ) )
		{
			$this->setError( patUSER_NO_DATA_GIVEN ); 
			return	false;
		}

		//	need username
		if( !$newdata[$this->groupFields["name"]] )
		{
			$this->setError( patUSER_NEED_GROUPNAME );
			return	false;
		}
			
		//	check whether user already exists
		if( $gid = $this->identifyGroup( array( $this->groupFields["name"] => $newdata[$this->groupFields["name"]] ), false ) )
		{
			if( $gid != $olddata[$this->groupFields["primary"]] )
			{
				$this->setError( patUSER_GROUP_ALREADY_EXISTS );
				return	false;
			}
		}

		if( !$gid = $this->identifyGroup( $olddata ) )
			return	false;

		$set	=	array();
		reset( $newdata );
		while( list( $field, $value ) = each( $newdata ) )
			if( in_array( $field, $this->groupFields ) )
				$set[]	=	$field."='".addslashes( $value )."'";
 
		$query		=	"UPDATE ".$this->groupTable." SET ".implode( ",", $set )." WHERE ".$this->groupFields["primary"]."='".$gid."'";

		$this->authDbc->query( $query );
		return	true;
	}
	

   /**
	*	adds a group
	*
	*	@access	public
	*	@param	array	$data		date for the group
	*	@return	int		$gid		ID of the group
	*/
	function	addGroup( $data = array() )
	{
		if( !is_array( $data ) || count( $data ) == 0 )
		{
			$this->setError( patUSER_NO_DATA_GIVEN ); 
			return	false;
		}

		//	need username
		if( !$data[$this->groupFields["name"]] )
		{
			$this->setError( patUSER_NEED_GROUPNAME );
			return	false;
		}
			
		//	check whether user already exists
		if( $this->identifyGroup( array( $this->groupFields["name"] => $data[$this->groupFields["name"]] ), false ) )
		{
			$this->setError( patUSER_GROUP_ALREADY_EXISTS );
			return	false;
		}

		if( !$data[$this->groupFields["primary"]] )
			$data[$this->groupFields["primary"]]	=	$this->authDbc->nextId( $this->userIdSequence . "_groups" );

		$gid	=	$data[$this->groupFields["primary"]];
			
		//	create query
		$set	=	array();
		while( list( $field, $value ) = each( $data ) )
			if( in_array( $field, $this->groupFields ) )
				$set[]	=	$field."='".addslashes( $value )."'";
		
		$query		=	"INSERT INTO ".$this->groupTable." SET ".implode( ",", $set );
		
		$this->authDbc->query( $query );
		
		return	$gid;
	}

   /**
	*	delete an existing group
	*
	*	@access	public
	*	@param	array	$groupdata	data used to identify a group
	*/
	function	deleteGroup( $groupdata = array() )
	{
		if( !$gid	=	$this->identifyGroup( $groupdata ) )
		{
			$this->setError( patUSER_NO_GROUP_FOUND );
			return	false;
		}

		$this->removeUserFromGroup( array( "gid" => $gid, "uid" => "all" ) );

		//	delete all permissions
		$this->deletePermission( array( "id" => $gid, "id_type" => "group" ) );
		
		//	delete group
		$query	=	"DELETE FROM ".$this->groupTable." WHERE ".$this->groupFields["primary"]."='".$gid."'";
		$this->authDbc->query( $query );

		return	true;
	}
	
   /**
	*	get joined groups
	*
	*	@access	public
	*	@param	array	$options	several options (like orderby, limit, offset or uid)
	*	@return	array	$joined		array containing arrays with all data of the joined groups
	*/
	function	getJoinedGroups( $options = array() )
	{
		//	check if user id is given => no identification needed
		if( isset( $options["uid"] ) )
			$uid	=	$options["uid"];
		elseif( isset( $options[$this->authFields["primary"]] ) )
			$uid	=	$options[$this->authFields["primary"]];
		elseif( $this->isAuthenticated() )
			$uid	=	$this->getUid();
		else
		{
			$this->setError( patUSER_NEED_UID );
			return	false;
		}	
		$query		=	"SELECT ".$this->groupTable.".* FROM ".$this->groupTable.",".$this->relTable." WHERE ".$this->relTable.".".$this->relFields["gid"]."=".$this->groupTable.".".$this->groupFields["primary"]." AND ".$this->relTable.".".$this->relFields["uid"]."='".$uid."'";

		$query		.=	$this->convertOptionsToSql( $options );
		
		//	query database
		return 	$this->authDbc->getAll( $query, array(), DB_FETCHMODE_ASSOC );
	}


   /**
	*	get users in group
	*
	*	@access	public
	*	@param	array	$fields		fields to get for the users (only from authTable)
	*	@param	array	$options	several options (like orderby, limit, offset)
	*	@return	array	$users		array containing arrays with all requested data of the users in this group
	*/
	function	getUsersInGroup( $fields, $options = array() )
	{
		//	check if group id is given
		if( $options["gid"] )
			$gid	=	$options["gid"];
		elseif( $options[$this->groupFields["primary"]] )
			$gid	=	$options[$this->groupFields["primary"]];
		else
		{
			$this->setError( patUSER_NEED_GID );
			return	false;
		}

		if( is_array( $fields ) && count( $fields ) > 0 )
		{
			for( $i=0; $i<count( $fields ); $i++ )
				$fields[$i]	=	$this->authTable.".".$fields[$i];
			$fields	=	implode( ",", $fields );
		}
		else
			$fields	=	$this->authTable.".*";
			
		$query		=	"SELECT ".$fields." FROM ".$this->authTable.",".$this->relTable." WHERE ".$this->relTable.".".$this->relFields["uid"]."=".$this->authTable.".".$this->authFields["primary"]." AND ".$this->relTable.".".$this->relFields["gid"]."='".$gid."'";
		$query		.=	$this->convertOptionsToSql( $options );

		//	query database
		$data			=	$this->authDbc->getAll( $query, array(), DB_FETCHMODE_ASSOC );
		return	$data;
	}

   /**
	*	add a user to a group
	*
	*	@access	public
	*	@param	array	$relation	user id and group id
	*	@return	bool	$success	true if user could be added
	*/
	function	addUserToGroup( $relation )
	{
		//	check if group id is given
		if( $relation["gid"] )
			$gid	=	$relation["gid"];
		elseif( $relation[$this->groupFields["primary"]] )
			$gid	=	$relation[$this->groupFields["primary"]];
		else
		{
			$this->setError( patUSER_NEED_GID );
			return	false;
		}

		if( !$gid = $this->identifyGroup( array( "gid" => $gid )  ) )
			return	false;
			
		//	check if group id is given
		if( $relation["uid"] )
			$uid	=	$relation["uid"];
		elseif( $relation[$this->authFields["primary"]] )
			$uid	=	$relation[$this->authFields["primary"]];
		//	No user ID => use logged in user
		elseif( $this->isAuthenticated() )
			$uid	=	$this->getUid();
		//	No user is logged in => error!
		else
		{
			$this->setError( patUSER_NEED_UID );
			return	false;
		}

		if( $this->isMemberOfGroup( $uid, $gid ) )
		{
			$this->setError( patUSER_ALREADY_JOINED_GROUP );
			return	false;
		}

		$query	=	"INSERT INTO ".$this->relTable." SET ".$this->relFields["uid"]."='".$uid."',".$this->relFields["gid"]."='".$gid."'";
		$this->authDbc->query( $query );
		return	true;
	}

   /**
	*	delete user(s) from group(s)
	*
	*	@access	public
	*	@param	array	$relation	user id and group id (use all to delete all users / groups )
	*	@return	bool	$success	true if user could be added
	*/
	function	removeUserFromGroup( $relation )
	{
		//	check if group id is given
		if( isset( $relation["gid"] ) )
			$gid	=	$relation["gid"];
		elseif( isset( $relation[$this->groupFields["primary"]] ) )
			$gid	=	$relation[$this->groupFields["primary"]];
		else
		{
			$this->setError( patUSER_NEED_GID );
			return	false;
		}
				
		if( $gid != "all" )
		{
			if( !$gid = $this->identifyGroup( array( "gid" => $gid ) ) )
			{
				$this->setError( patUSER_NO_GROUP_FOUND );
				return	false;
			}
		}
				
		//	check if group id is given
		if( isset( $relation["uid"] ) )
			$uid	=	$relation["uid"];
		elseif( isset( $relation[$this->authFields["primary"]] ) )
			$uid	=	$relation[$this->authFields["primary"]];
		//	No user ID => use logged in user
		elseif( $this->isAuthenticated() )
			$uid	=	$this->getUid();
		//	No user is logged in => error!
		else
		{
			$this->setError( patUSER_NEED_UID );
			return	false;
		}

		//	delete user from all groups
		if( $gid == "all" )
		{
			$query	=	"DELETE FROM ".$this->relTable." WHERE ".$this->relFields["uid"]."='".$uid."'";
			$this->authDbc->query( $query );
			return	true;
		}

		if( $uid == "all" )
		{
			$query	=	"DELETE FROM ".$this->relTable." WHERE ".$this->relFields["gid"]."='".$gid."'";
			$this->authDbc->query( $query );
			return	true;
		}
		
		if( !$this->isMemberOfGroup( $uid, $gid ) )
		{
			$this->setError( patUSER_NOT_IN_GROUP );
			return	false;
		}

		$query	=	"DELETE FROM ".$this->relTable." WHERE ".$this->relFields["uid"]."='".$uid."' AND ".$this->relFields["gid"]."='".$gid."'";
		
		$this->authDbc->query( $query );
		return	true;
	}

   /**
	*	check, if a user is in a group
	*
	*	@access	public
	*	@param	int	$uid	user id
	*	@param	int	$gid	group id
	*	@return	boolean	$member	true, if user is in group, false otherwise
	*/
	function	isMemberOfGroup( $uid, $gid )
	{
		$query	=	"SELECT * FROM ".$this->relTable." WHERE ".$this->relFields["uid"]."='".$uid."' AND ".$this->relFields["gid"]."='".$gid."'";
		$result	=	$this->authDbc->query( $query );
		if( $result->numRows() == 0 )
			$member	=	false;
		else
			$member	=	true;

		$result->free();
		return	$member;
	}

   /**
	*	get all permissions of a user or all permissions
	*
	*	@access	public
	*	@param	array	$clause		params for the permissions (any fields in your permission table)
	*	@param	string	$type		get user or group permissions or both ( both|user|group|all  )
	*	@return	array	$perms		permissions of the user
	*/
	function	getPermissions( $clause = array(), $type = "both" )
	{
		if( !$this->usePermissions  )
			return false;
	
		if( !$clause = $this->checkPermissionId( $clause ) )
			return	false;

		$id			=	$clause[$this->permFields["id"]];
		$id_type	=	$clause[$this->permFields["id_type"]];

		unset( $clause[$this->permFields["id"]] );
		unset( $clause[$this->permFields["id_type"]] );
		
		$where		=	array();
		$type		=	strtolower( $type );
		//	should groups perms also be fetched?
		if( $id_type == "user" )
		{
			$tmp	=	array();
			if( $type == "group" || $type == "both" ) 
			{
				//	get all groups
				$groups		=	$this->getJoinedGroups( array( "uid" => $id ) );
				//	add where statement for each group
				for( $i=0; $i<count( $groups ); $i++ )
					$tmp[]		=	"(".$this->permFields["id"]."='".$groups[$i][$this->groupFields["primary"]]."' AND ".$this->permFields["id_type"]."='group')";
			}
			
			if( $type == "user" || $type == "both" )
			{
				//	where statement for user
				$tmp[]		=	"(".$this->permFields["id"]."='".$id."' AND ".$this->permFields["id_type"]."='user')";
			}
			
			//	put all statements in one
			if( !empty( $tmp ) )
				$where[]		=	"(" . implode( " OR ", $tmp ) . ")";
		}
		
		if( $type == "all" )
		{
			// get all permissions for all users and groups
			$where[]		=	1;
		}
		else if( $id_type == "group" )
		{
			//	only one statement needed
			$where[]	=	$this->permFields["id"]."='".$id."' AND ".$this->permFields["id_type"]."='".$id_type."'";
		}

		//	include further params in the statement
		if( is_array( $clause ) && count( $clause ) > 0 )
		{
			reset( $clause );
			while( list( $field, $value ) = each( $clause ) )
			{
				if( in_array( $field, $this->permFields ) )
					$where[]	=	"(".$field."='".$value."' OR ".$field."='')";
			}
		}
		
		//	build query
		$query		=	"SELECT ".implode( ",",$this->permFields )." FROM ".$this->permTable." WHERE ".implode( " AND ", $where );
		$perms		=	$this->authDbc->getAll( $query, array(), DB_FETCHMODE_ASSOC );
		
		//	convert set to array
		for( $i=0; $i<count( $perms ); $i++ )
			$perms[$i][$this->permFields["perms"]]		=	explode( ",", $perms[$i][$this->permFields["perms"]] );

		return	$perms;
	}

   /**
	*	check, whether user has a permission
	*
	*	@access	public
	*	@param	array	$perm			permission(s) to be checked
	*	@param	string	$mode			all or any permission (all|any)
	*	@param	boolean	$includeGroups	should group permissions be checked,too?
	*	@return	boolean	$hasPermission	true, if the user has the permission, false otherwise
	*/
	function	hasPermission( $perm = array(), $mode = "all", $includeGroups = true )
	{
		if( !$this->usePermissions  )
			return false;
	
		if( !$perm = $this->checkPermissionId( $perm ) )
			return	false;
			
		$id			=	$perm[$this->permFields["id"]];
		$id_type	=	$perm[$this->permFields["id_type"]];

		unset( $perm[$this->permFields["id"]] );
		unset( $perm[$this->permFields["id_type"]] );
		
		//	extract permissions
		$perms		=	$perm[$this->permFields["perms"]];
		unset( $perm[$this->permFields["perms"]] );

		if( !is_array( $perms ) )
			$perms	=	array( $perms );
		
		$where		=	array();

		if( count( $perms ) )
		{
			$tmp	=	array();
			for( $i=0; $i<count( $perms ); $i++ )
			{		
				if( in_array( $perms[$i], $this->perms ) )
					$tmp[]			=	"FIND_IN_SET('".$perms[$i]."', ".$this->permFields["perms"].")>0";
			}
			if( count( $tmp ) )
			{
				if( $mode == "any" )
					$where[]		=	"(".implode( " OR ", $tmp ).")";
				else
					$where[]		=	"(".implode( " AND ", $tmp ).")";
			}
		}

		//	should groups perms also be fetched?
		if( $id_type == "user" && $includeGroups )
		{
			//	get all groups
			$groups		=	$this->getJoinedGroups( array( "uid" => $id ) );
			//	where statement for user
			$tmp		=	array( "(".$this->permFields["id"]."='".$id."' AND ".$this->permFields["id_type"]."='user')" );

			//	add where statement for each group
			for( $i=0; $i<count( $groups ); $i++ )
				$tmp[]		=	"(".$this->permFields["id"]."='".$groups[$i][$this->groupFields["primary"]]."' AND ".$this->permFields["id_type"]."='group')";
			
			//	put all statements in one
			$where[]		=	"(" . implode( " OR ", $tmp ) . ")";
		}
		else
			//	only one statement needed
			$where[]	=	$this->permFields["id"]."='".$id."' AND ".$this->permFields["id_type"]."='".$id_type."'";

			
		
		//	include further params in the statement
		if( is_array( $perm ) && count( $perm ) > 0 )
		{
			reset( $perm );
			while( list( $field, $value ) = each( $perm ) )
			{
				if( in_array( $field, $this->permFields ) )
					$where[]	=	"(".$field."='".$value."' OR ".$field."='')";
			}
		}
			
		$hasPermission	=	false;
		//	build query
		$query		=	"SELECT ".implode( ",",$this->permFields )." FROM ". $this->permTable ." WHERE ".implode( " AND ", $where );
		$result		=	$this->authDbc->query( $query );

		if( !DB::isError( $result ) &&  $result->numRows() > 0 )
			$hasPermission	=	true;

		$result->free();
		
		return	$hasPermission;		
	}
	
   /**
	*	add permission(s)
	*
	*	@access	public
	*	@param	array	$perm		array containing the permission(s) to be added
	*	@return	bool	$success	true if the permission could be added, false otherwise
	*/
	function	addPermission( $perm )
	{
		if( !$this->usePermissions  )
			return false;
	
		if( !$perm = $this->checkPermissionId( $perm ) )
			return	false;

		$perm[$this->permFields["perms"]]	=	$this->getOldPermission( $perm ) | $this->convertPermToInt( $perm[$this->permFields["perms"]] );
		$this->setPermission( $perm );

		return	true;
	}

   /**
	*	delete permission(s)
	*
	*	@access	public
	*	@param	array	$perm		array containing the permission(s) to be deleted
	*	@return	bool	$success	true if the permission could be deleted, false otherwise
	*/
	function	deletePermission( $perm )
	{
		if( !$this->usePermissions  )
			return false;
	
		if( !$perm = $this->checkPermissionId( $perm ) )
			return	false;

		$perm[$this->permFields["perms"]]	=	$this->convertPermToInt( $perm[$this->permFields["perms"]] );

		if( $perm[$this->permFields["perms"]] )
		{
			$perm[$this->permFields["perms"]]	=	$this->getOldPermission( $perm ) & ~$this->convertPermToInt( $perm[$this->permFields["perms"]] );
			return $this->setPermission( $perm );
		}
		else
			return $this->deleteAllPermissions( $perm );
	}

   /**
	*	get old permission
	*
	*	@access	private
	*	@param	array	$perm	array that defines the permission
	*	@return	int		$old	old permissions for this entry		
	*/
	function	getOldPermission( $perm )
	{
		if( !$this->usePermissions  )
			return false;
	
		$fields		=	array_values( $this->permFields );
		for( $i=0; $i<count( $fields ); $i++ )
			if( !isset( $perm[$fields[$i]] ) )
				$perm[$fields[$i]]	=	"";

		unset( $perm[$this->permFields["perms"]] );
				
		//	delete old permissions
		$where		=	array();
		reset( $perm );
		while( list( $field, $value ) = each( $perm ) )
			if( in_array( $field, $this->permFields ) )
				$where[]	=	$field."='".$value."'";
		
		$query	=	"SELECT ".$this->permFields["perms"]."+0 FROM ".$this->permTable." WHERE ".implode( " AND ", $where );
		$result	=	$this->authDbc->query( $query );
		
		$old	=	0;
		while( $row = $result->fetchRow() )
		{
			$old	=	$old | $row[0];
		}
			
		$result->free();
		return	$old;
	}

   /**
	*	delete all permissions without setting new ones
	*
	*	@access	public
	*	@param	array	$clause	array containing conditions for the where clause
	*	@return	bool	$success	true on success, false otherwise
	*/
	function	deleteAllPermissions( $clause )
	{
		if( !$this->usePermissions  )
			return false;
	
		if( !$clause = $this->checkPermissionId( $clause ) )
			return	false;

		unset( $clause[$this->permFields["perms"]] );
			
		$where		=	array();
		reset( $clause );
		while( list( $field, $value ) = each( $clause ) )
			if( in_array( $field, $this->permFields ) )
				$where[]	=	$field."='".$value."'";
				
		$query	=	"DELETE FROM ".$this->permTable." WHERE ".implode( " AND ", $where );
		$this->authDbc->query( $query );
		return	true;
	}

   /**
	*	check, whether id and id_type are set correctly
	*
	*	@access	private
	*	@param	array	$perm	array containg fields/values to identify the permission
	*	@return	bool	$success
	*/
	
	function	checkPermissionId( $perm )
	{
		//	check if  id is given
		if( isset( $perm["id"] ) )
			$id		=	$perm["id"];
		elseif( isset( $perm[$this->permFields["id"]] ) )
			$id		=	$perm[$this->permFields["id"]];
		//	No user ID => use logged in user
		elseif( $this->isAuthenticated() )
		{
			$id									=	$this->getUid();
			$perm[$this->permFields["id_type"]]	=	"user";
		}
		//	No user is logged in => error!
		else
		{
			$this->setError( patUSER_NEED_ID );
			return	false;
		}

		//	check for id_type
		if( $perm["id_type"] )
			$id_type		=	$perm["id_type"];
		elseif( $perm[$this->permFields["id_type"]] )
			$id_type		=	$perm[$this->permFields["id_type"]];
		else
		{
			$this->setError( patUSER_NEED_ID_TYPE );
			return	false;
		}

		unset( $perm["id"] );
		unset( $perm["id_type"] );

		$perm[$this->permFields["id"]]		=	$id;
		$perm[$this->permFields["id_type"]]	=	$id_type;

		return	$perm;
	}
	
   /**
	*	set a permission
	*
	*	@access	private
	*	@param	array	$perm	permission that should be set
	*/
	function	setPermission( $perm )
	{
		if( !$this->usePermissions  )
			return false;
	
		$perm[$this->permFields["perms"]]	=	$this->convertPermToInt( $perm[$this->permFields["perms"]] );
		
		$fields		=	array_values( $this->permFields );
		for( $i=0; $i<count( $fields ); $i++ )
			if( !isset( $perm[$fields[$i]] ) )
				$perm[$fields[$i]]	=	"";

		$permdel	=	$perm;
		unset( $permdel[$this->permFields["perms"]] );
		//	delete old permissions
		$where		=	array();
		reset( $permdel );
		while( list( $field, $value ) = each( $permdel ) )
			if( in_array( $field, $this->permFields ) )
				$where[]	=	$field."='".$value."'";

		$query		=	"DELETE FROM ".$this->permTable." WHERE ".implode( " AND ", $where );
		$this->authDbc->query( $query );
		
		if( $perm[$this->permFields["perms"]] == 0 )
			return	true;
		
		//	insert new permissions
		$set		=	array();
		reset( $perm );
		while( list( $field, $value ) = each( $perm ) )
			if( in_array( $field, $this->permFields ) )
				if( is_int( $value ) )
					$set[]	=	$field."=".addslashes( $value );
				else
					$set[]	=	$field."='".addslashes( $value )."'";
					
		$query		=	"INSERT INTO ".$this->permTable." SET ".implode( ",", $set );
		$this->authDbc->query( $query );
	
		return true;
	}

   /**
	*	convert	permission to int
	*
	*	@access	private
	*	@param	mixed	$perm		permission to convert
	*	@return	int		$int		permission as integer value
	*/
	function	convertPermToInt( $perm )
	{
		if( is_int( $perm ) )
			return	$perm;
			
		if( is_string( $perm ) )
			$perm	=	explode( ",", $perm );

		if( count( $this->permsConv ) == 0 )
			$this->buildPermsConv();
			
		$int		=	0;
		for( $i=0; $i<count( $perm ); $i++ )
			$int	=	$int | $this->permsConv[$perm[$i]];
		
		return	$int;	
	}

   /**
	*	convert	int to permission
	*
	*	@access	private
	*	@param	int		$int		integer value to convert
	*	@return	array	$perm		converted permission
	*/
	function	convertIntToPerm( $int )
	{
		if( is_array( $int ) )
			return	implode( ",", $int );
			
		$perm	=	array();
		reset( $this->perms );
		while( list( $key, $value ) = each( $this->perms ) )
		{
			if( $int & $key )
				array_push( $perm, $value );
		}
		
		return	implode( ",", $perm );
	}

   /**
	*	build conversion array
	*
	*	@access	private
	*/
	function	buildPermsConv()
	{
		reset( $this->perms );
		while( list( $key, $value ) = each( $this->perms ) )
			$this->permsConv[$value]	=	$key;
	}
	
   /**
	*	update all statistics
	*	
	*	@access	public
	*/
	function	updateStats()
	{
		//	need an authenticated user
		if( !$this->isAuthenticated() )
			return	false;

		//	Do not update stats more than once
		if( $this->statsUpdated )
			return	true;

		$set	=	array();

		//	count accessed pages?
		if( $this->stats["count_pages"] )
			$set[]	=	$this->stats["count_pages"]."=".$this->stats["count_pages"]."+1";

		//	calulate time online
		if( $this->stats["time_online"] && $this->useSessions )
		{
			$last_access	=	$this->getSessionValue( "patUserLastAccess" );
			if( $last_access )
			{
				$set[]		=	$this->stats["time_online"]."=".$this->stats["time_online"]."+".( time() - $last_access );
			}
			$this->storeSessionValue( "patUserLastAccess", time() );
		}
			
		//	anything needs to be updated?
		if( count( $set ) > 0 )
		{
			$query		=	"UPDATE ".$this->authTable." SET ".implode( ",", $set )." WHERE ".$this->authFields["primary"]."='".$this->uid."'";
			$this->authDbc->query( $query );
		}

		$this->statsUpdated	=	true;
	}


   /**
	*	build a select query
	*
	*	@access	private
	*	@param	mixed	name of the table(s)
	*	@param	array	$fields		array containing fieldnames to be fetched
	*	@param	array	$clause		array containing conditions for the where statement
	*	@param	array	$options	array containing misc options
	*	@return	string	$query		sql query
	*/
	function	buildSelectQuery( $table, $fields = array(), $clause = array(), $options = array() )
	{
		//	get list of fields for SELECT
		if( is_array( $fields ) && count( $fields ) > 0 )
			$fields	=	implode( ",", $fields );
		else
			$fields	=	"*";

		$where	=	array();
		//	Build where clause
		if( is_array( $clause ) )
		{
			for( $i=0; $i<count( $clause ); $i++ )
				$where[]	=	$this->buildWhereStatement( $clause[$i]["field"], $clause[$i]["value"], $clause[$i]["match"] );
		}
		
		//	build the query
		$query		=	"SELECT ".$fields." FROM ".$table;
		if( count( $where ) > 0 )
		{
			if( isset( $options["conditions"] ) && strtolower( $options["conditions"] ) == "any" )
				$query	.=	" WHERE ".implode( " OR ", $where );
			else
				$query	.=	" WHERE ".implode( " AND ", $where );
		}
				
		$query		.=	$this->convertOptionsToSql( $options );
	
		return	$query;
	}


   /**
	*	get information about table fields
	*	like DESCRIBE [table]
	* 
	*	@access	public
	*	@param	string	$tablename	internal table name
	*	@return	array	$desc	table description
	*/
	function getTableInfo( $tablename )
	{
		$table		=	isset( $this->tables[$tablename] ) ? $this->tables[$tablename]["table"] : $this->authTable;

		$dbc	=	&$this->getDbc( $tablename );
	
		// get table info
		$query	=	"SHOW COLUMNS FROM ". $table . ";"; 
		if( !$result	=	$dbc->query( $query ) ) 
		{
			$this->setError( patUSER_NO_DB_RESULT );
			return false;
		}
		$data	=	$result->get_result( patDBC_TYPEASSOC );
		$result->free();
		
		// search field
		reset( $data );
		$desc	=	array();
		for( $i = 0; $i < count( $data ); $i++ )
		{
			$desc[$i]	=	array( 
									"field"		=>	$data[$i]["Field"],
									"type"		=>	"unknown",
									"type_info"	=>	false,
									"null"		=>	$data[$i]["Null"],
									"key"		=>	$data[$i]["Key"],
									"default"	=>	$data[$i]["Default"],
									"extra"		=>	$data[$i]["Extra"]
								); 
			unset( $match );
	
			// get "type" and "type_info"
			// SET or ENUM
			if( preg_match( "/(set|enum)\((\'.*\',?)*\)/", $data[$i]["Type"], $match ) )
			{
				$desc[$i]["type"]			=	$match[1];
				$match						=	explode( ",", $match[2] );
				for ( $j = 0; $j < count( $match ); $j++ )
					$match[$j] = substr( $match[$j], 1, -1 );
	
				$desc[$i]["type_info"]	=	$match;
				continue;
			}
	
			// TINYINT, INT, CHAR, VARCHAR
			if ( preg_match( "/(int|tinyint|char|varchar)\((.*)\)/", $data[$i]["Type"], $match ) )
			{
				$desc[$i]["type"]					=	$match[1];
				$desc[$i]["type_info"]["length"]	=	$match[2];
				continue;
			}
			
			// default
			$desc[$i]["type"] = $data[$i]["Type"];
		}
		return $desc;
	}

   /**
	*	build a where statement
	*
	*	@access	private
	*	@param	string	$field	fieldname
	*	@param	mixed	$value	value of the field
	*	@param	string	$match	match type
	*/
	function	buildWhereStatement( $field, $value, $match = "exact" )
	{
		switch( $match )
		{
			case	">":
			case	"greater":
				$statement	=	$field.">'".$value."'";
				break;

			case	">=":
			case	"greaterorequal":
				$statement	=	$field.">='".$value."'";
				break;

			case	"<":
			case	"lower":
			case	"less":
				$statement	=	$field."<'".$value."'";
				break;

			case	"<=":
			case	"lessorequal":
			case	"lowerorequal":
				$statement	=	$field."<='".$value."'";
				break;

			case	"like":
				$statement	=	$field." LIKE '".$value."'";
				break;

			case	"contains":
				$statement	=	$field." LIKE '%".$value."%'";
				break;

			case	"startswith":
				$statement	=	$field." LIKE '".$value."%'";
				break;

			case	"endswith":
				$statement	=	$field." LIKE '%".$value."'";
				break;

			case	"between":
				$statement	=	"(".$field." BETWEEN '".$value[0]."' AND '".$value[1]."' OR ".$field." LIKE '".$value[1]."%')";
				break;

			case	"exact":
			case	"=":
				$statement	=	$field."='".$value."'";
				break;

			case	"neq":
			case	"!=":
			case	"<>":
			default:
				$statement	=	$field."<>'".$value."'";
				break;

		}
		return	$statement;
	}

   /**
	*	send query to any database
	*	can be used the use the internal dbcs in your own apps without knowing how the user object was confugured
	*
	*	@access	public
	*	@param	string	$dbc			name of the database
	*	@param	string	$query			query
	*	@return	object	DB_Result	$result	result of the query
	*/
	function	&sendQuery( $dbc, $query )
	{
		if( $dbc == "authDbc" )
			$result		=	$this->authDbc->query( $query );
		else
		{
			if( !isset( $this->dbcs[$dbc] ) )
				return	false;

			$result		=	$this->dbcs[$dbc]->query( $query );
		}
		return	$result;
	}

   /**
	*	convert options to sql
	*
	*	access	private
	*	@param	array	$options	assoc array containing all options
	*	@return	string	$sql		sql representation of the options
	*/
	function	convertOptionsToSql( $options = array() )
	{
		$sql	=	"";

		if( isset( $options["orderby"] ) )
			$sql	.=	" ORDER BY ".$options["orderby"];

		if( isset( $options["limit"] ) || isset( $options["offset"] ) )
		{
			$options["offset"]	=	isset( $options["offset"] ) ? $options["offset"] : 0;
			$options["limit"]	=	isset( $options["limit"] ) ? $options["limit"] : 1;

			$sql	.=	" LIMIT ".$options["offset"].",".$options["limit"];
		}
		return	$sql;
	}
	
   /**
	*	create url for PHP_SELF
	*
	*	@access	private
	*	@return	sting	$self	url to reference to the page itself
	*/
	function	getSelfUrl()
	{
		if( $this->getPHPVersion() >= 4.1 )
		{
			$vars	=	array_merge( $_GET, $_POST );
			$self	=	$_SERVER["PHP_SELF"]."?".SID;
		}
		else
		{
			global	$PHP_SELF, $HTTP_GET_VARS, $HTTP_POST_VARS;
			$vars	=	array_merge( $HTTP_GET_VARS, $HTTP_POST_VARS );
			$self	=	$PHP_SELF."?".SID;
		}
	
		if( is_array( $vars ) )
		{
			while( list( $key, $val ) = each( $vars ) )
				if( $val != session_id() && !in_array( $key, $this->ignoreVars ) && !in_array( $key, $this->authFields ) )
					$self	.=	"&".$key."=".$val;
		}
				
		return	$self;	
	}

   /**
	*	get the dbc object for a table
	*
	*	@param		string			$table	name of the table
	*	@return		object patDbc	$dbc	reference to the dbc object
	*/
	function	&getDbc( $table ) 
	{
		//	get dbc for the query
		if( isset( $this->tables[$table] ) && isset( $this->dbcs[$this->tables[$table]["dbc"]] ) )
			$dbc		=	&$this->dbcs[$this->tables[$table]["dbc"]];
		else
			$dbc		=	&$this->authDbc;

		return	$dbc;
	}
	
	
   /**
	*	set error code
	*
	*	@param	int	$error	error code
	*/
	function	setError( $error )
	{
		array_push( $this->errors, $error );
	}

   /**
	*	remove the last error from the list
	*
	*	@access	public
	*/
	function	removeLastError()
	{
		array_pop( $this->errors );
	}

   /**
	*	clear all errors
	*
	*	@access	public
	*/
	function	clearErrors()
	{
		$this->errors	=	array();
	}

   /**
	*	get the last error code
	*
	*	@access	public
	*	@return	integer		$code		last error code
	*/
	function	getLastErrorCode()
	{
		return	$this->errors[(count( $this->errors )-1)];
	}
	
   /**
	*	get the last error message
	*
	*	@access	public
	*	@return	string		$message		last error message
	*/
	function	getLastErrorMessage()
	{
		return	$this->translateErrorCode( $this->errors[(count( $this->errors )-1)] );
	}
	
   /**
	*	get the last error code AND message
	*
	*	@access	public
	*	@return	array		$error		last error code AND message
	*/
	function	getLastError()
	{
		return	array(	"code"		=>	$this->errors[(count( $this->errors )-1)],
						"message"	=>	$this->translateErrorCode( $this->errors[(count( $this->errors )-1)] ) );
	}
	
   /**
	*	get all error codes
	*
	*	@access	public
	*	@return	array	$errors	array containing all error codes
	*/
	function	getAllErrorCodes()
	{
		return	$this->errors;
	}

   /**
	*	get all error messages
	*
	*	@access	public
	*	@return	array	$errors	array containing all error messages
	*/
	function	getAllErrorMessages()
	{
		$messages	=	array();
		for( $i = 0;$i<count( $this->errors ); $i++ )
			$messages[$i]	=	$this->translateErrorCode( $this->errors[$i] );

		return	$messages;
	}

   /**
	*	get all error codes AND messages
	*
	*	@access	public
	*	@return	array	$errors	array containing all error codes AND messages
	*/
	function	getAllErrors()
	{
		$errors	=	array();
		for( $i = 0;$i<count( $this->errors ); $i++ )
		{
			$errors[$i]["code"]		=	$this->errors[$i];
			$errors[$i]["message"]	=	$this->translateErrorCode( $this->errors[$i] );
		}
		return	$errors;
	}

  /**
	*	translate an error code
	*
	*	@access	public
	*	@param	int		$code		error code
	*	@return	string	$message	error message for the code
	*/
	function	translateErrorCode( $code )
	{
		return	$this->errorMessages[$code];
	}

   /**
	*	gets the current PHP version as a floating point number
	*
	*	@access	private
	*	@return	float	$version	PHP version
	*/
	function	getPHPVersion()
	{
		$regs		=	array();
		if( preg_match( "/^([0-9])\.([0-9])/", phpversion(), $regs ) )
		{
			$version	=	(float)( $regs[1] . "." . $regs[2] );
		}
		else
			$version	=	2;

		return	$version;
	}

   /**
	*	encrypt a password using the specified encryption function
	*
	*	@access	public
	*	@param	string	$passwd	password
	*	@param	string	$salt	salt
	*	@return	string	$passwd	encrypted password
	*	@see	setCryptFunction()
	*/
	function	encryptPasswd( $passwd, $salt = false )
	{
		if( $this->cryptFunction )
		{
			switch( $this->cryptFunction )
			{
				case	"crypt":
					if( $salt === false )
						$salt	=	$this->generateSaltValue();
						
					$passwd	=	call_user_func( $this->cryptFunction, $passwd, $salt );
					break;
				default:
					$passwd	=	call_user_func( $this->cryptFunction, $passwd );
					break;
			}
		}
		return	$passwd;
	}
	
   /**
	* generate a salt
	*
	*	According to man(3) crypt:
	*	The salt is a two-character string chosen from the set [a-zA-Z0-9./]; 
	*	this string is used to perturb the hashing algorithm in one of 4096 different ways.
	* 
	*
	*	@access private
	*	@return string $salt randomly generated two-character salt value.
	*/
	function generateSaltValue() 
	{
		$saltValues = range('a', 'z');
		$saltValues = array_merge($saltValues, range('A', 'Z'));
		$saltValues = array_merge($saltValues, range('0', '9'));
		$saltValues[] = '.';
		$saltValues[] = '/';		
		
		return $saltValues[rand(0, count($saltValues))] . $saltValues[rand(0, count($saltValues))];
	}
}
?>
