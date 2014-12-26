CRUDGen
=====

CRUDGen is a plugin for [phpPgAdmin](http://phppgadmin.sourceforge.net/) to generate CRUD (Create, Report, Update and Delete) pages from tables in the database. 

This was my university's thesis project, so if you see some mistakes and designs errors, it was due to the lack of experience, but after all this time I've tried to solve most them and plugins works.

Before you file an issue, make sure you have read the _[known issues](#known-issues)_ and _[file an issue](#file-an-issue)_ sections that contains some important information.

Features
--------

* Generates Create, Report (with delete options) and Update pages **for tables with a single primary key**.
* Generated code is easy to understand, well formated, uses web standards and open technologies.
* Generated pages supports authentication using Database's users or information stored in a table.
* It is integrated to phpPgAdmin, so you'll have a powerful database administration tool with this app.


Screencast
----------

You can see how this plugin works before installing it by watching [Crudgen 0.1: A CRUD generator plugin for Postgres with phppgadmin](https://www.youtube.com/watch?v=ZjMyptlcYg4) on Youtube.


Installation
------------

1.  You must have [phpPgAdmin](http://phppgadmin.sourceforge.net/) installed
    and configurated to work with your servers.

2.  Download [CRUDGen](https://github.com/Skatox/crudgen/archive/master.zip),
    extract the content of the folder `crudgen-master` to `plugins/CrudGen`.

3.  Activate the plugin activation by opening `conf/config.inc.php` and add 
	the **CrudGen** value in the `$conf['plugins']` like this:

   		$conf['plugins'] = array('CrudGen');

4.	Then, you'll need to set up the CrudGen database, you can run the script
	located at plugin's `sql` subdirectory (you'll find more information inside
	this script) by executing this sql command:

		psql template1 < crudgen-pgsql.sql

5.	Finally, you must add the role `crudgen_admin` to the database user that 
	will use CrudGen.

		GRANT crudgen_admin TO your_db_user;

6. 	Done! If you go to a schema, you'll see a CrudGen icon at the left menu
	or at the top tabs.

Known issues
------------

Currently this stuff is not implement but any help is welcome to get this features:
* You can't create pages that work multi-tables.
* Tables with more than 1 primary key don't work.
* There's no support for multi-schema situations.

File an issue
-------------

You can report bugs and feature requests to [GitHub Issues](https://github.com/Skatox/crugen/issues).

**Please don't ask question in the issue tracker**, instead ask them by sending me an email.

When you file a bug, please try to follow these simple rules if applicable:

* Make sure you've read the README carefully.
* Make sure that phpPgAdmin is working ok.
* Add debug information (if possible) and explain the steps that you did to produce the error.
* Provide information about your environment:
  * Your current versions of your Postgresql, PHP, phpPgAdmin.
  * Explain the structure of your Database, Schema and Table.
* Make sure that the issue is reproducible with your description.

**It's most likely that your bug gets resolved faster if you provide as much information as possible!**

Development
-----------

Pull requests are very welcome! Start forking this repo and develop new features or improve existing ones. Anything that helps to improve this software can be added to the project.

### Author

[Miguel Useche](https://github.com/Skatox) ([@skatox](http://twitter.com/skatox))