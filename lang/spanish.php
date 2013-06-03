<?php

    /**
        * Spanish language file for plugin CrudGen.  Use this as a basis
        * for new translations.
        */

    // Language and character set
    $plugin_lang['appcharset'] = 'ISO-8859-1';
    $plugin_lang['applocale'] = 'es_ES';
    $plugin_lang['appdbencoding'] = 'LATIN1';
    $plugin_lang['applangdir'] = 'ltr';

    //Plugin data
    $plugin_lang['strdescription'] = 'Generador CRUD';

	//Links strings
    $plugin_lang['strid'] = 'ID';
    $plugin_lang['strgenerate'] = 'Generar';
    $plugin_lang['strdescr'] = 'Descripción';
    
    //Basic strings
    $plugin_lang['strcreate'] = 'Crear';
    $plugin_lang['strupdate'] = 'Editar';
    $plugin_lang['strreport'] = 'Reportar';
    $plugin_lang['strdelete'] = 'Eliminar';
    $plugin_lang['stractions'] = 'Acciones';
    $plugin_lang['stroperation'] = 'Operación';
    $plugin_lang['strno'] = 'No';
    $plugin_lang['stryes'] = 'Sí';
    $plugin_lang['strorder'] = 'Orden';
    
    //aplicación
    $plugin_lang['strcreateapp'] = 'Crear una nueva aplicación ';
    $plugin_lang['streditapp'] = 'Editar aplicación';
    $plugin_lang['strnoapps'] = 'No hay aplicaciones';
    $plugin_lang['strappwizard'] = 'Asistente de aplicaciones';
    $plugin_lang['strsecaccess'] = 'Acceso a la aplicación';
    $plugin_lang['strnosecurity'] = 'Sin seguridad';
    $plugin_lang['strnosecuritytxt'] = '(No pregunta para autenticar)';
    $plugin_lang['strsecdbuser'] = 'Usando usuarios de la base de datos';
    $plugin_lang['strsecdbusertxt'] = '(Usa las credenciales de la base datos, el archivo pg_hba.conf debe estar bien configurado)';
    $plugin_lang['strsecdbstored'] = 'Los usuarios y contraseñas están almacenados en una tabla de la base de datos';
    $plugin_lang['strsecdbstoredtxt']= '(Seleccione las columnas de la base de datos donde se guardan los datos de los usuarios y contraseñas)';
    $plugin_lang['strselsecurity'] = 'Seleccione el tipo de acceso -->';
    $plugin_lang['strappsaved'] = 'Aplicación creada.';
    $plugin_lang['strappedited'] = 'Aplicación editada.';
    $plugin_lang['strappnotsaved'] = 'Fallo la creación de la aplicación.';
    $plugin_lang['strconfdelapp'] = '¿Realmente deseas eliminar esta aplicación?';
    $plugin_lang['strconfdelapps'] = '¿Realmente deseas eliminar las aplicaciones seleccionadas?';
    $plugin_lang['strdelapp'] = 'Aplicación eliminada.';
    $plugin_lang['strdelapps'] = 'Aplicaciones eliminadas.';
    $plugin_lang['strerrdelapp'] = 'Falló la eliminación de la aplicación.';
    
    //Wizard
    $plugin_lang['strstep'] = 'Paso';
    $plugin_lang['strall'] = 'Todos';
    $plugin_lang['strnone'] = 'Ninguno';
    $plugin_lang['strtbldetect'] = 'Seleccione las columnas a ser usadas por la aplicación';
    $plugin_lang['stratbldetectwarn'] = 'Los campos No Nulos son seleccionados por defecto, mas tarde puedes omitir aquellos con valores predeterminados';
    $plugin_lang['strseloperation'] = 'Seleccione los campos que deseas mostrar en cada página de la aplicación';
    $plugin_lang['strpagesdetected'] = 'Las siguientes páginas van a ser creadas';
    $plugin_lang['strreportpages'] = 'Páginas de reportes y búsqueda';
    $plugin_lang['strcreatepages'] = 'Páginas para agregar datos';
    $plugin_lang['strupdatepages'] = 'Páginas para editar datos';
    $plugin_lang['strdeletepages'] = 'Páginas para eliminar datos';
    $plugin_lang['strclickaddpages'] = 'Haz clic aquó para agregar mas páginas';
    $plugin_lang['strthefile'] = 'El archivo ';
    $plugin_lang['strfilecreation'] = 'va a ser creado para trabajar con las siguientes columnas:';
    $plugin_lang['strsavepagessuccessful']= 'Página agregada exitosamente.';
    $plugin_lang['strappdatatxt'] = 'Rellena los datos requerida para empezar a generar la aplicación:';

    //Pages
    $plugin_lang['strmanagepage'] = 'Gestionar páginas';
    $plugin_lang['strpages'] = 'Páginas';
    $plugin_lang['straddpages'] = 'Agregar página';
    $plugin_lang['streditpages'] = 'Editar página';
    $plugin_lang['strfilename'] = 'Nombre del archivo';
    $plugin_lang['strcompleted'] = 'Completado';
    $plugin_lang['strdelpage'] = '¿Realmente deseas eliminar ésta página?';
    $plugin_lang['strdelpages'] = '¿Realmente deseas eliminar las páginas seleccionadas?';
    $plugin_lang['strdeletedpage'] = 'Página eliminada.';
    $plugin_lang['strdeletedpages'] = 'Páginas eliminadas.';
    $plugin_lang['strerrdelpage'] = 'Falló la eliminación de la página.';
    $plugin_lang['strpageinfo'] = 'Información de la página';
    $plugin_lang['strpagetitle'] = 'Titulo';
    $plugin_lang['strpagemainmenu'] = 'Menú principal';
    $plugin_lang['strpageonmainmenu'] = '¿Mostrar ésta página en el menú?';
    $plugin_lang['strpagecontent']= 'Contenido';
    $plugin_lang['strfieldname'] = 'Columna';
    $plugin_lang['strdisplay'] = 'Mostrar';
    $plugin_lang['strdisplayname'] = 'Nombre a mostrar';
    $plugin_lang['strshowinpage'] = 'Visible';
    $plugin_lang['strremotecol'] = 'Columna remota';
    $plugin_lang['strfkvalue'] = 'Valor de la clave foránea';
    $plugin_lang['strmaninp'] = 'Entrada manual';
    $plugin_lang['strpriority'] = 'Orden para mostrar';
    $plugin_lang['strsavepagesuccessful'] = 'Página editada correctamente';
    
    //Generation
    $plugin_lang['strtheme'] = 'Tema';
    $plugin_lang['strgenerating'] = 'Generando';
    $plugin_lang['strpreview'] = 'Vista previa';
    $plugin_lang['strphplibrary'] = 'Biblioteca de PHP';
    $plugin_lang['strpgsql'] = 'pgsql';
    $plugin_lang['strpdo'] = 'pdo_pgsql';   
    $plugin_lang['strinsertsuccess'] = 'La información fue añadida correctamente';
    $plugin_lang['strinsertfail'] = 'Ocurrió un problema al añadir la información';
    $plugin_lang['strupdatesuccess'] = 'Información editada correctamente';
    $plugin_lang['strrecordnoexist'] = 'El registro seleccionado no existe';
    $plugin_lang['strupdatefail'] = 'Ocurrió un problema al editar la información';
    $plugin_lang['strasc'] = 'Ascendente';
    $plugin_lang['strdesc'] = 'Descendente';
    $plugin_lang['strsortby'] = 'Order por';
    $plugin_lang['strsrows'] = 'filas';
    $plugin_lang['strdelsucess'] = 'Datos eliminados correctamente';
    $plugin_lang['strsearch'] = 'Buscar';
    $plugin_lang['stremptyrows'] = 'No existen datos que satisfacen el criterio de búsqueda.';
    $plugin_lang['strgotopage'] = 'Ir a la página: ';
    $plugin_lang['strselectval'] = '--Seleccione--';
    $plugin_lang['strnoselecteditems'] = 'Por favor seleccione algunos ítems para continuar.';
    $plugin_lang['strconfirmdelete'] = '¿Está seguro que desea eliminar los datos seleccionados?';
    $plugin_lang['strwriteprimarykey'] = 'Escribe la clave primaria del registro a editar.';
    
    //Errors
    $plugin_lang['strerrnotbl'] = 'El esquema seleccionado está vacío, debes tener algunas tablas para crear la aplicación.';
    $plugin_lang['strnocrudgendb'] = 'El esquema de CrudGen no está instalado, por favor lee el archivo INSTALL (localizado en la carpeta del plugin) para conocer las instrucciones.';
    $plugin_lang['strnoappname'] = 'Debes darle un nombre a tu aplicación.';
    $plugin_lang['strnohost'] = 'Debes especificar el host de la base de datos.';
    $plugin_lang['strnoport'] = 'Debes especificar el puerto de la base de datos.';
    $plugin_lang['strnousername'] = 'Debes especificar el usuario de la base de datos.';
    $plugin_lang['strnotablecol'] = 'Debes especificar la tabla de la base de datos donde la información de acceso está almacenada.';
    $plugin_lang['strnousercol'] = 'Debes especificar la columna de donde la información del usuario está almacenada.';
    $plugin_lang['strnopasscol'] = 'Debes especificar la columna de donde la información de la contraseña está almacenada.';
    $plugin_lang['strnouniquename'] = 'Ya existe una aplicación con ese nombre, debes usar otro.';
    $plugin_lang['strerrorappsavedb'] = 'Ocurrió un problema cuando se guardaba la aplicación en la base de datos.';
    $plugin_lang['strnopages'] = 'No hay páginas para ésta aplicación.';
    $plugin_lang['strnopagesgenerate'] = 'La aplicación no tiene páginas listas para ser generadas. Por favor agrega previamente su información.';
    $plugin_lang['strnopagetitle'] = 'Debes escribir el título de la página.';
    $plugin_lang['strnopagefilename'] = 'Debes escribir el nombre del archivo.';
    $plugin_lang['strerrnoextension'] = 'Debes escribir la extensión php en el nombre del archivo.';
    $plugin_lang['strnodisplayname'] = 'Falta el nombre a mostrar.';
    $plugin_lang['strnodisplaycolumns'] = 'Debe tener al menos una columna visible';
    $plugin_lang['strpageerrsavedb'] = 'Ocurrió un problema cuando se guardaba la página en la base de datos.';
    $plugin_lang['strnouniquefilename'] = 'Existe otra página en la aplicación con el mismo nombre del archivo.';
    $plugin_lang['strerrfielddb'] = 'Ocurrió un problema cuando se guardaba las columnas en la base de datos';
    $plugin_lang['strselpagetodelete'] = 'Seleccione las páginas a eliminar';
    $plugin_lang['strselapptodelete'] = 'Debe tener al menos una columna visible';
    $plugin_lang['strerrnoappid'] = 'Falta el identificador de la aplicación';
    $plugin_lang['strnocommonfile'] = 'Ocurrió un problema cuando se creaba el archivo común.';
    $plugin_lang['strloginerror'] = '¡Inicio de sesión incorrecto! Chequea si el usuario y contraseña son los correctos.';
    $plugin_lang['strerrpagegen'] = 'Ocurrió un error cuando se generaba ';
    $plugin_lang['strgenerror'] = 'Ocurrió un error cuando se generaba la aplicación.';
    $plugin_lang['strerrordbconn'] = 'No hay conexión a la base de datos';
    $plugin_lang['strerrorquery'] = 'Ocurrió un problema cuando se ejecutaba la consulta';
    $plugin_lang['strrowdeletedbad'] =  'Ocurrió un problema cuando se eliminaban los datos';
    $plugin_lang['strnorowstodelete'] =  'No se han seleccionado registros a eliminar.';
    $plugin_lang['strnoselecteditem'] =  'No se han seleccionado registros a editar.';
    $plugin_lang['strnomoreitems'] =  'No hay more registros a editar.';
    $plugin_lang['strpageerredit'] = 'Ocurrió un problema cuando se editaba la información.';    
?>
