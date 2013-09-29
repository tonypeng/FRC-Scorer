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

//$start = microtime();
//$startarray = explode(' ', $start);
//$start = $startarray[1] + $startarray[0];

define('FRC Scoring Page', 1);

require_once('defines.php');
require_once('core/globals.php');

# start output buffering
ob_start();

loadTheme();

/* Theme header; this function will load the html header section as well as set-up the page styling. */
themeHeader();

if(isset($_REQUEST['do']))
{
    $do = $_REQUEST['do'];
}
else
{
    $do = 'home';
}

$dofilemap = array(
		'home' => 'home.php',
        'matches' => 'matches.php',
        'rankings' => 'rankings.php',
        'admin' => 'admin/index.php'
    );
    
if(isset($_REQUEST['loggedout']))
{
?>
    <div style="border: 1px solid #000; background-color: lightgreen;">You have logged out.  Click <a href="index.php?do=admin">here</a> to sign back in.</div>
<?php
    themeSpacer(5);
}

navbarHeader();

navbarAdd('Matches', 'index.php?do=matches', $do != 'matches');
navbarAdd('Rankings', 'index.php?do=rankings', $do != 'rankings');

navbarEnd();

themeSpacer();    

breadcrumbHeader();

breadcrumbAppend($config_site_name, 'index.php');

require_once($dofilemap[$do]);

/* Closes the <body> and <html> tags. */
themeEnd();

ob_flush(); # flush everything in the buffer
?>