<?php

	/**
	 * English language file for plugin CrudGen.  Use this as a basis
	 * for new translations.
	 */

	// Language and character set
	$lang['appcharset'] = 'ISO-8859-1';

	//Plugin data
	$lang['strdescription'] = 'CRUD Generator';

	//Links strings
	$lang['strid'] = 'ID';
	$lang['strgenerate'] = 'Generate';
    $lang['strdescr'] = 'Description';
    
    //Application
    $lang['strcreateapp'] = 'Create new application';
    $lang['streditapp'] = 'Edit application';
    $lang['strnoapps'] = 'There are no applications';
    $lang['strappwizard'] = 'Application wizard';
    $lang['strsecaccess'] = 'Application\'s security access';
    $lang['strnosecurity'] = 'No security';
    $lang['strnosecuritytxt'] = '(Does not ask for authentication)';
    $lang['strsecdbuser'] = 'Login using database\'s users';
    $lang['strsecdbusertxt'] = '(Uses a created user to work with the DB, the file pg_hba.conf must be well configurated)';
    $lang['strsecdbstored'] = 'User and password are stored in a table from database';
    $lang['strsecdbstoredtxt']= '(Select columns from the database where user and password are stored)';
    $lang['strselsecurity'] = 'Select security access -->';
    $lang['strappsaved'] = 'Application created.';
    $lang['strappedited'] = 'Application edited.';
    $lang['strappnotsaved'] = 'Application creation failed.';
    $lang['strconfdelapp'] = 'Do you really want to delete this application?';
    $lang['strdelapp']= 'Application deleted.';
    $lang['strerrdelapp']= 'Application deletion failed.';
    
    //Pages
    $lang['strmanagepage'] = 'Manage pages';
    $lang['strpagesnotcreated'] = 'Non created pages';
    $lang['strpagescreated'] = 'Created pages';
    $lang['strpages'] = 'Pages';
    $lang['strpagecreated'] = 'Pages created.';
    $lang['strpagenotcreated'] = 'Pages not created.';
    $lang['straddpage'] = 'Add page';
    $lang['streditpages'] = 'Edit page';
    
    //Errors
    $lang['strerrnotbl'] = 'Selected schema is empty, you must have some tables to create an application.';
    $lang['strnocrudgendb'] = 'CrudGen\'s schema is not installed, please read the INSTALL file (located at plugin\'s folder) for instructions.';
    $lang['strnoappname'] = 'You must give a name for your application.';
    $lang['strnohost'] = 'You must specify database\'s host.';
    $lang['strnoport'] = 'You must specify database\'s port.';
    $lang['strnousername'] = 'You must specify database\'s username.';
    $lang['strnotablecol'] = 'You must specify database\'s table where login data is stored.';
    $lang['strnousercol'] = 'You must specify database\'s column where user data is stored.';
    $lang['strnopasscol'] = 'You must specify database\'s table where password data is stored.';
?>
