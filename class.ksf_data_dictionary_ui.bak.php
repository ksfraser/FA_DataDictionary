<?php

/****************************************************************//**
 *data_dictionary module UI
 *
 *	Purpose of this module is to alter Front Accounting tables
 *	so that column widths are large enough for the modules
 *	we have added.
 *
 *	We will have a form to launch the table alters.
 *
 *	We will take an array of tables, their column name,
 *	and the new sizes/attributes.
 *
 *	The form will launch an ALTER TABLES set of queries.
 *	Probably should set it to be ATOMIC so that if an update
 *	fails they are all rolled back.  However, fields too large
 *	is less of a problem than too small.
 *
 * ******************************************************************/

//require_once( 'class.generic_orders.php' ); 
//
require_once( '../ksf_modules_common/class.table_interface.php' );
require_once( '../ksf_modules_common/defines.inc.php' );


require_once( '../ksf_modules_common/class.origin_ui.php' );
require_once( '../ksf_modules_common/class.generic_fa_interface.php' ); 
require_once( 'class.ksf_data_dictionary.php' ); 

class ksf_data_dictionary_ui extends origin_ui
{
	var $caller;	//!< which class called us.
	var $data_dictionary;
	function __construct(  $host, $user, $pass, $database, $pref_tablename , $caller = null )
	{
		simple_page_mode(true);
		global $db;
		$this->set_var( 'caller', $caller );
		$this->set_var( 'db', $db );
		$this->data_dictionary = new ksf_data_dictionary( $host, $user, $pass, $database, $pref_tablename, $this );

		$this->data_dictionary->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		
		//The forms/actions for this module
		//Hidden tabs are just action handlers, without accompying GUI elements.
		//$this->tabs[] = array( 'title' => '', 'action' => '', 'form' => '', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Install Module', 'action' => 'create', 'form' => 'install', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'checkprefs', 'hidden' => TRUE );

		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Init Tables', 'action' => 'init_tables_form', 'form' => 'init_tables_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Init Tables Completed', 'action' => 'init_tables_complete_form', 'form' => 'init_tables_complete_form', 'hidden' => TRUE );
	
		//$this->tabs[] = array( 'title' => 'data_dictionary create', 'action' => 'create_data_dictionary_form', 'form' => 'create_data_dictionary_form', 'hidden' => FALSE );
		//$this->tabs[] = array( 'title' => 'data_dictionary created', 'action' => 'created_data_dictionary_form', 'form' => 'created_data_dictionary_form', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Alter Tables', 'action' => 'alter_tables_form', 'form' => 'alter_tables_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Alter Tables Completed', 'action' => 'alter_tables_complete_form', 'form' => 'alter_tables_complete_form', 'hidden' => TRUE );
	}

	/****************************************************************************//**
	 *is_installed
	 *
	 *
	 * ******************************************************************************/
	function is_installed()
	{
		return $this->data_dictionary->is_installed();
	}
	/****************************************************************************//**
	 *run
	 *
	 *
	 * ******************************************************************************/
	function run()
	{
		$this->data_dictionary->found = $this->found;
		$this->data_dictionary->help_context = $this->help_context;
		$this->data_dictionary->redirect_to = $this->redirect_to;
		return $this->data_dictionary->run();
	}

	/****************************************************************************//**
	 *Inherited
	 *show_config_form()
	 *
	 * ******************************************************************************/
	function show_config_form()
	{
		$this->data_dictionary->show_config_form();
	}
	/****************************************************************************//**
	 *create_data_dictionary_form
	 *
	 * Display a form on screen for generating a data_dictionary
	 *
	 * ******************************************************************************/
	function create_data_dictionary_form()
	{
		start_form();

		start_table();
		table_section_title(_("data_dictionary entry form"));
	//	label_row(_("No Transaction History (no inventory movement):"), NULL);
		label_row("&nbsp;", NULL);
		table_section(1);
		foreach( $this->fields_array as $field )
		{
			$name = $field['name'];
			if( isset( $field['readwrite'] ) )
			{
				if( $field['readwrite'] == 'read' )
				{
					//READ ONLY
					label_row( $name . "(RO)", $this->$name );
				}
				else
				{
					$this->in_table_display( $field );
				}
			}
			else
			{
				$this->in_table_display( $field );
			}
		}
		end_table();
		end_form();
	}

