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

############################################################
#
# Configurations, please modify the following constants 
# according to your system configurations.
#
############################################################

# Hostname of the default resource provider for PBSWeb-Lite to access
$PBSWEBDEFAULTHOST="ccsun41";

# List of all resourse providers and corresponding
# PBS commands, the format is
#  "hostname" => array ("qstat"=>"qstat_command" , 
#                       "qsub"=>"qsub_command", 
#                       "qdel"=>"qdel_command"......
$PBSWEBHOSTLIST=array( "ccsun41" => array ( "qstat" => "/usr/local/bin/qstat",
					    "qsub" => "/usr/local/bin/qsub",
					    "qdel" => "/usr/local/bin/qdel",
					    "max_nodes" => 16,
					    "max_ppn" => 2
					    )
		       );

# List of all queues on each resourse provider; use the
# name of queues as the keys and the corresponding max_walltime
# as the contents.
$PBSWEBQUEUELIST=array( "ccsun41" => array ( "short"    => "08:00:00",
					     "medium"   => "16:00:00",
					     "long"     => "36:00:00",
					     "verylong" => "96:00:00")
			);

# List of PBS Jobscript template files; label => template file
# Remember to modify those template files according to your PBS
# and system configurations.
$PBSWEBTEMPLATELIST=array( "plain" => "template/template_plain.pbs",
			   "g98"   => "template/template_g98.pbs",
			   "mpi"   => "template/template_mpi.pbs");

# Directory on the system where PBSWeb-Lite is installed.
$PBSWEBDIR="/var/www/pbs";

# Directory used to stage the file uploaded using PBSWeb.
# The directory should be writable by httpd; we suggest
# to set the permission of the directory as 700.
$PBSWEBTEMPUPLOADDIR="/var/www/pbs/upload";

# The path to the root of the PBSWeb-Lite directory on URL,
# the tailing '/' is necessary.
$PBSWEBPATH="/pbs/";

# Who to contact when there is a problem...
$PBSWEBMAIL="pbsweb@ccsun41.cc.ntu.edu.tw";

# Logo used in the webpage
$PBSWEBHEADERLOGO="img/ntulogo.gif";
$PBSWEBMAINLOGO="img/ntucc_main.jpg";

############################################################
# Global Constants for PBSWeb-Lite, you don't really need
# to modify the following constants.
############################################################

# Directory used by PBSWeb-Lite at the Resource Provider
# For example, if user "george" wants to use PBSWeb-Lite with their
# account at machine "aurora.nic.ualberta.ca", then george must
# have a directory called "~/pbsweb" (a symbolic link is also OK).
# This directory has to be created beforehand.
$PBSWEBUSERDIR="pbsweb";

# about the session cookie
$PBSWEBNAME="PBSWeb";
$PBSWEBEXPTIME=3600;

# A relative path to $PBSWEBDIR where files-to-be-downloaded will
# be staged.
$PBSWEBTEMPDOWNLOADDIR="download";

// $Id: config.php,v 1.14 2004/03/19 03:42:21 platin Exp $
?>
