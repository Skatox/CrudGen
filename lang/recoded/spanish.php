<?php

    /**
        * Spanish language file for plugin CrudGen.  Use this as a basis
        * for new translations.
        */

    // Language and character set
    $lang['applang'] = 'Spanish';
    $lang['appcharset'] = 'ISO-8859-1';
    $lang['applocale'] = 'es_ES';
    $lang['appdbencoding'] = 'LATIN1';
    $lang['applangdir'] = 'ltr';

    //Plugin data
    $lang['strdescription'] = 'Generador CRUD';

	//Links strings
    $lang['strid'] = 'ID';
    $lang['strgenerate'] = 'Generar';
    $lang['strdescr'] = 'Descripci&#243;n';
    
    //Basic strings
    $lang['strcreate'] = 'Crear';
    $lang['strupdate'] = 'Editar';
    $lang['strreport'] = 'Reportar';
    $lang['strdelete'] = 'Eliminar';
    $lang['stractions'] = 'Acciones';
    $lang['stroperation'] = 'Operaci&#243;n';
    $lang['strno'] = 'No';
    $lang['stryes'] = 'S&#237';
    $lang['strorder'] = 'Orden';
    
    //aplicaci&#243;n
    $lang['strcreateapp'] = 'Crear una nueva aplicaci&#243;n ';
    $lang['streditapp'] = 'Editar aplicaci&#243;n';
    $lang['strnoapps'] = 'No hay aplicaciones';
    $lang['strappwizard'] = 'Asistente de aplicaciones';
    $lang['strsecaccess'] = 'Acceso a la aplicaci&#243;n';
    $lang['strnosecurity'] = 'Sin seguridad';
    $lang['strnosecuritytxt'] = '(No pregunta para autenticar)';
    $lang['strsecdbuser'] = 'Usando usuarios de la base de datos';
    $lang['strsecdbusertxt'] = '(Usa las credenciales de la base datos, el archivo pg_hba.conf debe estar bien configurado)';
    $lang['strsecdbstored'] = 'Los usuarios y contrase&ntilde;as est&#225;n almacenados en una tabla de la base de datos';
    $lang['strsecdbstoredtxt']= '(Seleccione las columnas de la base de datos donde se guardan los datos de los usuarios y contrase&ntilde;as)';
    $lang['strselsecurity'] = 'Seleccione el tipo de acceso --&gt;';
    $lang['strappsaved'] = 'Aplicaci&#243;n creada.';
    $lang['strappedited'] = 'Aplicaci&#243;n editada.';
    $lang['strappnotsaved'] = 'Fallo la creaci&#243;n de la aplicaci&#243;n.';
    $lang['strconfdelapp'] = '&iquest;Realmente deseas eliminar esta aplicaci&#243;n?';
    $lang['strconfdelapps'] = '&iquest;Realmente deseas eliminar las aplicaciones seleccionadas?';
    $lang['strdelapp'] = 'Aplicaci&#243;n eliminada.';
    $lang['strdelapps'] = 'Aplicaciones eliminadas.';
    $lang['strerrdelapp'] = 'Fall&#243; la eliminaci&#243;n de la aplicaci&#243;n.';
    
    //Wizard
    $lang['strstep'] = 'Paso';
    $lang['strall'] = 'Todos';
    $lang['strnone'] = 'Ninguno';
    $lang['strtbldetect'] = 'Seleccione las columnas a ser usadas por la aplicaci&#243;n';
    $lang['stratbldetectwarn'] = 'Los campos No Nulos son seleccionados por defecto, mas tarde puedes omitir aquellos con valores predeterminados';
    $lang['strseloperation'] = 'Seleccione los campos que deseas mostrar en cada p&#225;gina de la aplicaci&#243;n';
    $lang['strpagesdetected'] = 'Las siguientes p&#225;ginas van a ser creadas';
    $lang['strreportpages'] = 'P&#225;ginas de reportes y b&#250;squeda';
    $lang['strcreatepages'] = 'P&#225;ginas para agregar datos';
    $lang['strupdatepages'] = 'P&#225;ginas para editar datos';
    $lang['strdeletepages'] = 'P&#225;ginas para eliminar datos';
    $lang['strclickaddpages'] = 'Haz clic aqu&#243; para agregar mas p&#225;ginas';
    $lang['strthefile'] = 'El archivo ';
    $lang['strfilecreation'] = 'va a ser creado para trabajar con las siguientes columnas:';
    $lang['strsavepagessuccessful']= 'P&#225;gina agregada exitosamente.';
    $lang['strappdatatxt'] = 'Rellena los datos requerida para empezar a generar la aplicaci&#243;n:';

    //Pages
    $lang['strmanagepage'] = 'Gestionar p&#225;ginas';
    $lang['strpages'] = 'P&#225;ginas';
    $lang['straddpages'] = 'Agregar p&aacute;gina';
    $lang['streditpages'] = 'Editar p&#225;gina';
    $lang['strfilename'] = 'Nombre del archivo';
    $lang['strcompleted'] = 'Completado';
    $lang['strdelpage'] = '&iquest;Realmente deseas eliminar &#233;sta p&#225;gina?';
    $lang['strdelpages'] = '&iquest;Realmente deseas eliminar las p&#225;ginas seleccionadas?';
    $lang['strdeletedpage'] = 'P&#225;gina eliminada.';
    $lang['strdeletedpages'] = 'P&#225;ginas eliminadas.';
    $lang['strerrdelpage'] = 'Fall&#243; la eliminaci&#243;n de la p&#225;gina.';
    $lang['strpageinfo'] = 'Informaci&#243;n de la p&#225;gina';
    $lang['strpagetitle'] = 'Titulo';
    $lang['strpagemainmenu'] = 'Men&#250; principal';
    $lang['strpageonmainmenu'] = '&iquest;Mostrar &#233;sta p&#225;gina en el men&#250;?';
    $lang['strpagecontent']= 'Contenido';
    $lang['strfieldname'] = 'Columna';
    $lang['strdisplay'] = 'Mostrar';
    $lang['strdisplayname'] = 'Nombre a mostrar';
    $lang['strshowinpage'] = 'Visible';
    $lang['strremotecol'] = 'Columna remota';
    $lang['strfkvalue'] = 'Valor de la clave for&#225;nea';
    $lang['strmaninp'] = 'Entrada manual';
    $lang['strpriority'] = 'Orden para mostrar';
    $lang['strsavepagesuccessful'] = 'P&#225;gina editada correctamente';
    
    //Generation
    $lang['strtheme'] = 'Tema';
    $lang['strgenerating'] = 'Generando';
    $lang['strpreview'] = 'Vista previa';
    $lang['strphplibrary'] = 'Biblioteca de PHP';
    $lang['strpgsql'] = 'pgsql';
    $lang['strpdo'] = 'pdo_pgsql';   
    $lang['strinsertsuccess'] = 'La informaci&#243;n fue a&ntilde;adida correctamente';
    $lang['strinsertfail'] = 'Ocurri&#243; un problema al a&ntilde;adir la informaci&#243;n';
    $lang['strupdatesuccess'] = 'Informaci&#243;n editada correctamente';
    $lang['strrecordnoexist'] = 'El registro seleccionado no existe';
    $lang['strupdatefail'] = 'Ocurri&#243; un problema al editar la informaci&#243;n';
    $lang['strasc'] = 'Ascendente';
    $lang['strdesc'] = 'Descendente';
    $lang['strsortby'] = 'Order por';
    $lang['strsrows'] = 'filas';
    $lang['strdelsucess'] = 'Datos eliminados correctamente';
    $lang['strsearch'] = 'Buscar';
    $lang['stremptyrows'] = 'No existen datos que satisfacen el criterio de b&#250;squeda.';
    $lang['strgotopage'] = 'Ir a la p&#225;gina: ';
    $lang['strselectval'] = '--Seleccione--';
    $lang['strnoSeleccioneeditems'] = 'Por favor seleccione algunos &#237tems para continuar.';
    $lang['strconfirmdelete'] = '&iquest;Est&#225; seguro que desea eliminar los datos seleccionados?';
    $lang['strwriteprimarykey'] = 'Escribe la clave primaria del registro a editar.';
    
    //Errors
    $lang['strerrnotbl'] = 'El esquema seleccionado est&#225; vac&#237o, debes tener algunas tablas para crear la aplicaci&#243;n.';
    $lang['strnocrudgendb'] = 'El esquema de CrudGen no est&#225; instalado, por favor lee el archivo INSTALL (localizado en la carpeta del plugin) para conocer las instrucciones.';
    $lang['strnoappname'] = 'Debes darle un nombre para tu aplicaci&#243;n.';
    $lang['strnohost'] = 'Debes especificar el host de la base de datos.';
    $lang['strnoport'] = 'Debes especificar el puerto de la base de datos.';
    $lang['strnousername'] = 'Debes especificar el usuario de la base de datos.';
    $lang['strnotablecol'] = 'Debes especificar la tabla de la base de datos donde la informaci&#243;n de acceso est&#225; almacenada.';
    $lang['strnousercol'] = 'Debes especificar la columna de donde la informaci&#243;n del usuario est&#225; almacenada.';
    $lang['strnopasscol'] = 'Debes especificar la columna de donde la informaci&#243;n de la contrase&ntilde;a est&#225; almacenada.';
    $lang['strnouniquename'] = 'Ya existe una aplicaci&#243;n con ese nombre, debes usar otro.';
    $lang['strerrorappsavedb'] = 'Ocurri&#243; un problema cuando se guardaba la aplicaci&#243;n en la base de datos.';
    $lang['strnopages'] = 'No hay p&#225;ginas para &#233;sta aplicaci&#243;n.';
    $lang['strnopagesgenerate'] = 'La aplicaci&#243;n no tiene p&#225;ginas listas para ser generadas. Por favor agrega previamente su informaci&#243;n.';
    $lang['strnopagetitle'] = 'Debes escribir el t&#237tulo de la p&#225;gina.';
    $lang['strnopagefilename'] = 'Debes escribir el nombre del archivo.';
    $lang['strerrnoextension'] = 'Debes escribir la extensi&#243;n php en el nombre del archivo.';
    $lang['strnodisplayname'] = 'Falta el nombre a mostrar.';
    $lang['strpageerrsavedb'] = 'Ocurri&#243; un problema cuando se guardaba la p&#225;gina en la base de datos.';
    $lang['strnouniquefilename'] = 'Existe otra p&#225;gina en la aplicaci&#243;n con el mismo nombre del archivo.';
    $lang['strerrfielddb'] = 'Ocurri&#243; un problema cuando se guardaba las columnas en la base de datos';
    $lang['strselpagetodelete'] = 'Seleccione las p&#225;ginas a eliminar';
    $lang['strselapptodelete'] = 'Seleccione las aplicaciones a eliminar';
    $lang['strerrnoappid'] = 'Falta el identificador de la aplicaci&#243;n';
    $lang['strnocommonfile'] = 'Ocurri&#243; un problema cuando se creaba el archivo com&#250;n.';
    $lang['strloginerror'] = '&iexcl;Inicio de sesi&#243;n incorrecto! Chequea si el usuario y contrase&ntilde;a son los correctos.';
    $lang['strerrpagegen'] = 'Ocurri&#243; un error cuando se generaba ';
    $lang['strerrordbconn'] = 'No hay conexi&#243;n a la base de datos';
    $lang['strerrorquery'] = 'Ocurri&#243; un problema cuando se ejecutaba la consulta';
    $lang['strrowdeletedbad'] =  'Ocurri&#243; un problema cuando se eliminaban los datos';
    $lang['strnorowstodelete'] =  'No se han seleccionado registros para eliminar.';
    $lang['strnoselecteditem'] =  'No se han seleccionado registros para editar.';
    $lang['strnomoreitems'] =  'No hay more registros a editar.';
    $lang['strpageerredit'] = 'Ocurri&#243; un problema cuando se editaba la informaci&#243;n.';    
?>
