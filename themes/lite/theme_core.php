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

function theme_header()
{
?>
<div id="container">
<?php
}

function theme_content_begin()
{
?>
<div class="bordered padded2">
<?php
}

function theme_content_end()
{
?>
</div>
<?php
}

function theme_spacer($pixels)
{
?>
<br />
<?php
}

function theme_end()
{
}

function theme_navtag_header()
{
?>
<div style="height: 159px; display: table; width: 100%;" class="bordered">
<a href="index.php"><img src="img/calgames.png" style="position: absolute; background-color: white;" /></a>
<div style="top: 50%; display: table-cell; vertical-align: middle;">
<div id="navbar" style="top: -50%;">
<?php
}

function theme_navtag_close()
{
?>
</div>
</div>
</div>
<?php
}

function theme_breadcrumb_header()
{
?>
<div id="breadcrumb">
<?php
}

function theme_breadcrumb_close()
{
    echo "\n";
?>
</div>
<?php
}

function theme_utilbar_header()
{
?>
<div id="utilbar">
<?php
}

function theme_utilbar_end()
{
?>
</div>
<?php
}
?>
