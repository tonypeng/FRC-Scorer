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
    header("Location: ../index.php");
    exit();
}

# this should never happen...
if(!isadmin())
{
    header("Location: index.php?do=admin");
    exit();
}

breadcrumbAppend('Admin Panel', 'index.php?do=admin');
breadcrumbAppend('Update Scores');

breadcrumbEnd();

themeSpacer();

themeContentBegin();
?>
<h6 id="input_box">Enter Data</h6><br />
<span style="font-style: italic;">Go to excel, press "Copy to Web", and paste (CTRL + V) in the following text area.</span><br />
<form action="admin/submit.php" method="POST">
        Data:<br />
    <textarea name="data" style="width: 100%;" rows="15"></textarea><br />
    <input type="submit" style="width:  100%;" name="submit" value="Submit" />
</form>
<br />
<h6 id="input_box">Clear Data</h6><br />
<a href="admin/clear_cache.php" onclick="return confirm('Are you sure?');">Clear All Data</a>
<?php if(isset($_GET['timestamp'])):?>
<div id="notice" style="position:fixed; top: 0; left: 0; background-color: #adff2f; width: 100%; text-align: center; vertical-align: middle; display: inline-block;">Successfully updated scores! <?php echo date("h:i A", $_GET['timestamp']); ?><img style="position: absolute; top: 0; right: 0.5%; width: 14px; height: 14px; cursor: pointer;" src="img/red_cross.png" onclick="var div = document.getElementById('notice'); div.parentNode.removeChild(div);" /></div>
<?php
endif;

themeSpacer(5);

themeContentEnd();

themeSpacer(5);
?>