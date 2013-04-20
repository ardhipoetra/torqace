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

// pbsutil.php
// Functions used to parse and create PBS scripts.

include_once ("error.php");

//
// A PBS jobscript is defined using an array:
//
// $jobinfo['name']:       string, name of the PBS job and jobscript.
// $jobinfo['queue']:      string, the queue to submit the job.
// $jobinfo['nodes']:      string, number of nodes to use.
// $jobinfo['ppn']:        string, number of cpu per node.
// $jobinfo['maxtime']:    string, the maximal wall-time.
// $jobinfo['merge']:      string, Yes means merge (#PBS -j oe), otherwise no.
// $jobinfo['mail_abort']: string, Yes means send mail when job aborts.
// $jobinfo['mail_end']:   string, Yes means send mail when job ends.
// $jobinfo['mail_start']: string, Yes means send mail when job starts.
// $jobinfo['mail']:       string, mail address for the mail.
// $jobinfo['script']:     string, the entire jobscript.
//
// added jobinfo properties below
// $jobinfo['type']:       string, tipe job "file", "compressed", "array"

// generate a pbs script using $pbsinfo array
function pbsutils_script($jobinfo) {
	$jobscript = "#!/bin/sh --login\n";
	$jobscript .= "#\n";
	$jobscript .= "# This script is generated automatically by Torqace.\n";
	$jobscript .= "#\n";
	$jobscript .= "# TORQ -t ".$jobinfo['type'];
	$jobscript .= "\n#\n";
	//  $jobscript .= "#PBS -S /bin/sh\n";
	if (isset($jobinfo['name']) && $jobinfo['name'] != "") {
		$jobscript .= "#PBS -N " . $jobinfo['name'] . "\n";
	} else {
		$jobscript .= "#PBS -N none\n";
	}
	if (isset($jobinfo['queue']) && $jobinfo['queue'] != "") {
		$jobscript .= "#PBS -q " . $jobinfo['queue'] . "\n";
	}
	if (isset($jobinfo['nodes'])) {
		if (isset($jobinfo['ppn'])) {
			$jobscript .= "#PBS -l nodes=" . $jobinfo['nodes'];
			$jobscript .= ":" . "ppn=" . $jobinfo['ppn'] . "\n";
		} else {
			$jobscript .= "#PBS -l nodes=" . $jobinfo['nodes'] . "\n";
		}
	} else {
		// default is one node
		if (isset($jobinfo['ppn'])) {
			$jobscript .= "#PBS -l nodes=1:ppn=" . $jobinfo['ppn'] . "\n";
		} else {
			$jobscript .= "#PBS -l nodes=1\n";
		}
	}
	if (isset($jobinfo['maxtime']) && $jobinfo['maxtime'] != "00:00:00") {
		$jobscript .= "#PBS -l walltime=" . $jobinfo['maxtime'] . "\n";
	}
	if (isset($jobinfo['merge']) && $jobinfo['merge'] == "Yes") {
		$jobscript .= "#PBS -j oe\n";
	}
	$mstr = "";
	if (isset($jobinfo['mail_abort']) && $jobinfo['mail_abort'] == "Yes") {
		$mstr .= "a";
	}
	if (isset($jobinfo['mail_end']) && $jobinfo['mail_end'] == "Yes") {
		$mstr .= "e";
	}
	if (isset($jobinfo['mail_start']) && $jobinfo['mail_start'] == "Yes") {
		$mstr .= "b";
	}
	if ($mstr != "") {
		$jobscript .= "#PBS -m " . $mstr . "\n";
	}
	if (isset($jobinfo['mail']) && $jobinfo['mail'] != "") {
		$jobscript .= "#PBS -M " . $jobinfo['mail'] . "\n";
	}
	// two blank lines to separate the script
	$jobscript .= "#\n";
	$jobscript .= "\n";
	if (!preg_match("/^\s*cd\s+\\\$PBS_O_WORKDIR[\s;]/", $jobinfo['script'])) {
		// always cd to the workdir first
		$jobscript .= "cd \$PBS_O_WORKDIR\n";
	}
	// then the script body!
	// can't directly attach the whole $jobinfo['script'], we need to correct
	// the EOL between different systems.
	$script_array = explode("\n", $jobinfo['script']);
	foreach ($script_array as $line) {
		$jobscript .= rtrim($line) . "\n";
	}

	return $jobscript;
}

