<?php
    $user = isset($_POST['crudgen_user']) ? $_POST['crudgen_user'] : '';
    $passwd = isset($_POST['crudgen_passwd']) ? $_POST['crudgen_passwd'] : '';
?>
<div class="login">
    <form action="" id="login-form" method="post">
        <div class="row">
            <label for="crudgen_user">User</label>
            <input type="text" id="crudgen_user" name="crudgen_user" value="<?php echo $user ?>"/>
        </div>
        <div class="row">
            <label for="crudgen_password">Password</label>
            <input type="password" id="crudgen_passwd" name="crudgen_passwd" value="<?php echo $passwd ?>"/>
        </div>
        <div class="row buttons">
            <input type="submit" name="button-send" value="Login" />
        </div>
    </form>
</div>
