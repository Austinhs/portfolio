<?php

	/**
	* Create thumbnails of student pictures
	*
	* @author Andrew Leslie  
	*/
	
	require_once("Warehouse.php");
	
	if(empty($_SESSION['USERNAME']) || USER('PROFILE') != 'admin'){
		header("LOCATION: index.php");
	}
	
	foreach(DBGet(DBQuery("SELECT SYEAR FROM STUDENT_ENROLLMENT GROUP BY SYEAR order by SYEAR")) as $SYEAR){ // Grab all used school years and process these for the folder structure
		
		if(is_dir($StudentPicturesPath.$SYEAR['SYEAR'])){ // If the folder doesn't exist, then there can't be any pictures for the year, ignore them.
			$StudentPictureList = scandir($StudentPicturesPath.$SYEAR['SYEAR']);
			foreach(DBGet(DBQuery("SELECT STUDENT_ID FROM STUDENT_ENROLLMENT WHERE SYEAR = {$SYEAR['SYEAR']}")) as $STUDENT_ID) // Get all the student ids for that year
				foreach(preg_grep("~".$STUDENT_ID['STUDENT_ID']."~", $StudentPictureList) as $FileName){ // We don't know the extention so we'll just search for the student id and if it exists, grab the filename
					$image = imagecreatefromjpeg($StudentPicturesPath.$SYEAR['SYEAR']."/".$FileName);
					$width = imagesx($image);
					$height = imagesy($image);
					
					$ThumbHeight = 300; // px
					$ThumbWidth = floor($width * ($ThumbHeight / $height)); // Calcuate the width for it to scale
					
					$Thumbnail = imagecreatetruecolor($ThumbWidth, $ThumbHeight);
					imagecopyresized($Thumbnail, $image, 0, 0, 0, 0, $ThumbWidth, $ThumbHeight, $width, $height);
					imagejpeg($Thumbnail, $StudentPicturesPath.$SYEAR['SYEAR']."/".$FileName);
					
					echo("Processed: ".$StudentPicturesPath.$SYEAR['SYEAR']."/".$FileName."<br />\r\n");
				}
		}
	}
	
?>