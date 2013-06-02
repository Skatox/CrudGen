<?php

    /**
        * English language file for plugin CrudGen.  Use this as a basis
        * for new translations.
        */

    // Language and character set
    $plugin_lang['appcharset'] = 'ISO-8859-1';

    //Plugin data
    $plugin_lang['strdescription'] = 'CRUD Generator';

	//Links strings
    $plugin_lang['strid'] = 'ID';
    $plugin_lang['strgenerate'] = 'Generate';
    $plugin_lang['strdescr'] = 'Description';
    
    //Basic strings
    $plugin_lang['strcreate'] = 'Create';
    $plugin_lang['strupdate'] = 'Update';
    $plugin_lang['strreport'] = 'Report';
    $plugin_lang['strdelete'] = 'Delete';
    $plugin_lang['stractions'] = 'Actions';
    $plugin_lang['stroperation'] = 'Operation';
    $plugin_lang['strno'] = 'No';
    $plugin_lang['stryes'] = 'Yes';
    $plugin_lang['strorder'] = 'Order';
    
    //Application
    $plugin_lang['strcreateapp'] = 'Create new application';
    $plugin_lang['streditapp'] = 'Edit application';
    $plugin_lang['strnoapps'] = 'There are no applications';
    $plugin_lang['strappwizard'] = 'Application wizard';
    $plugin_lang['strsecaccess'] = 'Application\'s security access';
    $plugin_lang['strnosecurity'] = 'No security';
    $plugin_lang['strnosecuritytxt'] = '(Does not ask for authentication)';
    $plugin_lang['strsecdbuser'] = 'Login using database\'s users';
    $plugin_lang['strsecdbusertxt'] = '(Uses a created user to work with the DB, the file pg_hba.conf must be well configurated)';
    $plugin_lang['strsecdbstored'] = 'User and password are stored in a table from database';
    $plugin_lang['strsecdbstoredtxt']= '(Select columns from the database where user and password are stored)';
    $plugin_lang['strselsecurity'] = 'Select security access -->';
    $plugin_lang['strappsaved'] = 'Application created.';
    $plugin_lang['strappedited'] = 'Application edited.';
    $plugin_lang['strappnotsaved'] = 'Application creation failed.';
    $plugin_lang['strconfdelapp'] = 'Do you really want to delete this application?';
    $plugin_lang['strconfdelapps'] = 'Do you really want to delete selected applications?';
    $plugin_lang['strdelapp'] = 'Application deleted.';
    $plugin_lang['strdelapps'] = 'Applications deleted.';
    $plugin_lang['strerrdelapp'] = 'Application deletion failed.';
    
    //Wizard
    $plugin_lang['strstep'] = 'Step';
    $plugin_lang['strall'] = 'All';
    $plugin_lang['strnone'] = 'None';
    $plugin_lang['strtbldetect'] = 'Select columns to be used by the application';
    $plugin_lang['stratbldetectwarn'] = 'Not null fields are selected by default, you can ommit those with default values later';
    $plugin_lang['strseloperation'] = 'Select the desired fields to be displayed on each application\'s page';
    $plugin_lang['strpagesdetected'] = 'The following pages are going to be created';
    $plugin_lang['strreportpages'] = 'Search and report pages';
    $plugin_lang['strcreatepages'] = 'Insert data pages';
    $plugin_lang['strupdatepages'] = 'Update data pages';
    $plugin_lang['strdeletepages'] = 'Delete data pages';
    $plugin_lang['strclickaddpages'] = 'Click here to add more pages';
    $plugin_lang['strthefile'] = 'The file ';
    $plugin_lang['strfilecreation'] = 'is going to be created to work with the following columns:';
    $plugin_lang['strsavepagessuccessful']= 'Pages added successfully.';
    $plugin_lang['strappdatatxt'] = 'Fill required data to start generating the application:';

    //Pages
    $plugin_lang['strmanagepage'] = 'Manage pages';
    $plugin_lang['strpages'] = 'Pages';
    $plugin_lang['straddpages'] = 'Add pages';
    $plugin_lang['streditpages'] = 'Edit page';
    $plugin_lang['strfilename'] = 'Filename';
    $plugin_lang['strcompleted'] = 'Completed';
    $plugin_lang['strdelpage'] = 'Do you really want to delete this page?';
    $plugin_lang['strdelpages'] = 'Do you really want to delete selected pages?';
    $plugin_lang['strdeletedpage'] = 'Page deleted.';
    $plugin_lang['strdeletedpages'] = 'Pages deleted.';
    $plugin_lang['strerrdelpage'] = 'Page deletion failed.';
    $plugin_lang['strpageinfo'] = 'Page information';
    $plugin_lang['strpagetitle'] = 'Title';
    $plugin_lang['strpagemainmenu'] = 'Main menu';
    $plugin_lang['strpageonmainmenu'] = 'Show this page on the main menu?';
    $plugin_lang['strpagecontent']= 'Content';
    $plugin_lang['strfieldname'] = 'Field';
    $plugin_lang['strdisplay'] = 'Display';
    $plugin_lang['strdisplayname'] = 'Name to display';
    $plugin_lang['strshowinpage'] = 'Visible';
    $plugin_lang['strremotecol'] = 'Remote column';
    $plugin_lang['strfkvalue'] = 'Foreign Key\'s value';
    $plugin_lang['strmaninp'] = 'Manual input';
    $plugin_lang['strpriority'] = 'Display priority';
    $plugin_lang['strsavepagesuccessful'] = 'Page updated successfully';
    
    //Generation
    $plugin_lang['strtheme'] = 'Theme';
    $plugin_lang['strgenerating'] = 'Generating';
    $plugin_lang['strpreview'] = 'Preview';
    $plugin_lang['strphplibrary'] = 'PHP Library';
    $plugin_lang['strpgsql'] = 'pgsql';
    $plugin_lang['strpdo'] = 'pdo_pgsql';   
    $plugin_lang['strinsertsuccess'] = 'The information was added successfully';
    $plugin_lang['strinsertfail'] = 'There was a problem when adding the information';
    $plugin_lang['strupdatesuccess'] = 'Information edited successfully';
    $plugin_lang['strrecordnoexist'] = 'Selected record doesn\'t exists';
    $plugin_lang['strupdatefail'] = 'There was a problem when editing the information';
    $plugin_lang['strasc'] = 'Ascending';
    $plugin_lang['strdesc'] = 'Descending';
    $plugin_lang['strsortby'] = 'Sort by';
    $plugin_lang['strsrows'] = 'rows';
    $plugin_lang['strdelsucess'] = 'Data successfully deleted';
    $plugin_lang['strsearch'] = 'Search';
    $plugin_lang['stremptyrows'] = 'No data matching your search criteria.';
    $plugin_lang['strgotopage'] = 'Go to page: ';
    $plugin_lang['strselectval'] = '--Select--';
    $plugin_lang['strnoselecteditems'] = 'Please select some items to continue.';
    $plugin_lang['strconfirmdelete'] = 'Are you sure you want to delete selected data?';
    $plugin_lang['strwriteprimarykey'] = 'Write the primary key of the record you want to edit.';
    
    //Errors
    $plugin_lang['strerrnotbl'] = 'Selected schema is empty, you must have some tables to create an application.';
    $plugin_lang['strnocrudgendb'] = 'CrudGen\'s schema is not installed, please read the INSTALL file (located at plugin\'s folder) for instructions.';
    $plugin_lang['strnoappname'] = 'You must give a name for your application.';
    $plugin_lang['strnohost'] = 'You must specify database\'s host.';
    $plugin_lang['strnoport'] = 'You must specify database\'s port.';
    $plugin_lang['strnousername'] = 'You must specify database\'s username.';
    $plugin_lang['strnotablecol'] = 'You must specify database\'s table where login data is stored.';
    $plugin_lang['strnousercol'] = 'You must specify database\'s column where user data is stored.';
    $plugin_lang['strnopasscol'] = 'You must specify database\'s table where password data is stored.';
    $plugin_lang['strnouniquename'] = 'Another application has the same name, you must use a new one.';
    $plugin_lang['strerrorappsavedb'] = 'There was a problem when saving the application into the database.';
    $plugin_lang['strnopages'] = 'There are no pages for this application.';
    $plugin_lang['strnopagesgenerate'] = 'This application has no pages ready to be generated. Please add their information first.';
    $plugin_lang['strnopagetitle'] = 'You must give a page title.';
    $plugin_lang['strnopagefilename'] = 'You must write the name of the file.';
    $plugin_lang['strerrnoextension'] = 'You must write the php extension in to the filename.';
    $plugin_lang['strnodisplayname'] = 'Missing display name.';
    $plugin_lang['strpageerrsavedb'] = 'There was a problem when saving the page into the database.';
    $plugin_lang['strnouniquefilename'] = 'There is another page in the application with the same filename.';
    $plugin_lang['strerrfielddb'] = 'There was a problem when saving fields into the database';
    $plugin_lang['strselpagetodelete'] = 'Select pages to be deleted';
    $plugin_lang['strselapptodelete'] = 'Select applications to be deleted';
    $plugin_lang['strerrnoappid'] = 'Missing application\'s ID';
    $plugin_lang['strnocommonfile'] = 'There was a problem when creating the common file.';
    $plugin_lang['strloginerror'] = 'Login failed! Check if username and password are correct.';
    $plugin_lang['strerrpagegen'] = 'There was an error when generating ';
    $plugin_lang['strgenerror'] = 'There was an error when generating the application.';
    $plugin_lang['strerrordbconn'] = 'There is no connection to the database';
    $plugin_lang['strerrorquery'] = 'There was a problem when executing the query';
    $plugin_lang['strrowdeletedbad'] =  'There was a problem when deleting the data';
    $plugin_lang['strnorowstodelete'] =  'There are no selected items to delete.';
    $plugin_lang['strnoselecteditem'] =  'There are no selected items to edit.';
    $plugin_lang['strnomoreitems'] =  'There are no more items to edit.';
    $plugin_lang['strpageerredit'] = 'There was a problem when editing the information.';    
?>
