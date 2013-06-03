<?php

    /**
        * Spanish language file for plugin CrudGen.  Use this as a basis
        * for new translations.
        */

    // Language and character set
    $plugin_lang['applang'] = 'Spanish';
    $plugin_lang['appcharset'] = 'ISO-8859-1';
    $plugin_lang['applocale'] = 'es_ES';
    $plugin_lang['appdbencoding'] = 'LATIN1';
    $plugin_lang['applangdir'] = 'ltr';

    //Plugin data
    $plugin_lang['strdescription'] = 'Generador CRUD';

	//Links strings
    $plugin_lang['strid'] = 'ID';
    $plugin_lang['strgenerate'] = 'Generar';
    $plugin_lang['strdescr'] = 'Descripci&#243;n';
    
    //Basic strings
    $plugin_lang['strcreate'] = 'Crear';
    $plugin_lang['strupdate'] = 'Editar';
    $plugin_lang['strreport'] = 'Reportar';
    $plugin_lang['strdelete'] = 'Eliminar';
    $plugin_lang['stractions'] = 'Acciones';
    $plugin_lang['stroperation'] = 'Operaci&#243;n';
    $plugin_lang['strno'] = 'No';
    $plugin_lang['stryes'] = 'S&#237';
    $plugin_lang['strorder'] = 'Orden';
    
    //aplicaci&#243;n
    $plugin_lang['strcreateapp'] = 'Crear una nueva aplicaci&#243;n ';
    $plugin_lang['streditapp'] = 'Editar aplicaci&#243;n';
    $plugin_lang['strnoapps'] = 'No hay aplicaciones';
    $plugin_lang['strappwizard'] = 'Asistente de aplicaciones';
    $plugin_lang['strsecaccess'] = 'Acceso a la aplicaci&#243;n';
    $plugin_lang['strnosecurity'] = 'Sin seguridad';
    $plugin_lang['strnosecuritytxt'] = '(No pregunta para autenticar)';
    $plugin_lang['strsecdbuser'] = 'Usando usuarios de la base de datos';
    $plugin_lang['strsecdbusertxt'] = '(Usa las credenciales de la base datos, el archivo pg_hba.conf debe estar bien configurado)';
    $plugin_lang['strsecdbstored'] = 'Los usuarios y contrase&ntilde;as est&#225;n almacenados en una tabla de la base de datos';
    $plugin_lang['strsecdbstoredtxt']= '(Seleccione las columnas de la base de datos donde se guardan los datos de los usuarios y contrase&ntilde;as)';
    $plugin_lang['strselsecurity'] = 'Seleccione el tipo de acceso --&gt;';
    $plugin_lang['strappsaved'] = 'Aplicaci&#243;n creada.';
    $plugin_lang['strappedited'] = 'Aplicaci&#243;n editada.';
    $plugin_lang['strappnotsaved'] = 'Fallo la creaci&#243;n de la aplicaci&#243;n.';
    $plugin_lang['strconfdelapp'] = '&iquest;Realmente deseas eliminar esta aplicaci&#243;n?';
    $plugin_lang['strconfdelapps'] = '&iquest;Realmente deseas eliminar las aplicaciones seleccionadas?';
    $plugin_lang['strdelapp'] = 'Aplicaci&#243;n eliminada.';
    $plugin_lang['strdelapps'] = 'Aplicaciones eliminadas.';
    $plugin_lang['strerrdelapp'] = 'Fall&#243; la eliminaci&#243;n de la aplicaci&#243;n.';
    
    //Wizard
    $plugin_lang['strstep'] = 'Paso';
    $plugin_lang['strall'] = 'Todos';
    $plugin_lang['strnone'] = 'Ninguno';
    $plugin_lang['strtbldetect'] = 'Seleccione las columnas a ser usadas por la aplicaci&#243;n';
    $plugin_lang['stratbldetectwarn'] = 'Los campos No Nulos son seleccionados por defecto, mas tarde puedes omitir aquellos con valores predeterminados';
    $plugin_lang['strseloperation'] = 'Seleccione los campos que deseas mostrar en cada p&#225;gina de la aplicaci&#243;n';
    $plugin_lang['strpagesdetected'] = 'Las siguientes p&#225;ginas van a ser creadas';
    $plugin_lang['strreportpages'] = 'P&#225;ginas de reportes y b&#250;squeda';
    $plugin_lang['strcreatepages'] = 'P&#225;ginas para agregar datos';
    $plugin_lang['strupdatepages'] = 'P&#225;ginas para editar datos';
    $plugin_lang['strdeletepages'] = 'P&#225;ginas para eliminar datos';
    $plugin_lang['strclickaddpages'] = 'Haz clic aqu&#243; para agregar mas p&#225;ginas';
    $plugin_lang['strthefile'] = 'El archivo ';
    $plugin_lang['strfilecreation'] = 'va a ser creado para trabajar con las siguientes columnas:';
    $plugin_lang['strsavepagessuccessful']= 'P&#225;gina agregada exitosamente.';
    $plugin_lang['strappdatatxt'] = 'Rellena los datos requerida para empezar a generar la aplicaci&#243;n:';

    //Pages
    $plugin_lang['strmanagepage'] = 'Gestionar p&#225;ginas';
    $plugin_lang['strpages'] = 'P&#225;ginas';
    $plugin_lang['straddpages'] = 'Agregar p&aacute;gina';
    $plugin_lang['streditpages'] = 'Editar p&#225;gina';
    $plugin_lang['strfilename'] = 'Nombre del archivo';
    $plugin_lang['strcompleted'] = 'Completado';
    $plugin_lang['strdelpage'] = '&iquest;Realmente deseas eliminar &#233;sta p&#225;gina?';
    $plugin_lang['strdelpages'] = '&iquest;Realmente deseas eliminar las p&#225;ginas seleccionadas?';
    $plugin_lang['strdeletedpage'] = 'P&#225;gina eliminada.';
    $plugin_lang['strdeletedpages'] = 'P&#225;ginas eliminadas.';
    $plugin_lang['strerrdelpage'] = 'Fall&#243; la eliminaci&#243;n de la p&#225;gina.';
    $plugin_lang['strpageinfo'] = 'Informaci&#243;n de la p&#225;gina';
    $plugin_lang['strpagetitle'] = 'Titulo';
    $plugin_lang['strpagemainmenu'] = 'Men&#250; principal';
    $plugin_lang['strpageonmainmenu'] = '&iquest;Mostrar &#233;sta p&#225;gina en el men&#250;?';
    $plugin_lang['strpagecontent']= 'Contenido';
    $plugin_lang['strfieldname'] = 'Columna';
    $plugin_lang['strdisplay'] = 'Mostrar';
    $plugin_lang['strdisplayname'] = 'Nombre a mostrar';
    $plugin_lang['strshowinpage'] = 'Visible';
    $plugin_lang['strremotecol'] = 'Columna remota';
    $plugin_lang['strfkvalue'] = 'Valor de la clave for&#225;nea';
    $plugin_lang['strmaninp'] = 'Entrada manual';
    $plugin_lang['strpriority'] = 'Orden para mostrar';
    $plugin_lang['strsavepagesuccessful'] = 'P&#225;gina editada correctamente';
    
    //Generation
    $plugin_lang['strtheme'] = 'Tema';
    $plugin_lang['strgenerating'] = 'Generando';
    $plugin_lang['strpreview'] = 'Vista previa';
    $plugin_lang['strphplibrary'] = 'Biblioteca de PHP';
    $plugin_lang['strpgsql'] = 'pgsql';
    $plugin_lang['strpdo'] = 'pdo_pgsql';   
    $plugin_lang['strinsertsuccess'] = 'La informaci&#243;n fue a&ntilde;adida correctamente';
    $plugin_lang['strinsertfail'] = 'Ocurri&#243; un problema al a&ntilde;adir la informaci&#243;n';
    $plugin_lang['strupdatesuccess'] = 'Informaci&#243;n editada correctamente';
    $plugin_lang['strrecordnoexist'] = 'El registro seleccionado no existe';
    $plugin_lang['strupdatefail'] = 'Ocurri&#243; un problema al editar la informaci&#243;n';
    $plugin_lang['strasc'] = 'Ascendente';
    $plugin_lang['strdesc'] = 'Descendente';
    $plugin_lang['strsortby'] = 'Order por';
    $plugin_lang['strsrows'] = 'filas';
    $plugin_lang['strdelsucess'] = 'Datos eliminados correctamente';
    $plugin_lang['strsearch'] = 'Buscar';
    $plugin_lang['stremptyrows'] = 'No existen datos que satisfacen el criterio de b&#250;squeda.';
    $plugin_lang['strgotopage'] = 'Ir a la p&#225;gina: ';
    $plugin_lang['strselectval'] = '--Seleccione--';
    $plugin_lang['strnoSeleccioneeditems'] = 'Por favor seleccione algunos &#237tems para continuar.';
    $plugin_lang['strconfirmdelete'] = '&iquest;Est&#225; seguro que desea eliminar los datos seleccionados?';
    $plugin_lang['strwriteprimarykey'] = 'Escribe la clave primaria del registro a editar.';
    
    //Errors
    $plugin_lang['strerrnotbl'] = 'El esquema seleccionado est&#225; vac&#237o, debes tener algunas tablas para crear la aplicaci&#243;n.';
    $plugin_lang['strnocrudgendb'] = 'El esquema de CrudGen no est&#225; instalado, por favor lee el archivo INSTALL (localizado en la carpeta del plugin) para conocer las instrucciones.';
    $plugin_lang['strnoappname'] = 'Debes darle un nombre para tu aplicaci&#243;n.';
    $plugin_lang['strnohost'] = 'Debes especificar el host de la base de datos.';
    $plugin_lang['strnoport'] = 'Debes especificar el puerto de la base de datos.';
    $plugin_lang['strnousername'] = 'Debes especificar el usuario de la base de datos.';
    $plugin_lang['strnotablecol'] = 'Debes especificar la tabla de la base de datos donde la informaci&#243;n de acceso est&#225; almacenada.';
    $plugin_lang['strnousercol'] = 'Debes especificar la columna de donde la informaci&#243;n del usuario est&#225; almacenada.';
    $plugin_lang['strnopasscol'] = 'Debes especificar la columna de donde la informaci&#243;n de la contrase&ntilde;a est&#225; almacenada.';
    $plugin_lang['strnouniquename'] = 'Ya existe una aplicaci&#243;n con ese nombre, debes usar otro.';
    $plugin_lang['strerrorappsavedb'] = 'Ocurri&#243; un problema cuando se guardaba la aplicaci&#243;n en la base de datos.';
    $plugin_lang['strnopages'] = 'No hay p&#225;ginas para &#233;sta aplicaci&#243;n.';
    $plugin_lang['strnopagesgenerate'] = 'La aplicaci&#243;n no tiene p&#225;ginas listas para ser generadas. Por favor agrega previamente su informaci&#243;n.';
    $plugin_lang['strnopagetitle'] = 'Debes escribir el t&#237tulo de la p&#225;gina.';
    $plugin_lang['strnopagefilename'] = 'Debes escribir el nombre del archivo.';
    $plugin_lang['strerrnoextension'] = 'Debes escribir la extensi&#243;n php en el nombre del archivo.';
    $plugin_lang['strnodisplayname'] = 'Falta el nombre a mostrar.';
    $plugin_lang['strnodisplaycolumns'] = 'Debe tener al menos una columna visible';
    $plugin_lang['strpageerrsavedb'] = 'Ocurri&#243; un problema cuando se guardaba la p&#225;gina en la base de datos.';
    $plugin_lang['strnouniquefilename'] = 'Existe otra p&#225;gina en la aplicaci&#243;n con el mismo nombre del archivo.';
    $plugin_lang['strerrfielddb'] = 'Ocurri&#243; un problema cuando se guardaba las columnas en la base de datos';
    $plugin_lang['strselpagetodelete'] = 'Seleccione las p&#225;ginas a eliminar';
    $plugin_lang['strselapptodelete'] = 'Seleccione las aplicaciones a eliminar';
    $plugin_lang['strerrnoappid'] = 'Falta el identificador de la aplicaci&#243;n';
    $plugin_lang['strnocommonfile'] = 'Ocurri&#243; un problema cuando se creaba el archivo com&#250;n.';
    $plugin_lang['strloginerror'] = '&iexcl;Inicio de sesi&#243;n incorrecto! Chequea si el usuario y contrase&ntilde;a son los correctos.';
    $plugin_lang['strerrpagegen'] = 'Ocurri&#243; un error cuando se generaba ';
    $plugin_lang['strgenerror'] = 'Ocurri&#243; un error cuando se generaba la aplicaci&#243;n.';
    $plugin_lang['strerrordbconn'] = 'No hay conexi&#243;n a la base de datos';
    $plugin_lang['strerrorquery'] = 'Ocurri&#243; un problema cuando se ejecutaba la consulta';
    $plugin_lang['strrowdeletedbad'] =  'Ocurri&#243; un problema cuando se eliminaban los datos';
    $plugin_lang['strnorowstodelete'] =  'No se han seleccionado registros para eliminar.';
    $plugin_lang['strnoselecteditem'] =  'No se han seleccionado registros para editar.';
    $plugin_lang['strnomoreitems'] =  'No hay more registros a editar.';
    $plugin_lang['strpageerredit'] = 'Ocurri&#243; un problema cuando se editaba la informaci&#243;n.';    
?>
