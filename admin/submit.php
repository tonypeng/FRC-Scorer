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
    $fhe_b = '';

    $queueing = fopen('../cache/cache_current_queue.html', 'w+');

    $new = false;

    $match = 0;

    $redalliance = 0;
    $red1 = '';
    $red2 = '';
    $red3 = '';
    $redscore = 0;

    $bluealliance = 0;
    $blue1 = '';
    $blue2 = '';
    $blue3 = '';
    $bluescore = 0;

    $gray = false;
    $dontskip = false;

    $parse_state = -1;

    $unscored_matches = 0; // counter for unscored matches -- second unscored match is queueing

    $timestamp = '';

    $last_matchtype = '';

    /*************************/
    // For eliminations
    /*************************/
    // array_root( array_qf_x( red_alliance, blue_alliance, array_matches( array_qf_x_y( red_score, blue_score ) ) ) );
    $quarterfinals = array(
        array( 0, 0, array( array(-1, -1), array(-1, -1), array(-1, -1) ) ),
        array( 0, 0, array( array(-1, -1), array(-1, -1), array(-1, -1) ) ),
        array( 0, 0, array( array(-1, -1), array(-1, -1), array(-1, -1) ) ),
        array( 0, 0, array( array(-1, -1), array(-1, -1), array(-1, -1) ) )
    );

    // array_root( array_sf_x( red_alliance, blue_alliance, array_matches( array_sf_x_y( red_score, blue_score ) ) ) );
    $semifinals = array(
        array( 0, 0, array( array(-1, -1), array(-1, -1), array(-1, -1) ) ),
        array( 0, 0, array( array(-1, -1), array(-1, -1), array(-1, -1) ) )
    );

    // array_root( array_f_x( red_alliance, blue_alliance, array_matches( array_f_x_y( red_score, blue_score ) ) ) );
    $finals = array(
        array( 0, 0, array( array(-1, -1), array(-1, -1), array(-1, -1) ) )
    );

    $championAlliance = 0;

    // array_root( [num] => array( captain, team2, team3 ) );
    $alliances = array( array(0, 0, 0), array(0, 0, 0), array(0, 0, 0), array(0, 0, 0), array(0, 0, 0), array(0, 0, 0), array(0, 0, 0), array(0, 0, 0), array(0, 0, 0) );

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
            {
                writeline($fhe, '</table>');
            }
            else if($parse_state == 3)
            {
                writeElims($fhe_b, $quarterfinals, $semifinals, $finals, $championAlliance, $alliances);
            }

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

                    if(file_exists('../cache/cache_current_elim_bracket.html'))
                    {
                        rename('../cache/cache_current_elim_bracket.html', '../cache/cache_last_elim_bracket.html');
                    }

                    $fhe = fopen('../cache/cache_current_elim.html', 'w+');
                    $fhe_b = fopen('../cache/cache_current_elim_bracket.html', 'w+');

                    writeline($fhe, '<table border="1" style="width: 100%;">');
                    writeline($fhe, '<tr><td><span style="font-weight: bold;">Match</span></td><td><span style="color:red; font-weight: bold;">Red Alliance</span></td><td><span style="color:red; font-weight: bold;">Red Captain</span></td><td><span style="color:red; font-weight: bold;">Red 2</span></td><td><span style="color:red; font-weight: bold;">Red 3</span></td><td><span style="color:blue; font-weight: bold;">Blue Alliance</span></td><td><span style="color:blue; font-weight: bold;">Blue Captain</span></td><td><span style="color:blue; font-weight: bold;">Blue 2</span></td><td><span style="color:blue; font-weight: bold;">Blue 3</span></td><td><span style="font-weight: bold;">Result</span></td><td><span style="font-weight: bold;">Recorded</span></td><td><span style="font-weight: bold;">Match</span></td></tr>');

                    $new = false;
                    $gray = false;

                    $parse_state = 2;
                }
                else if($parts[0] === "<Champions>")
                {
                    $parse_state = 3;
                }
                else if($parts[0] === "<Ranks>")
                {
                    if(file_exists('../cache/cache_current_rankings.html'))
                    {
                        rename('../cache/cache_current_rankings.html', '../cache/cache_last_rankings.html');
                    }

                    $fh2 = fopen('../cache/cache_current_rankings.html', 'w+');

                    writeline($fh2, '<table border="1" style="width: 100%;">');
                    writeline($fh2, '<tr><td><span style="font-weight: bold;">Rank #</span></td><td>Team Number</td><td>Qualification</td><td>Assist</td><td>Auto</td><td>Truss</td><td>Teleop</td><td>Win-Loss-Tie Record</td><td>Team Name</td><td><span style="font-weight: bold;">Rank #</span></td></tr>');

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
                    writeline($fh, '<tr><td><span style="font-weight: bold;">Match #</span></td><td><span style="color:red; font-weight: bold;">Red 1</span></td><td><span style="color:red; font-weight: bold;">Red 2</span></td><td><span style="color:red; font-weight: bold;">Red 3</span></td><td><span style="color:blue; font-weight: bold;">Blue 1</span></td><td><span style="color:blue; font-weight: bold;">Blue 2</span></td><td><span style="color:blue; font-weight: bold;">Blue 3</span></td><td><span style="font-weight: bold;">Result</span></td><td><span style="font-weight: bold;">Recorded</span></td><td><span style="font-weight: bold;">Match #</span></td></tr>');

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
                    $assist = ltrim($score_parts[1], '0');
                    $auto = ltrim($score_parts[2], '0');
                    $truss = ltrim($score_parts[3], '0');
                    $teleop = ltrim($score_parts[4], '0');

                    if($qual == '')
                        $qual = '0';
                    if($assist == '')
                        $assist = '0';
                    if($auto == '')
                        $auto = '0';
                    if($truss == '')
                        $truss = '0';
                    if($teleop == '')
                        $teleop = '0';
                }
                else
                {
                    $qual = '-';
                    $assist = '-';
                    $auto = '-';
                    $truss = '-';
                    $teleop = '-';

                }

                $attr = $gray ? ' style="background-color: #EEEEEE;"' : '';

                writeline($fh2, '<tr' . $attr . '>');
                writeline($fh2, "<td style=\"width: 11.1%; height: 20px;\">$rank</td><td style=\"width: 11.1%; height: 20px;\">$team</td><td style=\"width: 11.1%; height: 20px;\">$qual</td><td style=\"width: 11.1%; height: 20px;\">$assist</td><td style=\"width: 11.1%; height: 20px;\">$auto</td><td style=\"width: 11.1%; height: 20px;\">$truss</td><td style=\"width: 11.1%; height: 20px;\">$teleop</td><td style=\"width: 11.1%; height: 20px;\">$record</td><td style=\"width: 11.1%; height: 20px;\">$name</td><td style=\"width: 11.1%; height: 20px;\">$rank</td>");
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

                    writeline($fh, '<td style="width: 10%; height: 20px;">'.$match.'</td><td style="width: 10%; height: 20px; border: 1px solid #CC0000; "><strong>'.$red1.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #CC0000; "><strong>'.$red2.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #CC0000; "><strong>'.$red3.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #4F94CD; "><strong>'.$blue1.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #4F94CD; "><strong>'.$blue2.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #4F94CD; "><strong>'.$blue3.'</strong></td><td style="width: 10%; height: 20px;">'.$res.'</td><td style="width: 10%; height: 20px;">'.$timestamp.'</td><td style="width: 10%; height: 20px;">'.$match.'</td>');
                    writeline($fh, '</tr>');

                    $gray = !$gray;

                    $timestamp = '';
                }
            break;
            case 2:
                $new = !$new;

                $match = strlen($parts[0]) > 0 ? $parts[0] : $match;

                $alliance = $parts[2];

                if(!is_numeric($alliance)) $alliance = 0;

                $alliance = $alliance + 0;

                $team1 = $parts[4];
                $team2 = $parts[5];
                $team3 = $parts[6];

                if($alliances[$alliance][0] === 0)
                {
                    $alliances[$alliance][0] = $team1 + 0;
                    $alliances[$alliance][1] = $team2 + 0;
                    $alliances[$alliance][2] = $team3 + 0;
                }

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
                    $bluealliance = $alliance;
                    $blue1 = $team1;
                    $blue2 = $team2;
                    $blue3 = $team3;
                    $bluescore = count($parts) < 7 ? "-" : trim($parts[9]);
                    $bluescore = is_numeric($bluescore) ? $bluescore : "-";
                }
                else
                {
                    $redalliance = $alliance;
                    $red1 = $team1;
                    $red2 = $team2;
                    $red3 = $team3;
                    $redscore = count($parts) < 7 ? "-" : trim($parts[9]);
                    $redscore = is_numeric($redscore) ? $redscore : "-";
                }

                $match_type = substr($match, 0, 1);

                $team_num = $team == ' R' ? 0 : 1;

                if($match_type == 'Q')
                {
                    $match_num = substr($match, 2, 1) + 0;

                    $quarterfinals[$match_num - 1][$team_num] = $alliance + 0;
                    $quarterfinals[$match_num - 1][2][$round - 1][$team_num] = count($parts) < 7 || $parts[9] == '' ? -1 : trim($parts[9]) + 0;
                }
                else if($match_type == 'S')
                {
                    $match_num = substr($match, 2, 1) + 0;

                    $semifinals[$match_num - 1][$team_num] = $alliance + 0;
                    $semifinals[$match_num - 1][2][$round - 1][$team_num] = count($parts) < 7 || $parts[9] == '' ? -1 : trim($parts[9]) + 0;
                }
                else if($match_type == 'F')
                {
                    $finals[0][$team_num] = $alliance + 0;
                    $finals[0][2][$round - 1][$team_num] = count($parts) < 7 || $parts[9] == '' ? -1 : trim($parts[9]) + 0;
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

                    if($match_type != $last_matchtype && $last_matchtype != '')
                    {
                        writeline($fhe, '<tr style="height: 20px;"></tr>');
                    }

                    $attr = $gray ? ' style="background-color: #EEEEEE;"' : '';

                    writeline($fhe, '<tr' . $attr . '>');
                    writeline($fhe, '<td style="width: 10%; height: 20px;">'.$match.' - '.$round.'</td><td style="width: 10%; height: 20px; border: 1px solid #CC0000; "><strong>'.$redalliance.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #CC0000; "><strong>'.$red1.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #CC0000; "><strong>'.$red2.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #CC0000; "><strong>'.$red3.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #4F94CD; "><strong>'.$bluealliance.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #4F94CD; "><strong>'.$blue1.'</strong></td><td style="width: 10%; height: 20px; border: 1px solid #4F94CD; "><strong>'.$blue2.'</strong></td><td style="width: 10%; height: 20px;  border: 1px solid #4F94CD; "><strong>'.$blue3.'</strong></td><td style="width: 10%; height: 20px;">'.$res.'</td><td style="width: 10%; height: 20px;">'.$timestamp.'</td><td style="width: 10%; height: 20px;">'.$match.'</td>');
                    writeline($fhe, '</tr>');

                    $gray = !$gray;

                    $timestamp = '';

                    $last_matchtype = $match_type;
                }
                else
                    $dontskip = true;
            break;
            case 3:
                if($parts[0] === 'F')
                {
                    $championAlliance = $parts[1] + 0;
                }
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

function writeElims($handle, $qf_arr, $sf_arr, $f_arr, $championAlliance, $alliances)
{
    /* todo: clean up code */

    /* xf_game_alliance*/
    $qf_1_1 = $qf_arr[0][0];
    $qf_1_2 = $qf_arr[0][1];
    $qf_2_1 = $qf_arr[1][0];
    $qf_2_2 = $qf_arr[1][1];
    $qf_3_1 = $qf_arr[2][0];
    $qf_3_2 = $qf_arr[2][1];
    $qf_4_1 = $qf_arr[3][0];
    $qf_4_2 = $qf_arr[3][1];

    $sf_1_1 = $sf_arr[0][0];
    $sf_1_2 = $sf_arr[0][1];
    $sf_2_1 = $sf_arr[1][0];
    $sf_2_2 = $sf_arr[1][1];

    $f_1_1 = $f_arr[0][0];
    $f_1_2 = $f_arr[0][1];

    $qf_1_1_teams = $qf_1_1 <= 0 || $alliances[$qf_1_1][0] === 0 ? '' : '(' . $alliances[$qf_1_1][0] . ', ' . $alliances[$qf_1_1][1] . ',  ' . $alliances[$qf_1_1][2] . ')';
    $qf_1_2_teams = $qf_1_2 <= 0 || $alliances[$qf_1_2][0] === 0 ? '' : '(' . $alliances[$qf_1_2][0] . ', ' . $alliances[$qf_1_2][1] . ',  ' . $alliances[$qf_1_2][2] . ')';
    $qf_2_1_teams = $qf_2_1 <= 0 || $alliances[$qf_2_1][0] === 0 ? '' : '(' . $alliances[$qf_2_1][0] . ', ' . $alliances[$qf_2_1][1] . ',  ' . $alliances[$qf_2_1][2] . ')';
    $qf_2_2_teams = $qf_2_2 <= 0 || $alliances[$qf_2_2][0] === 0 ? '' : '(' . $alliances[$qf_2_2][0] . ', ' . $alliances[$qf_2_2][1] . ',  ' . $alliances[$qf_2_2][2] . ')';
    $qf_3_1_teams = $qf_3_1 <= 0 || $alliances[$qf_3_1][0] === 0 ? '' : '(' . $alliances[$qf_3_1][0] . ', ' . $alliances[$qf_3_1][1] . ',  ' . $alliances[$qf_3_1][2] . ')';
    $qf_3_2_teams = $qf_3_2 <= 0 || $alliances[$qf_3_2][0] === 0 ? '' : '(' . $alliances[$qf_3_2][0] . ', ' . $alliances[$qf_3_2][1] . ',  ' . $alliances[$qf_3_2][2] . ')';
    $qf_4_1_teams = $qf_4_1 <= 0 || $alliances[$qf_4_1][0] === 0 ? '' : '(' . $alliances[$qf_4_1][0] . ', ' . $alliances[$qf_4_1][1] . ',  ' . $alliances[$qf_4_1][2] . ')';
    $qf_4_2_teams = $qf_4_2 <= 0 || $alliances[$qf_4_2][0] === 0 ? '' : '(' . $alliances[$qf_4_2][0] . ', ' . $alliances[$qf_4_2][1] . ',  ' . $alliances[$qf_4_2][2] . ')';

    $sf_1_1_teams = $sf_1_1 <= 0 || $alliances[$sf_1_1][0] === 0 ? '' : '(' . $alliances[$sf_1_1][0] . ', ' . $alliances[$sf_1_1][1] . ',  ' . $alliances[$sf_1_1][2] . ')';
    $sf_1_2_teams = $sf_1_2 <= 0 || $alliances[$sf_1_2][0] === 0 ? '' : '(' . $alliances[$sf_1_2][0] . ', ' . $alliances[$sf_1_2][1] . ',  ' . $alliances[$sf_1_2][2] . ')';
    $sf_2_1_teams = $sf_2_1 <= 0 || $alliances[$sf_2_1][0] === 0 ? '' : '(' . $alliances[$sf_2_1][0] . ', ' . $alliances[$sf_2_1][1] . ',  ' . $alliances[$sf_2_1][2] . ')';
    $sf_2_2_teams = $sf_2_2 <= 0 || $alliances[$sf_2_2][0] === 0 ? '' : '(' . $alliances[$sf_2_2][0] . ', ' . $alliances[$sf_2_2][1] . ',  ' . $alliances[$sf_2_2][2] . ')';

    $f_1_1_teams = $f_1_1 <= 0 || $alliances[$f_1_1][0] === 0 ? '' : '(' . $alliances[$f_1_1][0] . ', ' . $alliances[$f_1_1][1] . ',  ' . $alliances[$f_1_1][2] . ')';
    $f_1_2_teams = $f_1_2 <= 0 || $alliances[$f_1_2][0] === 0 ? '' : '(' . $alliances[$f_1_2][0] . ', ' . $alliances[$f_1_2][1] . ',  ' . $alliances[$f_1_2][2] . ')';

    $qf_1_1 = $qf_1_1 <= 0 || $alliances[$qf_1_1][0] === 0 ? '' : $qf_1_1;
    $qf_1_2 = $qf_1_2 <= 0 || $alliances[$qf_1_2][0] === 0 ? '' : $qf_1_2;
    $qf_2_1 = $qf_2_1 <= 0 || $alliances[$qf_2_1][0] === 0 ? '' : $qf_2_1;
    $qf_2_2 = $qf_2_2 <= 0 || $alliances[$qf_2_2][0] === 0 ? '' : $qf_2_2;
    $qf_3_1 = $qf_3_1 <= 0 || $alliances[$qf_3_1][0] === 0 ? '' : $qf_3_1;
    $qf_3_2 = $qf_3_2 <= 0 || $alliances[$qf_3_2][0] === 0 ? '' : $qf_3_2;
    $qf_4_1 = $qf_4_1 <= 0 || $alliances[$qf_4_1][0] === 0 ? '' : $qf_4_1;
    $qf_4_2 = $qf_4_2 <= 0 || $alliances[$qf_4_2][0] === 0 ? '' : $qf_4_2;

    $sf_1_1 = $sf_1_1 <= 0 || $alliances[$sf_1_1][0] === 0 ? '' : $sf_1_1;
    $sf_1_2 = $sf_1_2 <= 0 || $alliances[$sf_1_2][0] === 0 ? '' : $sf_1_2;
    $sf_2_1 = $sf_2_1 <= 0 || $alliances[$sf_2_1][0] === 0 ? '' : $sf_2_1;
    $sf_2_2 = $sf_2_2 <= 0 || $alliances[$sf_2_2][0] === 0 ? '' : $sf_2_2;

    $f_1_1 = $f_1_1 <= 0 || $alliances[$f_1_1][0] === 0 ? '' : $f_1_1;
    $f_1_2 = $f_1_2 <= 0 || $alliances[$f_1_2][0] === 0 ? '' : $f_1_2;

    $championTop = $championAlliance <= 0 || $alliances[$championAlliance] === 0 ? '' : 'Champion:';
    $championBottom = $championAlliance <= 0 || $alliances[$championAlliance][0] === 0 ? '' : $championAlliance . '(' . $alliances[$championAlliance][0] . ', ' . $alliances[$championAlliance][1] . ',  ' . $alliances[$championAlliance][2] . ')';

    writeline($handle, '<table style="width: 100%; table-layout: fixed; border-collapse: collapse; border-spacing: 0;">');
    writeline($handle, '<tr style="width: 100%;"><td class="cell border-bottom">' . $qf_1_1 . $qf_1_1_teams . '</td><td class="cell"></td><td class="cell"></td><td class="cell"></td><td class="cell"></td><td class="cell"></td><td class="cell border-bottom">' . $qf_2_1 . $qf_2_1_teams . '</td></tr>');
    writeline($handle, '<tr style="width: 100%;"><td class="cell"></td><td class="cell border-left border-bottom">' . $sf_1_1 . $sf_1_1_teams . '</td><td class="cell"></td><td class="cell"></td><td class="cell"></td><td class="cell border-right border-bottom">' . $sf_2_1 . $sf_2_1_teams . '</td><td class="cell"></td></tr>');
    writeline($handle, '<tr style="width: 100%;"><td class="cell border-bottom"></td><td class="cell border-left"></td><td class="cell border-left"></td><td class="cell"></td><td class="cell border-right"><td class="cell border-right"></td><td class="cell border-bottom"></td></tr>');
    writeline($handle, '<tr style="width: 100%;"><td class="cell">' . $qf_1_2 . $qf_1_2_teams . '</td><td class="cell"></td><td class="cell border-left"></td><td class="cell"></td><td class="cell border-right"></td><td class="cell"></td><td class="cell">' . $qf_2_2 . $qf_2_2_teams . '</td></tr>');

    writeline($handle, '<tr style="width: 100%;"><td class="cell"></td><td class="cell"></td><td class="cell border-left border-bottom">' . $f_1_1 . $f_1_1_teams . '</td><td class="cell border-left border-top border-right" style="font-weight: bold;">' . $championTop . '</td><td class="cell border-right border-bottom">' . $f_1_2 . $f_1_2_teams . '</td><td class="cell"></td><td class="cell"></td></tr>');
    writeline($handle, '<tr style="width: 100%;"><td class="cell"></td><td class="cell"></td><td class="cell border-left border-top"></td><td class="cell border-left border-bottom border-right">' . $championBottom . '</td><td class="cell border-right border-top"></td><td class="cell"></td><td class="cell"></td></tr>');

    writeline($handle, '<tr style="width: 100%;"><td class="cell border-bottom">' . $qf_4_1 . $qf_4_1_teams . '</td><td class="cell"></td><td class="cell border-left"></td><td class="cell"></td><td class="cell border-right"></td><td class="cell"></td><td class="cell border-bottom">' . $qf_3_1 . $qf_3_1_teams . '</td></tr>');
    writeline($handle, '<tr style="width: 100%;"><td class="cell"></td><td class="cell border-left border-bottom">' . $sf_1_2 . $sf_1_2_teams . '</td><td class="cell border-left"></td><td class="cell"></td><td class="cell border-right"></td><td class="cell border-right border-bottom">' . $sf_2_2 . $sf_2_2_teams . '</td><td class="cell"></td></tr>');
    writeline($handle, '<tr style="width: 100%;"><td class="cell border-bottom"></td><td class="cell border-left"></td><td class="cell"></td><td class="cell"></td><td class="cell"><td class="cell border-right"></td><td class="cell border-bottom"></td></tr>');
    writeline($handle, '<tr style="width: 100%;"><td class="cell">' . $qf_4_2 . $qf_4_2_teams . '</td><td class="cell"></td><td class="cell"></td><td class="cell"></td><td class="cell"></td><td class="cell"></td><td class="cell">' . $qf_3_2 . $qf_3_2_teams . '</td></tr>');
    writeline($handle, '</table>');
}
?>
