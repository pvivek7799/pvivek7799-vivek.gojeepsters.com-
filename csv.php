<?php

$dataarray = array();

if (($handle = fopen('pro.csv', 'r')) !== FALSE) {
    while (($data = fgetcsv($handle, 10000, ',')) !== FALSE) {
        $temp = array();
        if (($handle1 = fopen('pro1.csv', 'r')) !== FALSE) {
            while (($data1 = fgetcsv($handle1, 10000, ',')) !== FALSE) {
                $data1[]=$data[0];
              $dataarray[]=$data1;
            }
        }
//	if($data[27]){
//	    $content = file_get_contents($data[27]);
//	//Store in the filesystem.
//	$fp = fopen("C:/xampp/htdocs/magento/media/import/".$data[0].".jpg", "w");
//	fwrite($fp, $content);
//	fclose($fp);
//	}
//	if($data[26]){
//	    $content = file_get_contents($data[27]);
//	//Store in the filesystem.
//	$fp = fopen("C:/xampp/htdocs/magento/media/import/".$data[0]."_1.jpg", "w");
//	fwrite($fp, $content);
//	fclose($fp);
//	}
//	if($data[28]){
//	    $content = file_get_contents($data[27]);
//	//Store in the filesystem.
//	$fp = fopen("C:/xampp/htdocs/magento/media/import/".$data[0]."_2.jpg", "w");
//	fwrite($fp, $content);
//	fclose($fp);
//	}
    }
}
$file = fopen("output.csv","w");

foreach ($dataarray as $line) {
  fputcsv($file, $line);
}

fclose($file);
?>