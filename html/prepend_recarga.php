<?php
/**
 *	patUser prepend
 */
//error_reporting( E_ALL );

	if(!isset($_SESSION)) {
		ini_set('session.use_trans_sid', 0);
		ini_set('session.use_only_cookies', 1);
	}


	// change the data-source-name to fit your needs - see documentation at http://pear.php.net
	$dsn	=	"mysql://root@localhost/numerocentral";
	
	//	patTemplate is used for login screen
	include_once( "patTemplate.php" );
	$tmpl	=	new	patTemplate();
	$tmpl->setBasedir( "templates_recarga" );
	
	
	//	user management class
	include_once( "patUser.php" );
	//	Work with session, use global var $patUserData
	$user	=	new	patUser( true, "patUserData" );

	//	set access to main database


	//	database that contains auth table with all important user data
	include_once( "DB.php" );
	

	// either set db-object...
	$authDbc 	=&	DB::connect( $dsn );
	if( DB::isError( $authDbc ) )
	{
		echo "<b>Database connection failed</b><br>";
		echo "<pre>";
		print_r( $authDbc );
		echo "</pre>";	
		die( "Database connection failed" );
	}		
	$user->setAuthDbc( $authDbc );

/*		
	// or setup authdbc by dsn
	$authDbc	=	$user->setAuthDbc( $dsn );
	if( DB::isError( $authDbc ) )
	{
		echo "<b>Database connection failed</b><br>";
		echo "<pre>";
		print_r( $authDbc );
		echo "</pre>";	
		die( "Database connection failed" );
	}
*/


	//	this table stores all users
	$user->setAuthTable( "users" );

	//	set required fieldnames
	$user->setAuthFields( array(	"primary"	=>	"uid",
									"username"	=>	"username",
									"passwd"	=>	"passwd" ) );

	//	patTemplate object for Login screen
	//	can be left out if you want to use HTTP authentication
	$user->setTemplate( $tmpl );

	//	maximum login attempts
	$user->setMaxLoginAttempts( 35 );

	$user -> setCryptFunction ( 'md5' );

	function getpost_ifset ( $test_vars ) {
		if( !is_array( $test_vars ) )
	                $test_vars = array( $test_vars );

	        foreach( $test_vars as $test_var ) {
	                if ( isset( $_POST[$test_var] ) ) {
	                        global $$test_var;
	                        $$test_var = $_POST[$test_var];
	                } elseif ( isset( $_GET[$test_var] ) ) {
	                        global $$test_var;
	                        $$test_var = $_GET[$test_var];
	                } elseif ( isset( $_REQUEST[$test_var] ) ) {
	                        global $$test_var;
	                        $$test_var = $_REQUEST[$test_var];
	                } elseif ( isset( $HTTP_GET_VARS[$test_var] ) ) {
	                        global $$test_var;
	                        $$test_var = $HTTP_GET_VARS[$test_var];
	                } else {
	                        global $$test_var;
	                }
	        }
	}
?>