	function init_tables_complete_form()
	{
		$createdcount = 0;
		//assumption create_table will return TRUE on success
		if( $this->data_dictionary->create_table() )
		{
			$createdcount++;
		}
     		display_notification("init tables complete form created " . $createdcount . " tables");
	}
	function created_data_dictionary_form()
	{
     		display_notification("Coupon created?");
	}
	function init_tables_form()
	{
            	display_notification("init tables form");
		$this->call_table( 'init_tables_complete_form', "Init Tables" );
	}
	function alter_tables_form()
	{
		echo "alter tables form<br />";
            	display_notification("alter tables form");
		$this->call_table( 'alter_tables_complete_form', "Alter Tables" );
	}
	function alter_tables_complete_form()
	{
		foreach( $this->data_dictionary->alters_needed as $callback )
		{
			if( is_callable( $this->data_dictionary->$callback() ) )
			{
				$this->data_dictionary->$callback();
			}
		}

	}
}
/**********************************************************************//**
 * class origin_ui
 *
 * Common processes for UI classes
 *
 * Functions:
 * 	fields_array2var
 * 	in_table_display
 * 	call_table
 * Inherited: (not guaranteed to be complete due to changes in inherited class(es)
 * *************************************************************************/
//renamed to not clash with origin_ui.
class origin_ui2 extends generic_fa_interface
{
	/**********************************************************************************//**
	 *
	 * 
	 * @param object client object needing values set
	 * **************************************************************************************/
	function __construct( $client )
	{
		$this->client = $client;
	}
	/*********************************************************************************//**
	 *fields_array2var
	 *	Take the data out of POST variables and put them into
	 *	the variables defined as table columns (fields_array)
	 *
	 * 
	 *	@returns int count of fields set
	 *
	 * ***********************************************************************************/
	/*@int@*/function fields_array2var()
	{
		$count = 0;
		$this->client->reset_values();
		foreach( $this->client->fields_array as $row )
		{
			$var = $row['name'];
			if( isset( $_POST[$var] ) )
			{
				$this->client->$var = $_POST[$var];
				$count++;
			}
		}
		return $count;
	}
	/****************************************************************************//**
	 *in_table_display
	 *
	 * @param array display on the screen, within a table, 1 row as specified by the array
	 *
	 * ******************************************************************************/
	function in_table_display( $field_array )
	{
		echo "in table display<br />";
		//ASSUMPTION we've already checked the readwrite attribute
		//and this is a writeable fields
		if( strncmp( $field_array['type'], "varchar", 7 ) == 0 
			OR strncmp( $field_array['type'], "int", 3 ) == 0 
		  )
		{
			label_row( $name . "(VC)", $this->$name );
		}
		else
		if( strncmp( $field_array['type'], "timestamp", 7 ) == 0 
			OR strncmp( $field_array['type'], "datetime", 7 ) == 0 
		  )
		{
			label_row( $name . "(DT)", $this->$name );
		}
		else
		if( strncmp( $field_array['type'], "boolean", 7 ) == 0 

		  )
		{
			label_row( $name . "(bool)", $this->$name );
		}

	}
	/************************************************//**
	 *call_table
	 *
	 * 	Puts a table on the screen with a button
	 * 	to act as a "Are you sure" type of screen
	 * 	so that the user has to init the action.
	 *
	 *@param action routine (next screen) to call
	 *@param msg the message to be displayed on the button to push
	 *@returns NOTHING
	 * **************************************************/
	function call_table( $action, $msg )
	{
		echo "call table<br />";
                start_form(true);
                 start_table(TABLESTYLE2, "width=40%");
                 table_section_title( $msg );
                 hidden('action', $action );
                 end_table(1);
                 submit_center( $action, $msg );
                 end_form();
	}
}


?>
