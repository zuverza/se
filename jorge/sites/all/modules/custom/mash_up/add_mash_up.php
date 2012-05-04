<?php

header('Location: /jorge');


	
$uid = $_GET['uid'];
$nid = $_GET['nid'];

$action = $_GET['action'];



if(isset($uid) && isset($nid)){

	$link = mysql_connect('localhost', 'pajarocl', 'Pilotto#07')or die(mysql_error());


	mysql_select_db('pajarocl_drp8') or die(mysql_error());



	if($action == 'add'){

		$query = "INSERT INTO dr_mash_up (nid, uid) VALUES ('$nid', '$uid')"; 

		mysql_query($query)or die(mysql_error());

		

	}
	else if($action == 'remove'){
		
		
		$query = "DELETE FROM  dr_mash_up WHERE nid = '$nid' AND uid = '$uid'"; 

		mysql_query($query)or die(mysql_error());


	}
	else if ($action == 'make_mash_up'){
		
	require ("incl/pclzip.lib.php");
	
	$zipfile = new PclZip('zipfile.zip');


	$query = "SELECT m. * , fu . *, fm.* FROM dr_mash_up m,  dr_file_usage fu, dr_file_managed  fm WHERE m.nid  = $nid AND m.nid = fu.id AND fu.fid = fm.fid  ";


	$result = mysql_query($query);




	while($files = mysql_fetch_array($result)){
		
		//$file_in = 'http://haciendarealcelaya.com/jorge/sites/default/files/'.$files['filename'];


		//$files_to_zip[] =  '../../../../default/files/' . $files['filename'];

		$files_to_zip[] =    $files['filename'];

	}

	//$files_to_zip = array('mash_up.module');

		$result = create_zip($files_to_zip,"my-archive".$uid.".zip");

		//echo '<META HTTP-EQUIV="Refresh" Content="0; URL="my-archive'.$uid.'.zip">';    


	}


	mysql_close($link);
}










/* creates a compressed zip file */
function create_zip($files = array(),$destination = '',$overwrite = false) {

  //if the zip file already exists and overwrite is false, return false
  if(file_exists($destination) && !$overwrite) { return false; }
  //vars
  $valid_files = array();
  //if files were passed in...
  if(is_array($files)) {
    //cycle through each file
    foreach($files as $file) {
      //make sure the file exists
      if(file_exists($file)) {
        $valid_files[] = $file;
      }
    }
  }
  //if we have good files...
  if(count($valid_files)) {
    //create the archive
    $zip = new ZipArchive();
    if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
      return false;
    }
    //add the files
    foreach($valid_files as $file) {
      $zip->addFile($file,$file);
    }
    //debug
    //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
    
    //close the zip -- done!
    $zip->close();
    
    //check to make sure the file exists
    return file_exists($destination);
  }
  else
  {
    return false;
  }
}


?>