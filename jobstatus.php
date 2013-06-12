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

session_name($PBSWEBNAME);
session_set_cookie_params($PBSWEBEXPTIME, $PBSWEBPATH);
session_start();
setcookie(session_name(), session_id(), time() + $PBSWEBEXPTIME, $PBSWEBPATH);

include_once ("auth.php");

auth_page();

$username = $_SESSION['username'];

if (!$_GET['host']) {
	$host = $PBSWEBDEFAULTHOST;
} else {
	$host = $_GET['host'];
}

$jobid = $_GET['jobid'];

$isArr = $_GET['arr'];

if (isset($isArr)) {
	$jobid = $jobid."[]";
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
		<title>PBSWeb-Lite Job Status for Job <?php print($jobid); ?></title>
	</head>
<body bgcolor="white">
	<h1>
	<img src="<?php echo $PBSWEBHEADERLOGO; ?>" border="0" height="102" width="92" alt="PBS Logo">Job Status for Job <?php print($jobid); ?>
	</h1>
	</hr>
<?php /* Put standard navigation menu */
include ("navbar.php");
?>
	<?php 
		if ($isArr)
			$qstat_job = shell_exec("ssh -l $username $host \" $qstat_cmd -x -f -t $jobid ; exit\" 2>&1");
		else
			$qstat_job = shell_exec("ssh -l $username $host \" $qstat_cmd -x -f  $jobid ; exit\" 2>&1");
		//$qstat_job = ereg_replace("Job_Name", "<b><font color='blue'>Job_Name</font></b>", $qstat_job);
		//$qstat_job = ereg_replace("queue", "<b><font color='blue'>queue</font></b>", $qstat_job);
		//$qstat_job = ereg_replace("exec_host", "<b><font color='blue'>exec_host</font></b>", $qstat_job);
		//$qstat_job = ereg_replace("Resource_List.nodes", "<b><font color='blue'>Resource_List.nodes</font></b>", $qstat_job);		
		$xml = simplexml_load_string($qstat_job);
		if ($xml === false) {
    		die('Error parsing XML');   
		}
		//print_r($xml);
		$owner = strstr($xml->Job[0]->Job_Owner, '@', true);
		if (strcmp($owner, $username)) {
			error_page("You're not authorized to see this job detail");
			exit();
		} else {
			for ($i=0; $i < sizeof($xml->Job); $i++) { 
			?>
			<hr />
			<table width=90% border=1>
				<tr>
					<th>Job ID</th>
					<td><?php echo $xml->Job[$i]->Job_Id; ?></td>
				</tr>
				<tr>
					<th>Job Name</th>
					<td><?php echo $xml->Job[$i]->Job_Name; ?></td>
				</tr>
				<tr>
					<th>Job State</th>
					<td><?php 
						$state = $xml->Job[$i]->job_state;
						switch ($state) {
							case 'R':
								echo "Running";
								break;
							case 'E':
								echo "Exiting";
								break;
							case 'H':
								echo "Hold";
								break;
							case 'C':
								echo "Completed";
								break;
							case 'Q':
								echo "Queued";
								break;
							default:
								echo "-";
								break;
						}
						?>
					</td>
				</tr>
				<tr>
					<th>Queue</th>
					<td><?php echo $xml->Job[$i]->queue; ?></td>
				</tr>
				<tr>
				<?php 
					$arr = explode("+", $xml->Job[$i]->exec_host); 
					$size = (sizeof($arr)+1)/2;
					echo "<th rowspan=".$size.">"
				?>
					Execute at : </th>
					<?php
						$count = 2;
						foreach ($arr as $exechost) {
							if (!$count%2) {
								echo "<tr>";
							}
							echo "<td>";
							echo $exechost;
							echo "</td>";
							if ($count%2) {
								echo "</tr>";
							}
							$count++;
						}
					?>
				</tr>
				<tr>
					<th rowspan="2">Resource Used</th>
					<th>Time Passed</th>
					<td><?php echo $xml->Job[$i]->resources_used->walltime; ?></td>
				</tr>
				<tr>
					<th>Memory Used</th>
					<td><?php echo $xml->Job[$i]->resources_used->mem; ?></td>
				</tr>
				<tr>
					<th rowspan="2">Resource List</th>
					<th>Need Nodes</th>
					<td><?php echo $xml->Job[$i]->Resource_List->nodes; ?></td>
				</tr>
				<tr>
					<th>Walltime</th>
					<td><?php echo $xml->Job[$i]->Resource_List->walltime; ?></td>
				</tr>
				<tr>
					<th>Created Time</th>
					<td><?php echo date(DATE_RFC850,intval($xml->Job[$i]->ctime)); ?></td>
				</tr>
				<tr>
					<th>Queued Time</th>
					<td><?php echo date(DATE_RFC850,intval($xml->Job[$i]->etime)); ?></td>
				</tr>
				<tr>
					<th>Started Time</th>
					<td><?php echo 
						isset($xml->Job[$i]->start_time) ? 
							date(DATE_RFC850,intval($xml->Job[$i]->start_time)) : "-"; ?></td>
				</tr>
				<tr>
					<th>Priority</th>
					<td><?php echo $xml->Job[$i]->Priority; ?></td>
				</tr>
				<tr>
					<th>Submitted Script</th>
					<td><?php echo $xml->Job[$i]->submit_args; ?></td>
				</tr>
				<tr>
					<th>Remaining Walltime</th>
					<td><?php echo $xml->Job[$i]->Walltime->Remaining; ?></td>
				</tr>
				<tr>
					<th>Working Directory</th>
					<td><?php $wd = explode("/",$xml->Job[$i]->init_work_dir); echo $wd[4];?></td>
				</tr>
				<tr>
					<th>Mail to : </th>
					<td><?php echo $xml->Job[$i]->Mail_Users; ?></td>
				</tr>
				<!-- <tr>
					<th>Job ID</th>
					<td><?php ?></td>
			 	</tr>-->
			</table>
			<br />
			<br />
			<?php
			} //end for loop
		} //end if cond
?>
<p>
<a href="qstat.php">Back to Queue Status</a>
</p>

<?php
	include ("navbar.php");
?>
<hr>
<? include_once("footer.php"); ?>
<!-- $Id: jobstatus.php,v 1.12 2004/03/18 21:04:19 platin Exp $ -->
</body>
</html>
