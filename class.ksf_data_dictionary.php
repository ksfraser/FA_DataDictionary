<?php

/****************************************************************//**
 *data_dictionary module
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
 *	Eventually I might add a table to store the alterations,
 *	as well as a screen to populate the table.
 *
 * ******************************************************************/

//require_once( 'class.generic_orders.php' ); 
//
require_once( '../ksf_modules_common/class.table_interface.php' );
require_once( '../ksf_modules_common/defines.inc.php' );


require_once( '../ksf_modules_common/class.generic_fa_interface.php' ); 

//class ksf_data_dictionary
//class ksf_data_dictionary extends generic_orders
/*********************************************************************************//**
 *ksf_data_dictionary extends generic_fa_interface
 *
 *	This class can be extended in a number of ways.
 *	$this->alters_needed needs to have any field defining routine added since
 *	it is called by the UI screen.
 *
 * 	You could simply add a function and update alters_needed.
 *
 *	You can extend the class, creating a new function that defines the table.
 *	You would also need to alter the UI class to find the extended class...
 *
 *	You could alter the routine fix_stock_id_size by adding more tables into the
 *	array definition.
 *
 *	You could use this class and call $this->modify_table_column() with your own 
 *	table definition.
 *
 * **************************************************************************************/
class ksf_data_dictionary extends generic_fa_interface
{
	//*****************************************************************************************************************
	var $db;
	var $ck;
	var $cs;
	var $server;
	var $rest_path;
	var $environment;
	var $debug;
	var $alters_needed;
	function __construct( $host, $user, $pass, $database, $pref_tablename, $ui_caller )
	{
		parent::__construct( $host, $user, $pass, $database, $pref_tablename );
		$this->alters_needed[] = "fix_stock_id_size";
		echo "ksf_d_d_constr";
		$this->ui_class = $ui_caller;
	}
	function define_table()
	{
		$this->fields_array[] = array('name' => 'ksf_data_dictionary_id', 	'type' => 'int(11)', 		'comment' => 'Index.', 'readwrite' => 'read', 'auto_increment' => 'anything');
		$this->fields_array[] = array('name' => 'updated_ts', 		'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP', 'readwrite' => 'read');
		$this->fields_array[] = array('name' => 'mandatory', 		'type' => 'boolean', 	'comment' => 'Mandatory', 'readwrite' => 'readwrite'); 	
		$this->fields_array[] = array('name' => 'date_created', 	'type' => 'datetime', 		'null' => 'NOT NULL',  'readwrite' => 'read'); 	
		$this->fields_array[] = array('name' => 'date_modified', 	'type' => 'datetime', 		'null' => 'NOT NULL', 'readwrite' => 'read'); 	

		//$this->fields_array[] = array('name' => 'description', 		'type' => 'varchar(64)', 	'comment' => 'Coupon Description.', 'readwrite' => 'readwrite'); 	
		//$this->fields_array[] = array('name' => 'product_ids', 		array 	List of product ID’s the data_dictionary can be used on.
		
		$this->table_details['tablename'] = $this->company_prefix . "ksf_data_dictionary";
		$this->table_details['primarykey'] = "ksf_data_dictionary_id";
		//$this->table_details['index'][0]['type'] = 'unique';
		//$this->table_details['index'][0]['columns'] = "id,code";
		//$this->table_details['index'][0]['keyname'] = "id-code";
	}
	/*******************************************************************************************//**
	 *fix_stock_id_size
	 *
	 *	Because of variation products (have attributes such as size and color) where 
	 *	everything else is the same, we want mostly human readable SKUs so that we
	 *	can easily type them in without having to hunt for them.  This requires longer
	 *	SKU lengths.  Default of 20 doesn't work for us anymore.
	 *
	 *	This function alters all of the tables (that we know of) to have the longer
	 *	length.  The Length is defined in an include file where we are putting
	 *	lengths so we can do this to other tables as needed too.
	 *
	 * **********************************************************************************************/
	function fix_stock_id_size()
	{
		require_once( '../ksf_modules_common/defines.inc.php' );
		/*
		$tables = array();
		$tables[] = array( 'table' => TB_PREF . 'item_codes', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
		$tables[] = array( 'table' => TB_PREF . 'purch_data', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH  );
		$tables[] = array( 'table' => TB_PREF . 'stock_master', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
		$tables[] = array( 'table' => TB_PREF . 'loc_stock', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
		$tables[] = array( 'table' => TB_PREF . 'item_codes', 'column' => 'item_code', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
		$tables[] = array( 'table' => TB_PREF . 'purch_order_details', 'column' => 'item_code', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
		$tables[] = array( 'table' => TB_PREF . 'stock_moves', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
		$tables[] = array( 'table' => TB_PREF . 'sales_order_details', 'column' => 'stk_code', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
		$tables[] = array( 'table' => TB_PREF . 'wo_issue_items', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
		$tables[] = array( 'table' => TB_PREF . 'wo_requirements', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
		$tables[] = array( 'table' => TB_PREF . 'workorders', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
		$tables[] = array( 'table' => TB_PREF . 'debtor_trans_details', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
		$tables[] = array( 'table' => TB_PREF . 'prices', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
		$tables[] = array( 'table' => TB_PREF . 'supp_invoice_items', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
		//$tables[] = array( 'table' => TB_PREF . '', 'column' => 'stock_id' );
	 */
		global $stock_id_tables;
		$this->modify_table_column( $stock_id_tables );
	}
}

?>
