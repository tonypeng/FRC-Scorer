<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2012 Tony Peng
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

if(!defined('FRC Scoring Page'))
{
    header("Location: ../index.php?do=admin");
    exit();
}

if(!isadmin())
{
    // login
    breadcrumbAppend('Admin Panel', 'index.php?do=admin');
    breadcrumbAppend('Login');
    
    breadcrumbEnd();
    
    themeSpacer();
    
    themeContentBegin();
    
    if(isset($_GET['incorrect']))
    {
        echo '<span style="color:#FF0000;">Incorrect password.</span>'."\n" .'<br /><br />';
    }
    ?>
    <h6>Login</h6>
    <span style="height: 5px;"></span>
    <form action="admin/login.php" method="POST">
        Password: 
        <input type="password" name="password" />&nbsp;<input type="submit" name="submit" value="Login" />
    </form>
    <?php
 
    themeSpacer(5);
    
    themeContentEnd();
}
else
{
    require_once("admin/admin.php");
}
?>