// create a file "$filename" with the $jobinfo,
// note that this script operates in the overwrite mode!
function pbsutils_save($filename, $jobinfo) {
	if (!$handle = fopen($filename, 'wb')) {
		error_page("Error opening file $filename");
		exit();
	} else {
		// file opened, create the jobscript
		$script_str = pbsutils_script($jobinfo);
		//Write the jobscript to the file
		if (!fwrite($handle, $script_str)) {
			fclose($handle);
			error_page("Error writing to file $filename");
			exit();
		}
		fclose($handle);
	}
}

// read and parse a PBS jobscript, return the result in a jobinfo array
function pbsutils_read($filename) {
	$jobinfo = array();

	// default values
	$jobinfo['name'] = "none";
	$jobinfo['queue'] = "";
	$jobinfo['nodes'] = 1;
	$jobinfo['ppn'] = 1;
	$jobinfo['maxtime'] = "00:00:00";
	$jobinfo['merge'] = "No";
	$jobinfo['mail_abort'] = "No";
	$jobinfo['mail_end'] = "No";
	$jobinfo['mail_start'] = "No";
	$jobinfo['mail'] = "";
	$jobinfo['script'] = "";
	
	$jobinfo['type'] = "file";

	$content = file($filename);

	$header_on = 1;
	foreach ($content as $line) {
		if ($line{0} != '#' && $header_on == 1) {
			// header regime ends
			$header_on = 0;
		}
		//parse Torqace keywords
		if (preg_match("/^#\s+TORQ\s+-t\s+([^\s]+)/", $line, $matches)) {
			$jobinfo['type'] = $matches[1];
		}
		
		// parse PBS keywords
		if (preg_match("/^#PBS\s+-N\s+([^\s]+)/", $line, $matches)) {
			$jobinfo['name'] = $matches[1];
		} elseif (preg_match("/^#PBS\s+-q\s+([^\s]+)/", $line, $matches)) {
			$jobinfo['queue'] = $matches[1];
		} elseif (preg_match("/^#PBS\s+-l\s+nodes=([^\s]+)/", $line, $matches)) {
			$tmpstr = $matches[1];
			if (preg_match("/^(\d+)/", $tmpstr, $matches)) {
				$jobinfo['nodes'] = $matches[1];
			}
			if (preg_match("/ppn=(\d+)/", $tmpstr, $matches)) {
				$jobinfo['ppn'] = $matches[1];
			}
		} elseif (preg_match("/^#PBS\s+-l\s+walltime=([^\s]+)/", $line, $matches)) {
			$jobinfo['maxtime'] = $matches[1];
		} elseif (preg_match("/^#PBS\s+-j\s+([^\s]+)/", $line, $matches)) {
			$tmpstr = " " . $matches[1];
			if (strpos($tmpstr, "o") && strpos($tmpstr, "e")) {
				$jobinfo['merge'] = "Yes";
			}
		} elseif (preg_match("/^#PBS\s+-m\s+([^\s]+)/", $line, $matches)) {
			$tmpstr = " " . $matches[1];
			if (strpos($tmpstr, "a")) {
				$jobinfo['mail_abort'] = "Yes";
			}
			if (strpos($tmpstr, "e")) {
				$jobinfo['mail_end'] = "Yes";
			}
			if (strpos($tmpstr, "b")) {
				$jobinfo['mail_start'] = "Yes";
			}
		} elseif (preg_match("/^#PBS\s+-M\s+([^\s]+)/", $line, $matches)) {
			$jobinfo['mail'] = $matches[1];
		} else {
			if ($header_on == 0) {
				$jobinfo['script'] .= $line;
			}
		}
	}

	return $jobinfo;

}

// collect data in $databank array into $jobinfo
function pbsutils_collect(&$jobinfo, $databank) {
	if (isset($databank['name'])) {
		$jobinfo['name'] = str_replace(" ", "", trim($databank['name']));
		$jobinfo['name'] = str_replace("/", "", $jobinfo['name']);
	}
	if (isset($databank['queue'])) {
		$jobinfo['queue'] = $databank['queue'];
	}
	if (isset($databank['nodes'])) {
		$jobinfo['nodes'] = $databank['nodes'];
	}
	if (isset($databank['ppn'])) {
		$jobinfo['ppn'] = $databank['ppn'];
	}
	if (isset($databank['maxtime'])) {
		$jobinfo['maxtime'] = $databank['maxtime'];
	}
	if (isset($databank['merge'])) {
		$jobinfo['merge'] = $databank['merge'];
	}
	if (isset($databank['mail_abort'])) {
		$jobinfo['mail_abort'] = $databank['mail_abort'];
	}
	if (isset($databank['mail_end'])) {
		$jobinfo['mail_end'] = $databank['mail_end'];
	}
	if (isset($databank['mail_start'])) {
		$jobinfo['mail_start'] = $databank['mail_start'];
	}
	if (isset($databank['mail'])) {
		$jobinfo['mail'] = $databank['mail'];
	}
	if (isset($databank['script'])) {
		// HTML form will escape "'\, we need to convert them back
		$script_str = str_replace("\\\\", "\\", $databank['script']);
		$script_str = str_replace("\\'", "'", $script_str);
		$script_str = str_replace("\\\"", "\"", $script_str);
		$jobinfo['script'] = $script_str;
	}
}

