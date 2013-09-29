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

define('FRC Scoring Page', 1);

require_once('../core/config.php');

if(!isset($_POST['submit']))
{
    header("Location: ../index.php?do=admin");
    exit();
}
else
{
    $input = $_POST['password'];
    
    if(sha1($input) == sha1($config_admin_password))
    {
        setcookie('session', sha1($config_admin_password.$_SERVER['REMOTE_ADDR']), time() + 60 * 60 * 24 * 7, '/');
        header("Location: ../index.php?do=admin");
    }
    else
    {
        header("Location: ../index.php?do=admin&incorrect");
    }
}


?>