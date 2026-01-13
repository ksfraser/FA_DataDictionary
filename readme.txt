/****************************************************************************
Name: ksf_data_dictionary
Free software under GNU GPL
*****************************************************************************/

WHAT DOES THIS MODULE DO?

*It is designed for altering the default FrontAccounting tables so that the columns
* will be the size required by our modules.

DEPENDENCIES
This module depends on the ksf_modules_common to be installed with defines.inc because 
that file holds the sizes for our modules as well as the list of tables holding
what columns that we need to alter

INSTALLATION:

1. FrontAccounting -> Setup -> Install/Activate Extensions

   Click on the icon in the right column corresponding to ksf_data_dictionary

   Extensions drop down box -> Activated for (name of your business)

   Click on "active" box for ksf_data_dictionary -> Update

2. FrontAccounting -> Setup -> Access Setup

   Select appropriate role click on ksf_data_dictionary header and entry -> Save Role

   Logout and log back in

3. FrontAccounting -> TAB -> ksf_data_dictionary

   Click on button -> Create Table
 
   Fill in details for connecting to the ksf_data_dictionary databases -> Update Mysql

----------------------------------------------------------

