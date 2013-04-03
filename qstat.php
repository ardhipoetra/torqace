<?php
/*

PBSWeb-Lite: A Simple Web-based Interface to PBS

Copyright (C) 2003, 2004 Yuan-Chung Cheng

PBSWeb-Lite is based on the PBSWeb code written by Paul Lu et al.
See History for more detailes.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

*/
?>
<?php

include_once("config.php");

session_name($PBSWEBNAME);
session_set_cookie_params($PBSWEBEXPTIME,$PBSWEBPATH);
session_start();
setcookie(session_name(),session_id(), time()+$PBSWEBEXPTIME, $PBSWEBPATH);

include_once("auth.php");

auth_page();

$username=$_SESSION['username'];

if (!$_POST['host']) {
  $host = $PBSWEBDEFAULTHOST;
} else {
  $host=$_POST['host'];
}

if (!isset($PBSWEBHOSTLIST[$host]["qstat"]) || $PBSWEBHOSTLIST[$host]["qstat"] == "") {
  error_page("Failed retrieving qstat command");
  exit();
} else {
  $qstat_cmd = $PBSWEBHOSTLIST[$host]["qstat"];
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<META HTTP-EQUIV="no-cache">
  <META HTTP-EQUIV="Pragma" CONTENT="no-cache"> 
  <title>PBSWeb-Lite Queue Status</title>
<script language="JavaScript"><!--
setTimeout("window.document.forms[0].submit()",60000);
//-->
</script>
</head>
<body bgcolor="white">
  <h1>
<img src="img/littlepbsguy.jpg" border="0" height="102" width="92" alt="PBS Logo">PBSWeb-Lite Queue Status
  </h1>

<?php
  /* Put standard navigation menu */
include("navbar.php");
?>

<hr>
<form method="POST" action="qstat.php">
    <table><tr><th>Host:</th><td><select name="host" onChange="window.document.forms[0].submit()">
<?php 
    // get the list of hosts
    foreach ($PBSWEBHOSTLIST as $hostname => $hostdata) {
    if ($hostname==$host) {
      echo "<OPTION VALUE=\x22$hostname\x22 SELECTED>$hostname";
    } else {
      echo "<OPTION VALUE=\x22$hostname\x22>$hostname";
    }
  }
?>

</select></td><td><INPUT TYPE="submit" VALUE="Reload"></td></tr></table>
</form>
<?php
    $qstat = `ssh -l $username $host '$qstat_cmd; exit' 2>&1`;
    $qstatB = `ssh -l $username $host '$qstat_cmd -B; exit' 2>&1`;
    $qstatQ = `ssh -l $username $host '$qstat_cmd -Q; exit' 2>&1`;
?>
    <h2>Current Job Queue</h2>
    <!-- 20010510 Chris added links to job id and for deletion. -->
    <table width=90% align="center" border=0>
    <?php 
    $stringarray = explode("\n", $qstat);
    for ($i = 0; $i < sizeof($stringarray) - 1; $i = $i + 1) {
	print("<tr>");
	if ($i == 0) {
		$stringarray[$i] = str_replace("Job id", "Job-id",
			$stringarray[$i]);
		$stringarray[$i] = str_replace("Time Use", "Time-Use",
			$stringarray[$i]);
	}
	$the_new_line = ereg_replace('(  *)', " ", $stringarray[$i]);
	$line_array = explode(" ", $the_new_line);
	$jobid = (int) $line_array[0];
	for ($j = 0; $j < sizeof($line_array); $j = $j + 1) {
		print('<td align="center">');
		if ($j == 0 && $i > 1)
			print("<a href=\"jobstatus.php?jobid=$jobid&host=$host\">
				$line_array[$j]</a></td>");
		else
			print("$line_array[$j]</td>");
	}
	if ($username == $line_array[2])
		print("<td><a href=\"qdel.php?jobid=$jobid&host=$host\">
		    Delete</a></td>");
	print("</tr>");
    }
    ?>
    </table>

    <h2>Queue Configuration</h2>
    <pre><?php echo $qstatQ; ?></pre>
    <h2>Server Status</h2>
    <pre><?php echo $qstatB; ?></pre>

<?php
  include("navbar.php");
?>
<hr>
<p>Send questions and comments to 
<?php
echo "<a href=\"mailto:" . $PBSWEBMAIL . "\">";
echo $PBSWEBMAIL . "</a>\n";
?>
You can find <a href='help.html'>help here </a>.</p>
<!-- $Id: qstat.php,v 1.13 2004/03/18 21:04:19 platin Exp $ -->
  </body>
</html>
