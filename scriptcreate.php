<?php
/*

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
include_once ("constant.php");

session_name($PBSWEBNAME);
session_set_cookie_params($PBSWEBEXPTIME, $PBSWEBPATH);
session_start();
setcookie(session_name(), session_id(), time() + $PBSWEBEXPTIME, $PBSWEBPATH);

include_once ("auth.php");
include_once ("error.php");
include_once ("pbsutils.php");

auth_page();
$username = $_SESSION['username'];

// need to get host and directory from either GET or POST
if (!(isset($_REQUEST['host']) && isset($_REQUEST['directory']))) {
	error_page("Error: no host and directory specified.");
	exit();
} else {
	$host = $_REQUEST['host'];
	$directory = $_REQUEST['directory'];
	
	$checkgrep = "grep \"^".substr($directory,1)."\\s\" .torqace";
	$checkgrep = `ssh -l "$username" "$host" 'cd ~/$PBSWEBUSERDIR; $checkgrep ;exit' 2>&1`;
	$checkgrep = explode(' ',trim($checkgrep));
	$tipe = $checkgrep[1];
}

if (isset($PBSWEBHOSTLIST[$host]['max_nodes'])) {
	$host_max_nodes = $PBSWEBHOSTLIST[$host]['max_nodes'];
} else {
	$host_max_nodes = 8;
}
if (isset($PBSWEBHOSTLIST[$host]['max_ppn'])) {
	$host_max_ppn = $PBSWEBHOSTLIST[$host]['max_ppn'];
} else {
	$host_max_ppn = 2;
}

// prepare queue and maxtime list
$queues = array();
$maxtimes = array();
$i = 0;
foreach ($PBSWEBQUEUELIST[$host] as $que => $mtime) {
	$queues[$i] = $que;
	$maxtimes[$i] = $mtime;
	$i = $i + 1;
}

// setup the $jobinfo array, see pbsutils.php for format
$jobinfo = array();
$jobinfo['mail'] = $username . "@" . "$host";
$jobinfo['maxtime'] = "00:00:00";

// if there is a jobinfo array in $_SESSION[], use it as the default
if (isset($_SESSION['jobinfo'])) {
	$jobinfo = $_SESSION['jobinfo'];
	unset($_SESSION['jobinfo']);
}

// collect data from $_POST
pbsutils_collect($jobinfo, $_POST);

if (isset($_POST['overwrite'])) {
	$overwrite = $_POST['overwrite'];
}

// operation, three possibilities
// $operation == "": default behavior, to create a new jobscript
// $operation == "Load a Previous Job"
// $operation == "Load"
$operation = $_POST['operation'];
if ($operation != "Load a Previous Job" && $operation != "Load") {
	$operation == "New";
}

// prepare list of existing jobs in this directory
$ls_cmd = "ls -p ~/" . $PBSWEBUSERDIR . "/" . $directory . "/*.pbs";
$ls_result = `ssh -l $username $host '$ls_cmd; exit' 2>&1`;
$lsarray = explode("\n", $ls_result);
$pbsjobs = array();
$i = 0;
foreach ($lsarray as $line) {
	if (preg_match("/\s*([^\s]+)\.pbs$/", $line, $matches)) {
		$pbsjobs[$i] = basename($matches[1]);
		$i = $i + 1;
	}
}

if ($operation == "Load") {
	if (!$_POST['loadjob']) {
		error_page("Error: job name is required.");
		exit();
	}
	$jobname = $_POST['loadjob'];
	$remote_file = $username . "@" . $host . ":~/";
	$remote_file .= $PBSWEBUSERDIR . "/" . $directory . "/" . $jobname . ".pbs";
	$localfile = $PBSWEBTEMPUPLOADDIR . "/" . $username . "/" . $jobname . ".pbs";
	$scp = `scp "$remote_file" "$localfile" 2>&1`;
	$jobinfo = pbsutils_read($localfile);
}

if ($operation != "Load a Previous Job") {
	// prepare template list; this re-read every time is a waste
	// of time, later we should find a more efficient solution.
	$templates = array();
	$template_info = array();
	$i = 0;
	foreach ($PBSWEBTEMPLATELIST as $template_name => $template_file) {
		$templates[$i] = $template_name;
		$template_info[$i] = pbsutils_read($template_file);
		$i++;
	}
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title><?php echo $TITLE_SCRIPTGEN; ?></title>
	</head>

	<body bgcolor="white">
		<script language="JavaScript">
<!-- Hide from older browsers

<?php // existing jobs
if (sizeof($pbsjobs) > 0) {
	echo "var existing_jobs=new Array(" . sizeof($pbsjobs) . ");\n";
	$i = 0;
	foreach ($pbsjobs as $element) {
		echo "existing_jobs[$i]='$element';\n";
		$i = $i + 1;
	}
}
?>

	function FormSubmit() {
		var invalidchars = " /\t\n()[]\\|*?$&`<>{}";
		var nameval='';

		if(document.pressed == 'Submit Job') {
<?php
		if (sizeof($pbsjobs) > 0) {
			echo "    var num_jobs=" . sizeof($pbsjobs) . ";\n";
			echo "    overwriteval=document.mainform.overwrite.checked;\n";
		}
?>
		nameval = document.mainform.name.value;

		if (nameval == '') {
			alert('Job name is a required field, please try again.');
			return false;
		}
		for (var i = 0; i < nameval.length; i++) {
			var letter = nameval.charAt(i);
			if (invalidchars.indexOf(letter) != -1) {
				alert("Job name \""+nameval+"\" contains invalid characters."); 
				return false;
			}
		}

		if (!confirm("Submit job \""+nameval+"\" to queue?")) {
	    	return false;
	    }

<?php
	if(sizeof($pbsjobs) > 0) {
?>
	if (!overwriteval) {
		for (var i = 0; i < num_jobs; i++) {
			if (nameval == existing_jobs[i]) {
				alert("Job name \""+nameval+"\" already exists, please use another name or submit with the overwrite box checked."); 
				return false;
			}
		}
	}
<?php 
	} // if(sizeof($pbsjobs) > 0) 
?>
	document.mainform.action ="scriptsubmit.php";
} else {
	document.mainform.action ="scriptcreate.php";
}
return true;
}

	function LoadTemplate() {
		var selected=document.mainform.template.selectedIndex;

	<?php // templates scripts
		if (sizeof($templates) > 0) {
			for ($i = 0; $i < sizeof($templates); $i++) {
				$option_count = $i + 1;
				// Properly escape special characters in the shell script
				$script_str = str_replace("\\", "\\\\", $template_info[$i]['script']);
				$script_str = str_replace("\n", "\\n", $script_str);
				$script_str = str_replace("'", "\\'", $script_str);
				$script_str = str_replace("\"", "\\\"", $script_str);
				// JavaScript code!
				echo "  if(selected == $option_count) {\n";
				echo "    document.mainform.script.value=\"$script_str\";\n";
				echo "  }\n";
			}
		}
		
	?>
	}
	// Stop hiding -->
		</script>
		<h1><img src="<?php echo $PBSWEBHEADERLOGO; ?>" border="0" height="102" width="92" alt="PBS Logo"><?php echo $TITLE_SCRIPTGEN; ?></h1>
		<?php
		include ("navbar.php");
	?>
		<hr>

		<?php 
		//echo "Debuging, You are lucky!!<br>";
		//echo "<pre>";
		//print_r($jobinfo);
		//print_r($_POST);
		//echo "</pre>";
		?>

		<form method="POST" name="mainform" onSubmit="return FormSubmit();">

			<?php echo "\n<input type='hidden' name='host' value='$host'>";
			echo "\n<input type='hidden' name='directory' value='$directory'>\n";
			?>

			<table cellpadding="6" border="5">
				<tbody>
					<tr>
						<td>
						<br>
						<?php
						if($operation == "Load a Previous Job") {
						// menu for loading an old job
						?>
						<table style="width: 90%; text-align: left; margin-left: auto; margin-right: auto;" border="0" cellspacing="0" cellpadding="0">
							<tbody>
								<tr>
									<td style="text-align: center; vertical-align: middle;">
									<input name="operation" value="New Job" type="submit" onClick="document.pressed=this.value">
									<br>
									<br>
									</td>
									<td style="text-align: center; vertical-align: middle;">
									<br>
									<br>
									</td>
								</tr>
								<tr>
									<td style="text-align: center; vertical-align: middle;">
									Job list : 
									<br>
									<select name="loadjob">										
										<?php
										foreach ($pbsjobs as $element) {
											echo "<option value=\"$element\">$element</option>\n";
										}
										?>
									</select>
									<br>
									</td>
									<td style="text-align: left; vertical-align: middle;">
									<br>
									<input name="operation" value="Load" type="submit" onClick="document.pressed=this.value">
									<br>
									</td>
								</tr>
							</tbody>
						</table> 
						<?php
							} else {
							// $operation != "Load a Previous Job",,
							// output the case for a new job
						?>
						<h3>Project : <?php echo substr($directory, 1); ?></h3>
						<h3>Project Type : <?php echo $tipe; ?></h3>
						<table style="width: 90%; text-align: left; margin-left: auto; margin-right: auto;">
							<tbody>
								<tr>
									<td style="vertical-align: center;"><b>Job Name:</b>
									<input maxlength="12" name="name" size="12" value="<?php print($jobinfo['name']); ?>">
									<br>
									<input value="Yes" name="overwrite" type="checkbox" <?php
										if ($overwrite == "Yes") { print("checked");
										}
 									?>>
									Overwrite existing job.
									<br>
									<br>
									</td>
								</tr>
								<tr>
									<td style="vertical-align: center;"><b>Change Template:</b>
									<select name="template" onChange="javascript:LoadTemplate()">
										<option value="none">------</option>
										<?php
										foreach ($templates as $element) {
											echo "<option value=\"$element\">$element</option>\n";
										}
										?>
									</select>
									<br>
									<br>
									</td>
								</tr>
								<tr>
									<br>
									<br>
									<td style="vertical-align: top;">
									<input name="operation" type="submit" value="Load a Previous Job" onClick="document.pressed=this.value">
									</td>
								</tr>
							</tbody>
						</table> <?php } // end if($operation == "Load a Previous Job")... ?>
						<br>
						</td>
						<td colspan="1" rowspan="2"><b>Execution Commands:</b>
						<br>
						<textarea wrap="off" cols="65" rows="25" name="script"><?php print($jobinfo['script']); ?></textarea>
						<br>
						<b><em>(Please Use GNU Bourne-Again Shell Script (BASH))</em></b></td>
					</tr>
					<tr>
						<td><b>Job Options</b>
						<br>
						<table style="text-align: left; width: 100%;">
							<tbody>
								<tr>
									<td style="vertical-align: middle; text-align: left;">Queue to submit job to:</td>
									<td style="vertical-align: middle; text-align: left;">
									<select name="queue">
										<option value="">Default </option>
										<?php
										foreach ($queues as $element) {
											if ($element == $jobinfo['queue']) {
												echo "<option value=\"$element\" selected>$element</option>\n";
											} else {
												echo "<option value=\"$element\">$element</option>\n";
											}
										}
										?>
									</select></td>
								</tr>
								<tr>
									<td style="vertical-align: middle; text-align: left;">Number of nodes to use:</td>
									<td style="vertical-align: middle; text-align: left;">
									<select name="nodes">
										<?php
										for ($i = 1; $i <= $host_max_nodes; $i++) {
											if ($i == $jobinfo['nodes']) {
												echo "<option value=\"$i\" selected>$i</option>\n";
											} else {
												echo "<option value=\"$i\">$i</option>\n";
											}
										}
										?>
									</select></td>
								</tr>
								<tr>

									<td style="vertical-align: middle; text-align: left;">Processor(s) per node:</td>
									<td style="vertical-align: middle; text-align: left;">
									<select name="ppn">
										<?php
										for ($i = 1; $i <= $host_max_ppn; $i++) {
											if ($i == $jobinfo['ppn']) {
												echo "<option value=\"$i\" selected>$i</option>\n";
											} else {
												echo "<option value=\"$i\">$i</option>\n";
											}
										}
										?>
									</select></td>

								</tr>
								<tr>
									<td style="vertical-align: middle; text-align: left;">Maximum Walltime:
									<br> (HH:MM:SS)</td>
									<td style="vertical-align: middle; text-align: left;">
									<input
									type="text" name="maxtime" value="<?php print($jobinfo['maxtime']); ?>" size="8" maxlength="8">
									<br> (00:00:00 = no time limit) <br>
									</td>
								</tr>

								<tr>
									<td style="vertical-align: middle; text-align: left;">Merge output dan error in 1 file </td>
									<td style="vertical-align: middle; text-align: left;">
									<input
									type="checkbox" name="merge" value="Yes" <?php
										if ($jobinfo['merge'] == "Yes") { print("checked");
										}
 ?>>
									</td>
								</tr>
								<tr>
									<td style="vertical-align: middle; text-align: left;">Send message when job:</td>
									<td style="vertical-align: middle; text-align: left;">
									<input type="checkbox" name="mail_abort" value="Yes" <?php
										if ($jobinfo['mail_abort'] == "Yes") { print("checked");
										}
 ?>>
									Abort
									<input type="checkbox" name="mail_end" value="Yes" <?php
										if ($jobinfo['mail_end'] == "Yes") { print("checked");
										}
 ?>>
									End
									<input type="checkbox" name="mail_start" value="Yes" <?php
										if ($jobinfo['mail_start'] == "Yes") { print("checked");
										}
 ?>>
									Start</td>

								</tr>
								<tr>
									<td style="vertical-align: middle; text-align: left;">Send message to:</td>
									<td style="vertical-align: middle; text-align: left;">
									<input
									type="text" name="mail" value="<?php print($jobinfo['mail']); ?>" size="20">
									</td>
								</tr>
								<?php if($tipe == "array") : ?>
								<tr name="arroptions" id="arroptions">
									<td style="vertical-align: middle; text-align: left;">Array Options :</td>
									<td style="vertical-align: middle; text-align: left;">
									<input type="text" name="arroption" >
									</td>
								</tr>
								<?php endif; ?>
							</tbody>
						</table>
						<br>
						</td>
					</tr>
					<tr>
						<?php if($operation != "Load a Previous Job") : ?>
						<input type="hidden" value="<?php echo $tipe; ?>" name="tipefile"/>
						<td colspan="2" rowspan="1">
							<input name="operation" type="submit" onClick="document.pressed=this.value" value="Submit Job">
						<br>
						<?php endif; ?> </td>
					</tr>
					<tr></tr>
				</tbody>
			</table>
		</form>

		<hr>
		<?php include_once 'footer.php'; ?>
		<!-- $Id: scriptcreate.php,v 1.13 2004/03/18 21:04:19 platin Exp $ -->

	</body>
</html>
