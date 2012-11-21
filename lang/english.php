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
    
    //Basic strings
    $lang['strcreate'] = 'Create';
    $lang['strupdate'] = 'Update';
    $lang['strreport'] = 'Report';
    $lang['strdelete'] = 'Delete';
    $lang['stractions'] = 'Actions';
    $lang['stroperation'] = 'Operation';
    $lang['strno'] = 'No';
    $lang['stryes'] = 'Yes';
    $lang['strorder'] = 'Order';
    
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
    $lang['strconfdelapps'] = 'Do you really want to delete selected applications?';
    $lang['strdelapp'] = 'Application deleted.';
    $lang['strdelapps'] = 'Applications deleted.';
    $lang['strerrdelapp'] = 'Application deletion failed.';
    
    //Wizard
    $lang['strstep'] = 'Step';
    $lang['strall'] = 'All';
    $lang['strnone'] = 'None';
    $lang['strtbldetect'] = 'Select columns to be used by the application';
    $lang['stratbldetectwarn'] = 'Not null fields are selected by default, you can ommit those with default values later';
    $lang['strseloperation'] = 'Select the desired fields to be displayed on each application\'s page';
    $lang['strpagesdetected'] = 'The following pages are going to be created';
    $lang['strreportpages'] = 'Search and report pages';
    $lang['strcreatepages'] = 'Insert data pages';
    $lang['strupdatepages'] = 'Update data pages';
    $lang['strdeletepages'] = 'Delete data pages';
    $lang['strclickaddpages'] = 'Click here to add more pages';
    $lang['strthefile'] = 'The file ';
    $lang['strfilecreation'] = 'is going to be created to work with the following columns:';
    $lang['strsavepagessuccessful']= 'Pages added successfully.';
    $lang['strappdatatxt'] = 'Fill required data to start generating the application:';

    //Pages
    $lang['strmanagepage'] = 'Manage pages';
    $lang['strpages'] = 'Pages';
    $lang['straddpages'] = 'Add pages';
    $lang['streditpages'] = 'Edit page';
    $lang['strfilename'] = 'Filename';
    $lang['strcompleted'] = 'Completed';
    $lang['strdelpage'] = 'Do you really want to delete this page?';
    $lang['strdelpages'] = 'Do you really want to delete selected pages?';
    $lang['strdeletedpage'] = 'Page deleted.';
    $lang['strdeletedpages'] = 'Pages deleted.';
    $lang['strerrdelpage'] = 'Page deletion failed.';
    $lang['strpageinfo'] = 'Page information';
    $lang['strpagetitle'] = 'Title';
    $lang['strpagemainmenu'] = 'Main menu';
    $lang['strpageonmainmenu'] = 'Show this page on the main menu?';
    $lang['strpagecontent']= 'Content';
    $lang['strfieldname'] = 'Field';
    $lang['strdisplay'] = 'Display';
    $lang['strdisplayname'] = 'Name to display';
    $lang['strshowinpage'] = 'Visible';
    $lang['strremotecol'] = 'Remote column';
    $lang['strfkvalue'] = 'Foreign Key\'s value';
    $lang['strmaninp'] = 'Manual input';
    $lang['strpriority'] = 'Display priority';
    $lang['strsavepagesuccessful'] = 'Page updated successfully';
    
    //Generation
    $lang['strtheme'] = 'Theme';
    $lang['strgenerating'] = 'Generating';
    $lang['strpreview'] = 'Preview';
    $lang['strphplibrary'] = 'PHP Library';
    $lang['strpgsql'] = 'pgsql';
    $lang['strpdo'] = 'pdo_pgsql';   
    $lang['strinsertsuccess'] = 'The information was added successfully';
    $lang['strinsertfail'] = 'There was a problem when adding the information';
    $lang['strupdatesuccess'] = 'Information edited successfully';
    $lang['strrecordnoexist'] = 'Selected record doesn\'t exists';
    $lang['strupdatefail'] = 'There was a problem when editing the information';
    $lang['strasc'] = 'Ascending';
    $lang['strdesc'] = 'Descending';
    $lang['strsortby'] = 'Sort by';
    $lang['strsrows'] = 'rows';
    $lang['strdelsucess'] = 'Data successfully deleted';
    $lang['strsearch'] = 'Search';
    $lang['stremptyrows'] = 'No data matching your search criteria.';
    $lang['strgotopage'] = 'Go to page: ';
    $lang['strselectval'] = '--Select--';
    $lang['strnoselecteditems'] = 'Please select some items to continue.';
    $lang['strconfirmdelete'] = 'Are you sure you want to delete selected data?';
    $lang['strwriteprimarykey'] = 'Write the primary key of the record you want to edit.';
    
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
    $lang['strnouniquename'] = 'Another application has the same name, you must use a new one.';
    $lang['strerrorappsavedb'] = 'There was a problem when saving the application into the database.';
    $lang['strnopages'] = 'There are no pages for this application.';
    $lang['strnopagesgenerate'] = 'This application has no pages ready to be generated. Please add their information first.';
    $lang['strnopagetitle'] = 'You must give a page title.';
    $lang['strnopagefilename'] = 'You must write the name of the file.';
    $lang['strerrnoextension'] = 'You must write the php extension in to the filename.';
    $lang['strnodisplayname'] = 'Missing display name.';
    $lang['strpageerrsavedb'] = 'There was a problem when saving the page into the database.';
    $lang['strnouniquefilename'] = 'There is another page in the application with the same filename.';
    $lang['strerrfielddb'] = 'There was a problem when saving fields into the database';
    $lang['strselpagetodelete'] = 'Select pages to be deleted';
    $lang['strselapptodelete'] = 'Select applications to be deleted';
    $lang['strerrnoappid'] = 'Missing application\'s ID';
    $lang['strnocommonfile'] = 'There was a problem when creating the common file.';
    $lang['strloginerror'] = 'Login failed! Check if username and password are correct.';
    $lang['strerrpagegen'] = 'There was an error when generating ';
    $lang['strgenerror'] = 'There was an error when generating the application.';
    $lang['strerrordbconn'] = 'There is no connection to the database';
    $lang['strerrorquery'] = 'There was a problem when executing the query';
    $lang['strrowdeletedbad'] =  'There was a problem when deleting the data';
    $lang['strnorowstodelete'] =  'There are no selected items to delete.';
    $lang['strnoselecteditem'] =  'There are no selected items to edit.';
    $lang['strnomoreitems'] =  'There are no more items to edit.';
    $lang['strpageerredit'] = 'There was a problem when editing the information.';    
?>
