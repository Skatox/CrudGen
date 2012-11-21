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
    $lang['strdescr'] = 'Descripción';
    
    //Basic strings
    $lang['strcreate'] = 'Crear';
    $lang['strupdate'] = 'Editar';
    $lang['strreport'] = 'Reportar';
    $lang['strdelete'] = 'Eliminar';
    $lang['stractions'] = 'Acciones';
    $lang['stroperation'] = 'Operación';
    $lang['strno'] = 'No';
    $lang['stryes'] = 'Sí';
    $lang['strorder'] = 'Orden';
    
    //aplicación
    $lang['strcreateapp'] = 'Crear una nueva aplicación ';
    $lang['streditapp'] = 'Editar aplicación';
    $lang['strnoapps'] = 'No hay aplicaciones';
    $lang['strappwizard'] = 'Asistente de aplicaciones';
    $lang['strsecaccess'] = 'Acceso a la aplicación';
    $lang['strnosecurity'] = 'Sin seguridad';
    $lang['strnosecuritytxt'] = '(No pregunta para autenticar)';
    $lang['strsecdbuser'] = 'Usando usuarios de la base de datos';
    $lang['strsecdbusertxt'] = '(Usa las credenciales de la base datos, el archivo pg_hba.conf debe estar bien configurado)';
    $lang['strsecdbstored'] = 'Los usuarios y contraseñas están almacenados en una tabla de la base de datos';
    $lang['strsecdbstoredtxt']= '(Seleccione las columnas de la base de datos donde se guardan los datos de los usuarios y contraseñas)';
    $lang['strselsecurity'] = 'Seleccione el tipo de acceso -->';
    $lang['strappsaved'] = 'Aplicación creada.';
    $lang['strappedited'] = 'Aplicación editada.';
    $lang['strappnotsaved'] = 'Fallo la creación de la aplicación.';
    $lang['strconfdelapp'] = '¿Realmente deseas eliminar esta aplicación?';
    $lang['strconfdelapps'] = '¿Realmente deseas eliminar las aplicaciones seleccionadas?';
    $lang['strdelapp'] = 'Aplicación eliminada.';
    $lang['strdelapps'] = 'Aplicaciones eliminadas.';
    $lang['strerrdelapp'] = 'Falló la eliminación de la aplicación.';
    
    //Wizard
    $lang['strstep'] = 'Paso';
    $lang['strall'] = 'Todos';
    $lang['strnone'] = 'Ninguno';
    $lang['strtbldetect'] = 'Seleccione las columnas a ser usadas por la aplicación';
    $lang['stratbldetectwarn'] = 'Los campos No Nulos son seleccionados por defecto, mas tarde puedes omitir aquellos con valores predeterminados';
    $lang['strseloperation'] = 'Seleccione los campos que deseas mostrar en cada página de la aplicación';
    $lang['strpagesdetected'] = 'Las siguientes páginas van a ser creadas';
    $lang['strreportpages'] = 'Páginas de reportes y búsqueda';
    $lang['strcreatepages'] = 'Páginas para agregar datos';
    $lang['strupdatepages'] = 'Páginas para editar datos';
    $lang['strdeletepages'] = 'Páginas para eliminar datos';
    $lang['strclickaddpages'] = 'Haz clic aquó para agregar mas páginas';
    $lang['strthefile'] = 'El archivo ';
    $lang['strfilecreation'] = 'va a ser creado para trabajar con las siguientes columnas:';
    $lang['strsavepagessuccessful']= 'Página agregada exitosamente.';
    $lang['strappdatatxt'] = 'Rellena los datos requerida para empezar a generar la aplicación:';

    //Pages
    $lang['strmanagepage'] = 'Gestionar páginas';
    $lang['strpages'] = 'Páginas';
    $lang['straddpages'] = 'Agregar página';
    $lang['streditpages'] = 'Editar página';
    $lang['strfilename'] = 'Nombre del archivo';
    $lang['strcompleted'] = 'Completado';
    $lang['strdelpage'] = '¿Realmente deseas eliminar ésta página?';
    $lang['strdelpages'] = '¿Realmente deseas eliminar las páginas seleccionadas?';
    $lang['strdeletedpage'] = 'Página eliminada.';
    $lang['strdeletedpages'] = 'Páginas eliminadas.';
    $lang['strerrdelpage'] = 'Falló la eliminación de la página.';
    $lang['strpageinfo'] = 'Información de la página';
    $lang['strpagetitle'] = 'Titulo';
    $lang['strpagemainmenu'] = 'Menú principal';
    $lang['strpageonmainmenu'] = '¿Mostrar ésta página en el menú?';
    $lang['strpagecontent']= 'Contenido';
    $lang['strfieldname'] = 'Columna';
    $lang['strdisplay'] = 'Mostrar';
    $lang['strdisplayname'] = 'Nombre a mostrar';
    $lang['strshowinpage'] = 'Visible';
    $lang['strremotecol'] = 'Columna remota';
    $lang['strfkvalue'] = 'Valor de la clave foránea';
    $lang['strmaninp'] = 'Entrada manual';
    $lang['strpriority'] = 'Orden para mostrar';
    $lang['strsavepagesuccessful'] = 'Página editada correctamente';
    
    //Generation
    $lang['strtheme'] = 'Tema';
    $lang['strgenerating'] = 'Generando';
    $lang['strpreview'] = 'Vista previa';
    $lang['strphplibrary'] = 'Biblioteca de PHP';
    $lang['strpgsql'] = 'pgsql';
    $lang['strpdo'] = 'pdo_pgsql';   
    $lang['strinsertsuccess'] = 'La información fue añadida correctamente';
    $lang['strinsertfail'] = 'Ocurrió un problema al añadir la información';
    $lang['strupdatesuccess'] = 'Información editada correctamente';
    $lang['strrecordnoexist'] = 'El registro seleccionado no existe';
    $lang['strupdatefail'] = 'Ocurrió un problema al editar la información';
    $lang['strasc'] = 'Ascendente';
    $lang['strdesc'] = 'Descendente';
    $lang['strsortby'] = 'Order por';
    $lang['strsrows'] = 'filas';
    $lang['strdelsucess'] = 'Datos eliminados correctamente';
    $lang['strsearch'] = 'Buscar';
    $lang['stremptyrows'] = 'No existen datos que satisfacen el criterio de búsqueda.';
    $lang['strgotopage'] = 'Ir a la página: ';
    $lang['strselectval'] = '--Seleccione--';
    $lang['strnoSeleccioneeditems'] = 'Por favor seleccione algunos ítems para continuar.';
    $lang['strconfirmdelete'] = '¿Está seguro que desea eliminar los datos seleccionados?';
    $lang['strwriteprimarykey'] = 'Escribe la clave primaria del registro a editar.';
    
    //Errors
    $lang['strerrnotbl'] = 'El esquema seleccionado está vacío, debes tener algunas tablas para crear la aplicación.';
    $lang['strnocrudgendb'] = 'El esquema de CrudGen no está instalado, por favor lee el archivo INSTALL (localizado en la carpeta del plugin) para conocer las instrucciones.';
    $lang['strnoappname'] = 'Debes darle un nombre a tu aplicación.';
    $lang['strnohost'] = 'Debes especificar el host de la base de datos.';
    $lang['strnoport'] = 'Debes especificar el puerto de la base de datos.';
    $lang['strnousername'] = 'Debes especificar el usuario de la base de datos.';
    $lang['strnotablecol'] = 'Debes especificar la tabla de la base de datos donde la información de acceso está almacenada.';
    $lang['strnousercol'] = 'Debes especificar la columna de donde la información del usuario está almacenada.';
    $lang['strnopasscol'] = 'Debes especificar la columna de donde la información de la contraseña está almacenada.';
    $lang['strnouniquename'] = 'Ya existe una aplicación con ese nombre, debes usar otro.';
    $lang['strerrorappsavedb'] = 'Ocurrió un problema cuando se guardaba la aplicación en la base de datos.';
    $lang['strnopages'] = 'No hay páginas para ésta aplicación.';
    $lang['strnopagesgenerate'] = 'La aplicación no tiene páginas listas para ser generadas. Por favor agrega previamente su información.';
    $lang['strnopagetitle'] = 'Debes escribir el título de la página.';
    $lang['strnopagefilename'] = 'Debes escribir el nombre del archivo.';
    $lang['strerrnoextension'] = 'Debes escribir la extensión php en el nombre del archivo.';
    $lang['strnodisplayname'] = 'Falta el nombre a mostrar.';
    $lang['strpageerrsavedb'] = 'Ocurrió un problema cuando se guardaba la página en la base de datos.';
    $lang['strnouniquefilename'] = 'Existe otra página en la aplicación con el mismo nombre del archivo.';
    $lang['strerrfielddb'] = 'Ocurrió un problema cuando se guardaba las columnas en la base de datos';
    $lang['strselpagetodelete'] = 'Seleccione las páginas a eliminar';
    $lang['strselapptodelete'] = 'Seleccione las aplicaciones a eliminar';
    $lang['strerrnoappid'] = 'Falta el identificador de la aplicación';
    $lang['strnocommonfile'] = 'Ocurrió un problema cuando se creaba el archivo común.';
    $lang['strloginerror'] = '¡Inicio de sesión incorrecto! Chequea si el usuario y contraseña son los correctos.';
    $lang['strerrpagegen'] = 'Ocurrió un error cuando se generaba ';
    $lang['strgenerror'] = 'Ocurrió un error cuando se generaba la aplicación.';
    $lang['strerrordbconn'] = 'No hay conexión a la base de datos';
    $lang['strerrorquery'] = 'Ocurrió un problema cuando se ejecutaba la consulta';
    $lang['strrowdeletedbad'] =  'Ocurrió un problema cuando se eliminaban los datos';
    $lang['strnorowstodelete'] =  'No se han seleccionado registros a eliminar.';
    $lang['strnoselecteditem'] =  'No se han seleccionado registros a editar.';
    $lang['strnomoreitems'] =  'No hay more registros a editar.';
    $lang['strpageerredit'] = 'Ocurrió un problema cuando se editaba la información.';    
?>
