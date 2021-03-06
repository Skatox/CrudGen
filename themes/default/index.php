<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
        <title><?php printTitle();?></title>
        <link href="css/reset.css" rel="stylesheet" type="text/css" />
        <link href="css/style.css" rel="stylesheet" type="text/css" />
        <link href="css/jquery-ui.min.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <div id="header">
            <h1><?php printTitle();?></h1>
            <h2><?php printDescr();?></h2>
        </div>
        <?php if(checkAccess()) :  ?>
        <div id="content-wrapper">

            
            <div id="content">
                <h2><?php printPageTitle() ?></h2>
                <h3><?php printPageDescr() ?></h3>

                <div id="info">
                    <?php printPageText() ?>
                </div>

                <form action="<?php printFormAction() ?>" id="operation-form" name="operation-form" method="post">
                    <div id="operation-wrapper">
                        <?php printActionButtons() ?>
                        <?php pageOperation() ?>
                        <?php printActionButtons() ?>
                    </div>
                </form>
            </div>
        </div>
        <div id="sidebar">
            <h3>Menu</h3>
            <?php printMenu();?>
            <div class="logout">
                <?php printLogout();?>
            </div>
        </div>
        <?php endif; ?>
        <div id="footer">
            <div class="generated">
                Generated with <a href="https://github.com/Skatox/crudgen" target="_blank">CrudGen</a>.
            </div>
            <div class="xhtml-valid">
                <a href="http://validator.w3.org/check?uri=referer">
                    <img src="img/valid-xhtml10.png" alt="Valid XHTML 1.0 Transitional"/>
                </a>
            </div>
        </div>
        <?php printMessages() ?>
        <script type="text/javascript" src="js/jquery-1.11.2.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui.min.js"></script>
        <script type="text/javascript" src="js/jquery.validate.min.js"></script>
        <script type="text/javascript" src="js/jscode.min.js"></script>
    </body>
</html>
