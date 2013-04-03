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

  // pbsutil.php
  // Functions used to parse and create PBS scripts.

include_once("error.php");

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

// generate a pbs script using $pbsinfo array
function pbsutils_script($jobinfo)
{
  $jobscript = "#!/bin/sh --login\n";
  $jobscript .= "#\n";
  $jobscript .= "# This script is generated automatically by PBSWeb-Lite.\n";
  $jobscript .= "#\n";
  //  $jobscript .= "#PBS -S /bin/sh\n";
  if(isset($jobinfo['name']) && $jobinfo['name'] != "") {
    $jobscript .= "#PBS -N " . $jobinfo['name'] . "\n";
  } else {
    $jobscript .= "#PBS -N none\n";
  }
  if(isset($jobinfo['queue']) && $jobinfo['queue'] != "") {
    $jobscript .= "#PBS -q " . $jobinfo['queue'] . "\n";
  }
  if(isset($jobinfo['nodes'])) {
    if(isset($jobinfo['ppn'])) {
      $jobscript .= "#PBS -l nodes=" . $jobinfo['nodes'];
      $jobscript .= ":" . "ppn=" . $jobinfo['ppn'] . "\n";
    } else {
      $jobscript .= "#PBS -l nodes=" . $jobinfo['nodes'] . "\n";
    }
  } else {
    // default is one node
    if(isset($jobinfo['ppn'])) {
      $jobscript .= "#PBS -l nodes=1:ppn=" . $jobinfo['ppn'] . "\n";
    } else {
      $jobscript .= "#PBS -l nodes=1\n";
    }
  }
  if(isset($jobinfo['maxtime']) && $jobinfo['maxtime'] != "00:00:00") {
    $jobscript .= "#PBS -l walltime=" . $jobinfo['maxtime'] . "\n";
  }    
  if(isset($jobinfo['merge']) && $jobinfo['merge'] =="Yes") {
    $jobscript .= "#PBS -j oe\n";
  }
  $mstr="";
  if(isset($jobinfo['mail_abort']) && $jobinfo['mail_abort'] == "Yes") {
    $mstr .= "a";
  }
  if(isset($jobinfo['mail_end']) && $jobinfo['mail_end'] == "Yes") {
    $mstr .= "e";
  }
  if(isset($jobinfo['mail_start']) && $jobinfo['mail_start'] == "Yes") {
    $mstr .= "b";
  }
  if($mstr != "") {
    $jobscript .= "#PBS -m " . $mstr . "\n";
  }
  if(isset($jobinfo['mail']) && $jobinfo['mail'] != "") {
    $jobscript .= "#PBS -M " . $jobinfo['mail'] . "\n";
  }
  // two blank lines to separate the script
  $jobscript .= "#\n";
  $jobscript .= "\n";
  if(!preg_match("/^\s*cd\s+\\\$PBS_O_WORKDIR[\s;]/", $jobinfo['script'])) {
    // always cd to the workdir first
    $jobscript .= "cd \$PBS_O_WORKDIR\n";
  }
  // then the script body!
  // can't directly attach the whole $jobinfo['script'], we need to correct
  // the EOL between different systems.
  $script_array=explode("\n",$jobinfo['script']);
  foreach($script_array as $line) {
    $jobscript .= rtrim($line) . "\n";
  }

  return $jobscript;
}

