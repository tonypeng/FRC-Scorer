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
    exit('Invalid environment.');

require_once(FILE_ROOT . '/core/config.php');

date_default_timezone_set('America/Los_Angeles');

$global_theme_dir = '';

$global_breadcrumbs = '';

$global_navbar = '';

function loadTheme($themedir='themes/lite/')
{
    global $global_theme_dir;
    
    $global_theme_dir = $themedir;
    
    include_once($global_theme_dir . 'theme_core.php');
}

function themeHeader()
{
    global $global_theme_dir, $config_site_name;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title><?php echo $config_site_name; ?></title>
    <link rel="shortcut icon" href="img/shortcut_icon.png" />
	<link rel="stylesheet" media="screen" type="text/css" href="<?php echo $global_theme_dir; ?>style.css" />
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/funcs.js"></script>
</head>
<body>
<?php
    
    // include_once($global_theme_dir . 'theme_core.php');
    
    theme_header();
}

function themeContentBegin()
{
    theme_content_begin();
}

function themeContentEnd()
{
    theme_content_end();
}

function themeSpacer($pixels=1)
{
    theme_spacer($pixels);
}

function themeEnd()
{
    global $global_theme_dir, $start;
  
    theme_end();  
    
		$after = (date("Y") == "2012") ? "" : " - " . date("Y");
?>
<br />
<div>Scoring software is &copy; 2012<?php echo $after; ?> <a href="http://tonypeng.com/">Tony Peng</a></div>
</div>
<?php
	
    if(isset($start))
    {
?>
    <div style="text-align: center;">Page loaded in <?php $end = microtime(); $endarray = explode(' ', $end); $end = $endarray[1] + $endarray[0]; $total = $end - $start; $total = $total / 1000; $total = round($total, 5); $total = $total / 1000.0; echo $total; ?>ms.</div>
<?php
    }
?>
</body>
</html>
<?php

    // include_once($global_theme_dir . 'theme_core.php');
}

function navbarHeader()
{
    theme_navtag_header();
}

function navbarAdd($append, $page, $isurl=true)
{
    global $global_navbar;
    
    $append = ($page == '') ? $append : ($isurl ? '<a href="' . $page . '">' : '') . $append . ($isurl ? '</a>' : '');
    
    $global_navbar .= ($global_navbar == '' ? '' : ' &middot; ') . $append;
}

function navbarEnd()
{
    global $global_navbar;
    
    # flush breadcrumb
    echo $global_navbar;
    
    theme_navtag_close();
}


function breadcrumbHeader()
{
    theme_breadcrumb_header();
}

function breadcrumbAppend($append, $page='')
{
    global $global_breadcrumbs;
    
    $append = ($page == '') ? $append : '<a href="' . $page . '">' . $append . '</a>';
    
    $global_breadcrumbs .= $append . " &raquo; ";
}

function breadcrumbEnd()
{
    global $global_breadcrumbs;
    
    # flush breadcrumb
    echo substr($global_breadcrumbs, 0, strlen($global_breadcrumbs) - 9); // cut off last space and &raquo;
    
    theme_breadcrumb_close();
    
    utilBar();
}

function isadmin()
{
    global $config_admin_password;
    
    return (isset($_COOKIE['session']) && sha1($config_admin_password . $_SERVER['REMOTE_ADDR']) == $_COOKIE['session']);
}

function isadminpage()
{
    return isadmin() && isset($_REQUEST['do']) && $_REQUEST['do'] == 'admin';
}

function utilBar()
{
    theme_utilbar_header();
?>
    <span style="float: left;">Last updated: <span id="last_update"><?php echo date('h:i A'); ?></span></span><?php if(!isadminpage()) {?><span style="float: right; font-weight: bold;"><?php $text = isadmin() ? '<a href="index.php?do=admin">Admin Panel' : '<img id="loading" src="img/loading.gif" style="display: none;" />'; echo $text; ?></a></span>
<?php
    }
    else
    {
        ?>
<span style="float: right; font-weight: bold;"><a href="admin/logout.php" onclick="return confirm('Are you sure you want to log out?  You will have to log back in to add/edit scores.');">Log out</a></span>
        <?php
    }
    
    theme_utilbar_end();
}
?>