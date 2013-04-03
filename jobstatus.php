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

  if (!$_GET['host']) {
    $host = $PBSWEBDEFAULTHOST;
  } else {
    $host=$_GET['host'];
  }

  $jobid=$_GET['jobid'];

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
		<title>PBSWeb-Lite Job Status for Job <?php print($jobid); ?></title>
	</head>
<body bgcolor="white">
	<h1>
	<img src="img/littlepbsguy.jpg" border="0" height="102" width="92" alt="PBS Logo">Job Status for Job <?php print($jobid); ?>
	</h1>
	</hr>
<?php
  /* Put standard navigation menu */
  include("navbar.php");
?>
	<?php 
	  $qstat_job = `ssh -l $username $host "$qstat_cmd -f $jobid; exit" 2>&1`;
	  $qstat_job = ereg_replace( "Job_Name",
		"<b><font color='blue'>Job_Name</font></b>", $qstat_job );
	  $qstat_job = ereg_replace( "queue",
		"<b><font color='blue'>queue</font></b>", $qstat_job );
	  $qstat_job = ereg_replace( "exec_host",
		"<b><font color='blue'>exec_host</font></b>", $qstat_job );
	  $qstat_job = ereg_replace( "Resource_List.nodes",
		"<b><font color='blue'>Resource_List.nodes</font></b>",
		$qstat_job );
	  print("<pre>$qstat_job</pre>");
?>
<p>
<a href="qstat.php">Back to Queue Status</a>
</p>

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
<!-- $Id: jobstatus.php,v 1.12 2004/03/18 21:04:19 platin Exp $ -->
</body>
</html>
