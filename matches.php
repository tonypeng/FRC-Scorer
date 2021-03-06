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
    header("Location: index.php");
    exit();
}

breadcrumbAppend('Matches');

breadcrumbEnd();

themeSpacer();

themeContentBegin();

themeSpacer(5);
?>
<div style="padding: 4px; text-align: center;">
<?php
if(file_exists('cache/cache_current_elim.html') && file_exists('cache/cache_current_elim_bracket.html'))
{
	echo "<big>Elimination Matches</big><br />";
    echo '<div id="elim_matches_bracket">';
    include('cache/cache_current_elim_bracket.html');
    echo '</div>';
    themeSpacer(5);
	echo '<div id="elim_matches">';
	include('cache/cache_current_elim.html');
	echo '</div>';
	themeSpacer(5);
}

if(file_exists('cache/cache_current.html'))
{
    echo "<big>Qualification Matches</big><br />";
	echo '<div id="matches">';
	include('cache/cache_current.html');
	echo '</div>';
}
else
{
	echo "Match data is currently unavailable.<br />Check back soon!";
}
?>
</div>
<?php
themeSpacer(5);

themeContentEnd();

?>