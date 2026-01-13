<?php
/**********************************************
Name: COAST purchase order export
modified for COAST 1.5.1 and FrontAccounting 2.3.15 by kfraser 
Free software under GNU GPL
***********************************************/

$page_security = 'SA_ksf_data_dictionary';
$path_to_root="../..";

include($path_to_root . "/includes/session.inc");
add_access_extensions();
set_ext_domain('modules/ksf_data_dictionary');

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/modules/ksf_data_dictionary/class.ksf_data_dictionary.php");
include_once($path_to_root . "/modules/ksf_data_dictionary/class.ksf_data_dictionary_ui.php");
include_once($path_to_root . "/modules/ksf_modules_common/class.eventloop.php");

error_reporting(E_ALL);
ini_set("display_errors", "on");

global $db; // Allow access to the FA database connection
$debug_sql = 0;  // Change to 1 for debug messages
$prefsDB = "ksf_data_dictionary_prefs";

	echo "p0";

if( isset( $_POST['edit_form'] ) AND isset( $_POST['my_class']  ) )
{
	//AJAX call
	$cl = $_POST['my_class'];	//set in woo_interface
	require_once( 'class.' . $cl . '.php' );
	$mycl = new $cl( null, null, null, null, null );
	$mycl->form_post_handler();
	$_GET['action'] = $_POST['action'] = $_POST['return_to'];
	unset( $_POST );
	header("Status: 301 Moved Permanently");
	header("Location: " . $_SERVER['REQUEST_URI'] . ($_GET ? "?" . http_build_query( $_GET ) : "" ) );
	

}
else
{
	//$eventloop = new eventloop( "." );
	echo "p1";
	$ksf_data_dictionary_ui = new ksf_data_dictionary_ui( "fhsws002.ksfraser.com", "fhs", "fhs", "devel_fhs", "ksf_data_dictionary_prefs" );
	echo "p2";
	$found = $ksf_data_dictionary_ui->is_installed();
	echo "p3";
	$ksf_data_dictionary_ui->set_var( 'found', $found );
	echo "p4";
	$ksf_data_dictionary_ui->set_var( 'help_context', "ksf_data_dictionary Interface" );
	echo "p5";
	$ksf_data_dictionary_ui->set_var( 'redirect_to', "ksf_data_dictionary.php" );
	echo "p6";
	$ksf_data_dictionary_ui->run();
	echo "p7";
}

?>
