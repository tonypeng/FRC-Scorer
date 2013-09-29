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

require_once('../defines.php');
require_once('../core/globals.php');

if(!isset($_POST['submit']))
{
    header("Location: ../index.php?do=admin");
    exit();
}
else
{
	// this is where all the action happens
    if(!isadmin())
    {
        header("Location: ../index.php?do=admin");
        exit();
    }
    
    $start = microtime();
    $startarray = explode(' ', $start);
    $start = $startarray[1] + $startarray[0];

    $data = trim($_POST['data']);
    
    $fh = '';
	$fh2 = '';
	$fhe = '';
	
	$queueing = fopen('../cache/cache_current_queue.html', 'w+');
	
    $new = false;
    
    $match = 0;
    
    $red1 = '';
    $red2 = '';
    $red3 = '';
    $redscore = 0;
    
    $blue1 = '';
    $blue2 = '';
    $blue3 = '';
    $bluescore = 0;
    
    $gray = false;
	$dontskip = false;

	$parse_state = -1;
	
	$unscored_matches = 0; // counter for unscored matches -- second unscored match is queueing

	$timestamp = '';

    foreach(preg_split("/((\r?\n)|(\r\n?))/", $data) as $line)
    {
        $c = substr($line, 0, 1);
				
        if(!$dontskip && (strlen($line) <= 0 || (!is_numeric($c) && substr($line, 0, 1) !== '<' && substr($line, 0, 1) !== 'Q' && substr($line, 0, 1) !== 'S' && substr($line, 0, 1) !== 'F')))
            continue;
			
		$dontskip = false;
		
		if(substr($line, 0, 1) === '<')
		{
			if($parse_state == 0)
				writeline($fh2, '</table>');
			else if($parse_state == 1)
				writeline($fh, '</table>');
			else if($parse_state == 2)
				writeline($fhe, '</table>');
			
			$parse_state = -1;
		}
			
		$parts = explode("\t", $line);

		switch($parse_state)
		{
			case -1:
				if($parts[0] === "<Eliminations>")
				{
					if(file_exists('../cache/cache_current_elim.html'))
					{
						rename('../cache/cache_current_elim.html', '../cache/cache_last_elim.html');
					}
					
					$fhe = fopen('../cache/cache_current_elim.html', 'w+');
	
					writeline($fhe, '<table border="1" style="width: 100%;">');
					writeline($fhe, '<tr><td><span style="font-weight: bold;">Match</span></td><td><span style="color:blue; font-weight: bold;">Blue Captain</span></td><td><span style="color:blue; font-weight: bold;">Blue 2</span></td><td><span style="color:blue; font-weight: bold;">Blue 3</span></td><td><span style="color:red; font-weight: bold;">Red Captain</span></td><td><span style="color:red; font-weight: bold;">Red 2</span></td><td><span style="color:red; font-weight: bold;">Red 3</span></td><td><span style="font-weight: bold;">Result</span></td><td><span style="font-weight: bold;">Recorded</span></td><td><span style="font-weight: bold;">Match</span></td></tr>');
					
					$new = false;
					$gray = false;
					
					$parse_state = 2;
				}
				else if($parts[0] === "<Ranks>")
				{
					if(file_exists('../cache/cache_current_rankings.html'))
					{
						rename('../cache/cache_current_rankings.html', '../cache/cache_last_rankings.html');
					}
					
					$fh2 = fopen('../cache/cache_current_rankings.html', 'w+');
	
					writeline($fh2, '<table border="1" style="width: 100%;">');
					writeline($fh2, '<tr><td><span style="font-weight: bold;">Rank #</span></td><td>Team Number</td><td>Qualification</td><td>Autonomous</td><td>Climb</td><td>Teleop + Foul</td><td>Win-Loss-Tie Record</td><td>Team Name</td><td><span style="font-weight: bold;">Rank #</span></td></tr>');
					
					$parse_state = 0;
				}
				else if($parts[0] === "<Matches>")
				{
				    if(file_exists('../cache/cache_current.html'))
					{
						rename('../cache/cache_current.html', '../cache/cache_last.html');
					}
	
				    $fh = fopen('../cache/cache_current.html', 'w+');
    
					writeline($fh, '<table border="1" style="width: 100%;">');
					writeline($fh, '<tr><td><span style="font-weight: bold;">Match #</span></td><td><span style="color:blue; font-weight: bold;">Blue 1</span></td><td><span style="color:blue; font-weight: bold;">Blue 2</span></td><td><span style="color:blue; font-weight: bold;">Blue 3</span></td><td><span style="color:red; font-weight: bold;">Red 1</span></td><td><span style="color:red; font-weight: bold;">Red 2</span></td><td><span style="color:red; font-weight: bold;">Red 3</span></td><td><span style="font-weight: bold;">Result</span></td><td><span style="font-weight: bold;">Recorded</span></td><td><span style="font-weight: bold;">Match #</span></td></tr>');
					
					$parse_state = 1;
				}
			break;
			case 0:
				$rank = $parts[0];
				$team = $parts[1];
				$record = $parts[2];
				$name = $parts[4];
				
				$score = $parts[3];
				
				$score_parts = explode('-', $score);
				
				if(count($score_parts) >= 4)
				{
					$qual = ltrim($score_parts[0], '0');
					$auto = ltrim($score_parts[1], '0');
					$climb = ltrim($score_parts[2], '0');
					$teleop = ltrim($score_parts[3], '0');
					
					if($qual == '')
						$qual = '0';
					if($auto == '')
						$hybrid = '0';
					if($climb == '')
						$climb = '0';
					if($teleop == '')
						$teleop = '0';
				}
				else
				{
					$auto = '-';
					$climb = '-';
					$teleop = '-';
					$qual = '-';
				}

				$attr = $gray ? ' style="background-color: #EEEEEE;"' : '';
				
				writeline($fh2, '<tr' . $attr . '>');
				writeline($fh2, "<td style=\"width: 11.1%; height: 20px;\">$rank</td><td style=\"width: 11.1%; height: 20px;\">$team</td><td style=\"width: 11.1%; height: 20px;\">$qual</td><td style=\"width: 11.1%; height: 20px;\">$auto</td><td style=\"width: 11.1%; height: 20px;\">$climb</td><td style=\"width: 11.1%; height: 20px;\">$teleop</td><td style=\"width: 11.1%; height: 20px;\">$record</td><td style=\"width: 11.1%; height: 20px;\">$name</td><td style=\"width: 11.1%; height: 20px;\">$rank</td>");
				writeline($fh2, '</tr>');
				$gray = !$gray;
			break;
			case 1:
				$new = !$new;
					
				if($new)
				{
					$attr = $gray ? ' style="background-color: #EEEEEE;"' : '';
					
					writeline($fh, '<tr' . $attr . '>');
				}

				$match = $parts[0];
				
				$team1 = $parts[2];
				$team2 = $parts[3];
				$team3 = $parts[4];
				
				$team = $parts[1];
				
				$timestamp = strlen($parts[5]) > 0 ? $parts[5] : (strlen($timestamp) > 0 ? $timestamp : '-');
				
				if($team == 'B')
				{
					$blue1 = $team1;
					$blue2 = $team2;
					$blue3 = $team3;
					$bluescore = count($parts) < 7 ? "-" : trim($parts[6]);
					$bluescore = is_numeric($bluescore) ? $bluescore : "-";
				}
				else
				{
					$red1 = $team1;
					$red2 = $team2;
					$red3 = $team3;
					$redscore = count($parts) < 7 ? "-" : trim($parts[6]);
					$redscore = is_numeric($redscore) ? $redscore : "-";
				}
					
				if(!$new)
				{
					if($redscore == $bluescore)
					{
						if($redscore == "-")
						{
							$res = '-';
						}
						else
							$res = '<span style="color:#FF00FF; font-weight: bold;">T</span> ' . $bluescore . ' - ' . $redscore;
					}
					else
					{
						$res = ($bluescore > $redscore) ? '<span style="color:blue; font-weight: bold;">B</span> ' . $bluescore . ' - ' . $redscore : '<span style="color:red; font-weight: bold;">R</span> ' . $redscore . ' - ' . $bluescore;
					}
					
					trim($res);
					
					writeline($fh, '<td style="width: 10%; height: 20px;">'.$match.'</td><td style="width: 10%; height: 20px; border: 1px solid #4F94CD; "><strong>'.$blue1.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #4F94CD; "><strong>'.$blue2.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #4F94CD; "><strong>'.$blue3.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #CC0000; "><strong>'.$red1.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #CC0000; "><strong>'.$red2.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #CC0000; "><strong>'.$red3.'</strong></td><td style="width: 10%; height: 20px;">'.$res.'</td><td style="width: 10%; height: 20px;">'.$timestamp.'</td><td style="width: 10%; height: 20px;">'.$match.'</td>');
					writeline($fh, '</tr>');
					
					$gray = !$gray;
					
					$timestamp = '';
				}
			break;
			case 2:
				$new = !$new;
					
				if($new)
				{
					$attr = $gray ? ' style="background-color: #EEEEEE;"' : '';
					
					writeline($fhe, '<tr' . $attr . '>');
				}

				$match = strlen($parts[0]) > 0 ? $parts[0] : $match;
				
				$team1 = $parts[4];
				$team2 = $parts[5];
				$team3 = $parts[6];

				$round = $parts[1] != "" ? $parts[1] : $round;				

				if($team1 == '')
					$team1 = '-';
				if($team2 == '')
					$team2 = '-';
				if($team3 == '')
					$team3 = '-';
				
				$team = $parts[3];
				
				$timestamp = strlen($parts[8]) > 0 ? $parts[8] : (strlen($timestamp) > 0 ? $timestamp : '-');
				
				if($team == ' B')
				{
					$blue1 = $team1;
					$blue2 = $team2;
					$blue3 = $team3;
					$bluescore = count($parts) < 7 ? "-" : trim($parts[9]);
					$bluescore = is_numeric($bluescore) ? $bluescore : "-";
				}
				else
				{
					$red1 = $team1;
					$red2 = $team2;
					$red3 = $team3;
					$redscore = count($parts) < 7 ? "-" : trim($parts[9]);
					$redscore = is_numeric($redscore) ? $redscore : "-";
				}
					
				if(!$new)
				{
					if($redscore == $bluescore)
					{
						if($redscore == "-")
						{
							$res = '-';
							
							if(++$unscored_matches == 2)
							{
								writeline($queueing, "<span style=\"color: red;\">$red1, $red2, $red3</span> &amp; <span style=\"color: blue;\">$blue1, $blue2, $blue3</span>");
							}
						}
						else
							$res = '<span style="color:#FF00FF; font-weight: bold;">T</span> ' . $bluescore . ' - ' . $redscore;
					}
					else
					{
						$res = ($bluescore > $redscore) ? '<span style="color:blue; font-weight: bold;">B</span> ' . $bluescore . ' - ' . $redscore : '<span style="color:red; font-weight: bold;">R</span> ' . $redscore . ' - ' . $bluescore;
					}
					
					trim($res);
					
					writeline($fhe, '<td style="width: 10%; height: 20px;">'.$match.' - '.$round.'</td><td style="width: 10%; height: 20px; border: 1px solid #4F94CD; "><strong>'.$blue1.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #4F94CD; "><strong>'.$blue2.'</strong></td><td style="width: 10%; height: 20px;  border: 1px solid #4F94CD; "><strong>'.$blue3.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #CC0000; "><strong>'.$red1.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #CC0000; "><strong>'.$red2.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #CC0000; "><strong>'.$red3.'</strong></td><td style="width: 10%; height: 20px;">'.$res.'</td><td style="width: 10%; height: 20px;">'.$timestamp.'</td><td style="width: 10%; height: 20px;">'.$match.'</td>');
					writeline($fhe, '</tr>');
					
					$gray = !$gray;
					
					$timestamp = '';
				}
				else
					$dontskip = true;
			break;
		}
    }
    
    $end = microtime();
    $endarray = explode(' ', $end);
    $end = $endarray[1] + $endarray[0];
    
    $total = $end - $start;
    
    $total = $total / 1000;
    
    $total = round($total, 5);

    header("Location: ../index.php?do=admin&timestamp=".time()."#input_box");
}

function writeline($handle, $text)
{
    fwrite($handle, $text . "\n");
}
?>