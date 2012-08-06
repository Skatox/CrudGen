<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
        <title><?php printTitle();?></title>
        <link href="style.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="scripts.js"></script>
        <script type="text/javascript" src="jquery-1.3.2.min.js"></script>
        <script type="text/javascript" src="cal.js"></script>
    </head>
    <body>
        <div class="header">
            <h1><?php printTitle();?></h1>
            <h2><?php printDescr();?></h2>
        </div>
        <div class="content">
            
        <?php if(checkAccess()) :  ?>
            
            <div class="sidebar">
                <h2 class="title">MENU</h2>
                    <?php printMenu();?>
            </div>
            <div class="main">
                <h2><?php printPageTitle();?></h2>
                <h3><?php printPageDescr();?></h3>
                <p><?php printPageText();?></p>
                <form action="" name="op_form" method="post">
                    <div style="text-align: center;">
                            <?php
                            pageOperation();
                            ?>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <div class="footer">
            <p>
                <a href="http://validator.w3.org/check?uri=referer">
                    <img src="images/valid-xhtml10.png"
                         alt="Valid XHTML 1.0 Transitional" height="31" width="88" />
                </a>-
                <a href="http://jigsaw.w3.org/css-validator/check/referer">
                    <img style="border:0;width:88px;height:31px"
                         src="images/vcss.gif" alt="¡CSS Válido!" />
                </a>
            </p>
        </div>
    </body>
</html>
