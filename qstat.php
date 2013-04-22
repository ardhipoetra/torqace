<?php /*
 
 Torqace : Torque Interface

 Copyright (C) 2013, Ardhi Putra Pratama

 Torqace is based on the PBSWeb-Lite code written by Yuan-Chung Cheng.
 PBSWeb-Lite is based on the PBSWeb code written by Paul Lu et al.
 See History for more details.

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
include_once ("config.php");
include_once ("pbsutils.php");
include_once 'constant.php';

session_name($PBSWEBNAME);
session_set_cookie_params($PBSWEBEXPTIME, $PBSWEBPATH);
session_start();
setcookie(session_name(), session_id(), time() + $PBSWEBEXPTIME, $PBSWEBPATH);

include_once ("auth.php");

auth_page();

$username = $_SESSION['username'];

if (!$_POST['host']) {
	$host = $PBSWEBDEFAULTHOST;
} else {
	$host = $_POST['host'];
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
		<title><?php echo $TITLE_QSTAT; ?> </title>
		<script language="JavaScript">
			<!--setTimeout("window.document.forms[0].submit()", 60000);
			//-->
		</script>
	</head>
	<body bgcolor="white">
		<h1><img src="<?php echo $PBSWEBHEADERLOGO; ?>" border="0" height="102" width="92" alt="PBS Logo"><?php echo $TITLE_QSTAT; ?> </h1>

		<?php /* Put standard navigation menu */
	include ("navbar.php");
		?>

		<hr>
		<form method="POST" action="qstat.php">
			<table>
				<tr>
					<th>Host:</th><td>
					<select name="host" onChange="window.document.forms[0].submit()">
						<?php // get the list of hosts
						foreach ($PBSWEBHOSTLIST as $hostname => $hostdata) {
							if ($hostname == $host) {
								echo "<OPTION VALUE=\x22$hostname\x22 SELECTED>$hostname";
							} else {
								echo "<OPTION VALUE=\x22$hostname\x22>$hostname";
							}
						}
						?>
					</select></td>
					<td>
					<INPUT TYPE="submit" VALUE="Reload">
					</td>
				</tr>
			</table>
		</form>
		<?php
			$qstat = `ssh -l $username $host '$qstat_cmd; exit' 2>&1`;
			$qstatB = `ssh -l $username $host '$qstat_cmd -B; exit' 2>&1`;
			$qstatQ = `ssh -l $username $host '$qstat_cmd -Q; exit' 2>&1`;
		?>
		<h2>Current/Running Job Queue</h2>
		<!-- 20010510 Chris added links to job id and for deletion. -->
		<table width=90% border=1>
			<?php 
			$stringarray = explode("\n", $qstat);
			if (sizeof($stringarray) - 1 <= 0) print("Queue is empty \n");
			else {
			?>
				<tr>
				<th>Job ID</th>
				<th>Job name</th>
				<th>Run as</th>
				<th>Running time</th>
				<th>Status</th>
				<th>In queue</th>
				<!--<th>Action</th> -->
				</tr>
			<?php
			}
			for ($i = 2; $i < sizeof($stringarray) - 1; $i = $i + 1) {
				print("<tr>");
				if ($i == 0) {
					$stringarray[$i] = str_replace("Job id", "Job-id", $stringarray[$i]);
					$stringarray[$i] = str_replace("Time Use", "Time-Use", $stringarray[$i]);
				}
				$the_new_line = ereg_replace('(  *)', " ", $stringarray[$i]);
				$line_array = explode(" ", $the_new_line);
				$jobid = (int)$line_array[0];
				for ($j = 0; $j < sizeof($line_array)-1; $j = $j + 1) {
					print('<td align="center">');
					if ($j == 0)
						print("<a href=\"jobstatus.php?jobid=$jobid&host=$host\">$line_array[$j]</a></td>");
					elseif ($j == 4) {
						if ($line_array[$j] == "C") print("Completed</td>");
						elseif ($line_array[$j] == "R") print("Running</td>");
						elseif ($line_array[$j] == "Q") print("Queued</td>");
					}
					elseif ($j==6 && $username == $line_array[2]) {
						//print("<a href=\"qdel.php?jobid=$jobid&host=$host\">Delete</a></td>");
					}
					else
						print("$line_array[$j]</td>");
				}
				print("</tr>");
			}
			?>
		</table>

		<h2>Queue Status</h2>
		<table border="1">
			<tr>
				<th>Queue name</th>
				<th>Max Job that may be run concurrently</th>
				<th>Job in Queue</th>
				<th>Status (Enable/Disable)</th>
				<th>Status (Started/Stopped)</th>
				<th>Queued job</th>
				<th>Running Job</th>
				<th>Held Job</th>
				<th>Waiting for execution Job</th>
				<th>Moving Job</th>
				<th>Exiting Job</th>
				<th>Queue Type (Execution/Routing)</th>
			</tr>
			<tr>
<?php 
			$qtatQ_arr = parseQstat_Q($qstatQ);
			foreach ($qtatQ_arr as $keyQ => $valueQ) {
				if (strcmp($keyQ, "isenable") == 0) 
					$valueQ = (strcmp($valueQ, "yes") == 0) ? "Enable" : "Disable" ;
				elseif (strcmp($keyQ, "startedstat") == 0) 
					$valueQ = (strcmp($valueQ, "yes") == 0) ? "Started" : "Stopped" ;
				elseif (strcmp($keyQ, "type") == 0) 
					$valueQ = (strcmp($valueQ, "E") == 0) ? "Execution" : "Routing" ;
				print("<td align=\"center\">" . $valueQ . "</td>");
			}
?>		</tr></table>
		<h2>Host Status</h2>
		<table border="1">
			<tr>
				<th>Host name</th>
				<th>Max Job that can run concurrently</th>
				<th>Total Job</th>
				<th>Queued job</th>
				<th>Running Job</th>
				<th>Held Job</th>
				<th>Waiting for execution Job</th>
				<th>Moving Job</th>
				<th>Exiting Job</th>
				<th>Host Status</th>
			</tr>
			<tr>
<?php 
		$qtatB_arr = parseQstat_B($qstatB);
			foreach ($qtatB_arr as $keyB => $valueB) {
				print("<td align=\"center\">" . $valueB . "</td>");
			}
?>		</tr></table>
		<hr>				

<?php
	include ("footer.php");
?>
 
		<!-- $Id: qstat.php,v 1.13 2004/03/18 21:04:19 platin Exp $ -->
	</body>
</html>