// parse qstat (lihat stat jobs) supaya ke array untuk reformatting
function parseQstat($line) {

	$line_arr = preg_split('/\r\n|\n|\r/',$line);
	for ($i = 2;$i<sizeof($line_arr);$i++) {
		$arr = preg_split("/\s+/", $line_arr[$i]);
		$jobs[$i]['id'] = $arr[0]; //id job
		$jobs[$i]['nama'] = $arr[1]; //nama job
		$jobs[$i]['user'] = $arr[2]; //user yang ngerun
		$jobs[$i]['time'] = $arr[3]; //waktu yg dipake
		$jobs[$i]['status'] = $arr[4]; //status : C/R/etc
		$jobs[$i]['queue'] = $arr[5]; //queue yang dipake
	}
	return $jobs;	
}
// parse qstat -Q (lihat queue) supaya ke array untuk reformatting
function parseQstat_Q($line) {

	$queueinfo['nama'] = "Default";
	$queueinfo['maxjob'] = "0";
	$queueinfo['totrunjob'] = "0";
	$queueinfo['isenable'] = "yes";
	$queueinfo['startedstat'] = "yes";
	$queueinfo['que'] = "0";
	$queueinfo['run'] = "0";
	$queueinfo['hld'] = "0";
	$queueinfo['wat'] = "0";
	$queueinfo['trn'] = "0";
	$queueinfo['ext'] = "0";
	$queueinfo['type'] = "E";
	
	$line = preg_split('/\r\n|\n|\r/',$line);
	
	$arr = preg_split("/\s+/", $line[2]); // hanya 1 host paling atas
	$queueinfo['nama'] = $arr[0];
	$queueinfo['maxjob'] = $arr[1];
	$queueinfo['totrunjob'] = $arr[2];
	$queueinfo['isenable'] = $arr[3];
	$queueinfo['startedstat'] = $arr[4];
	$queueinfo['que'] = $arr[5];
	$queueinfo['run'] = $arr[6];
	$queueinfo['hld'] = $arr[7];
	$queueinfo['wat'] = $arr[8];
	$queueinfo['trn'] = $arr[9];
	$queueinfo['ext'] = $arr[10];
	$queueinfo['type'] = $arr[11];

	return $queueinfo;
}

// parse qstat -B (lihat host) supaya ke array untuk reformatting
function parseQstat_B($line) {
	$queue_host_info = array();
	$queue_host_info['nama'] = wolf.cs.ui.ac.id;
	$queue_host_info['maxjob'] = "0";
	$queue_host_info['totrunjob'] = "0";
	$queue_host_info['que'] = "0";
	$queue_host_info['run'] = "0";
	$queue_host_info['hld'] = "0";
	$queue_host_info['wat'] = "0";
	$queue_host_info['trn'] = "0";
	$queue_host_info['ext'] = "0";
	$queue_host_info['stat'] = "Active";
	
	$line = preg_split('/\r\n|\n|\r/',$line);
	
	$arr = preg_split("/\s+/", $line[2]); // hanya 1 server paling atas
	$queue_host_info['nama'] = $arr[0];
	$queue_host_info['maxjob'] = $arr[1];
	$queue_host_info['totrunjob'] = $arr[2];
	$queue_host_info['que'] = $arr[3];
	$queue_host_info['run'] = $arr[4];
	$queue_host_info['hld'] = $arr[5];
	$queue_host_info['wat'] = $arr[6];
	$queue_host_info['trn'] = $arr[7];
	$queue_host_info['ext'] = $arr[8];
	$queue_host_info['stat'] = $arr[9];
	
	return $queue_host_info;
	
}
// $Id: pbsutils.php,v 1.8 2004/03/18 21:04:19 platin Exp $
?>