// create a file "$filename" with the $jobinfo,
// note that this script operates in the overwrite mode!
function pbsutils_save($filename, $jobinfo)
{
  if (!$handle = fopen($filename, 'wb')) {
    error_page("Error opening file $filename");
    exit();
  } else {
    // file opened, create the jobscript
    $script_str=pbsutils_script($jobinfo);
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
function pbsutils_read($filename)
{
  $jobinfo=array();
  
  // default values
  $jobinfo['name']="none";
  $jobinfo['queue']="";      
  $jobinfo['nodes']=1;      
  $jobinfo['ppn']=1;        
  $jobinfo['maxtime']="00:00:00";    
  $jobinfo['merge']="No";      
  $jobinfo['mail_abort']="No"; 
  $jobinfo['mail_end']="No";   
  $jobinfo['mail_start']="No"; 
  $jobinfo['mail']="";       
  $jobinfo['script']="";
     
  $content=file($filename);

  $header_on=1;
  foreach($content as $line) {
    if( $line{0} != '#' && $header_on==1) {
      // header regime ends
      $header_on=0;
    }
    // parse PBS keywords
    if(preg_match("/^#PBS\s+-N\s+([^\s]+)/", $line,$matches)) {
      $jobinfo['name']=$matches[1];
    } elseif (preg_match("/^#PBS\s+-q\s+([^\s]+)/", $line,$matches)) {
      $jobinfo['queue']=$matches[1];
    } elseif (preg_match("/^#PBS\s+-l\s+nodes=([^\s]+)/", $line,$matches)) {
      $tmpstr=$matches[1];
      if(preg_match("/^(\d+)/",$tmpstr,$matches)) {
	$jobinfo['nodes']=$matches[1];
      }
      if(preg_match("/ppn=(\d+)/",$tmpstr,$matches)) {
	$jobinfo['ppn']=$matches[1];
      }
    } elseif (preg_match("/^#PBS\s+-l\s+walltime=([^\s]+)/", $line,$matches)) {
      $jobinfo['maxtime']=$matches[1];
    } elseif (preg_match("/^#PBS\s+-j\s+([^\s]+)/", $line,$matches)) {
      $tmpstr=" " . $matches[1];
      if(strpos($tmpstr,"o") && strpos($tmpstr,"e")) {
	$jobinfo['merge']="Yes";
      }
    } elseif (preg_match("/^#PBS\s+-m\s+([^\s]+)/", $line,$matches)) {
      $tmpstr=" " . $matches[1];
      if(strpos($tmpstr,"a")) {
	$jobinfo['mail_abort']="Yes";
      }
      if(strpos($tmpstr,"e")) {
	$jobinfo['mail_end']="Yes";
      }
      if(strpos($tmpstr,"b")) {
	$jobinfo['mail_start']="Yes";
      }
    } elseif (preg_match("/^#PBS\s+-M\s+([^\s]+)/", $line,$matches)) {
      $jobinfo['mail']=$matches[1];
    } else {
      if($header_on==0) {
	$jobinfo['script'] .= $line;
      }
    }
  }

  return $jobinfo;

}

// collect data in $databank array into $jobinfo
function pbsutils_collect(&$jobinfo,$databank) {
  if(isset($databank['name'])) {
    $jobinfo['name']=str_replace(" ","",trim($databank['name']));
    $jobinfo['name']=str_replace("/","",$jobinfo['name']);
  }
  if(isset($databank['queue'])) {
    $jobinfo['queue']=$databank['queue'];     
  }
  if(isset($databank['nodes'])) {
    $jobinfo['nodes']=$databank['nodes'];
  }
  if(isset($databank['ppn'])) {
    $jobinfo['ppn']=$databank['ppn'];
  }
  if(isset($databank['maxtime'])) {
    $jobinfo['maxtime']=$databank['maxtime'];
  }
  if(isset($databank['merge'])) {
    $jobinfo['merge']=$databank['merge'];
  }
  if(isset($databank['mail_abort'])) {
    $jobinfo['mail_abort']=$databank['mail_abort'];
  }
  if(isset($databank['mail_end'])) {
    $jobinfo['mail_end']=$databank['mail_end'];
  }  
  if(isset($databank['mail_start'])) {
    $jobinfo['mail_start']=$databank['mail_start'];
  }
  if(isset($databank['mail'])) {
    $jobinfo['mail']=$databank['mail'];
  }
  if(isset($databank['script'])) {
    // HTML form will escape "'\, we need to convert them back
    $script_str=str_replace("\\\\","\\",$databank['script']);
    $script_str=str_replace("\\'","'",$script_str);
    $script_str=str_replace("\\\"","\"",$script_str);
    $jobinfo['script']=$script_str;
  }      
}

// $Id: pbsutils.php,v 1.8 2004/03/18 21:04:19 platin Exp $
?>
