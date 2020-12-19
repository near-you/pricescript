<?php

ini_set('max_execution_time', 5900);
require_once 'funcs.php';
//require_once 'new.php';

//set_time_limit(9300);
error_reporting(-1);
/* ini_set('error_log', __DIR__ . '/php-errors.log');
error_log('Запись в лог', 0); */



echo (date("\n l dS of F Y h:i:s A \n"));
//clearDir(__DIR__ . '/ecache');   //// очищення тимчасової папки  ////
clearAttach();  ////  очищення папки /attach  ////
clearFiles();   ////   очищення папки /files  ////
emailAttach();  ////  виклик функції для завантаження вкладень з ел. пошти  ////
//getFileToFtp();

////  API для запити для завантаження прайсу DCLink  ////
/* $dclink = '/opt.dclink.com.ua';
$url = 'https://api.dclink.com.ua/api/GetPriceAll';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, array(
    'login' => 'L_nec.if.ua (Івано-Франківськ)',
    'password' => 'Pj96HrkI',
    'altname' => '1',
));

$output = curl_exec($ch);
curl_close($ch);
$fp = fopen(__DIR__ . '/attach' . $dclink . '.xml', 'w');
fwrite($fp, $output);
fclose($fp);

$filexml = (__DIR__ . '/attach' . $dclink . '.xml');
if (filesize($filexml) >= 10) {
    if (file_exists($filexml)) {
        $xml = simplexml_load_file($filexml);
        $foo = fopen(__DIR__ . '/files' . $dclink . '.csv', 'w');
        try {
            createCsv($xml, $foo);
        } catch (Exception $e) {
        }
        fclose($foo);
    }
}

echo "\n File Dclink.xml was downloaded \n "; */

// Завантаження прайсу від Mobiking
/* $ch = curl_init('https://httpclient.mobiking.com.ua:9443/8a4378756cae4f8a8b0a97a992741de4_9c42b2155d9449089347fae25181d2d5.xml');
$fp = fopen(__DIR__ . '/attach/' . 'Mobiking' . '.xml', 'wb');
//curl_setopt($ch, CURLOPT_TIMEOUT, 12000);
//curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 12000);
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

curl_exec($ch);
curl_close($ch);
fclose($fp);

echo "\n File Mobiking.xml was downloaded \n ";

////  Конвертування прайсу з XML в CSV ////
$filexml = (__DIR__ . '/attach/Mobiking.xml');
if (filesize($filexml) >= 10000) {
    if (file_exists($filexml)) {
        $xml = simplexml_load_file($filexml);
        $foo = fopen(__DIR__ . '/files/mobiking.csv', 'w');
        createCsv($xml, $foo);
        fclose($foo);
    }
} */
// завантаження прайса напряму по урл
/*$url = 'https://technoplus-pro.com/get_price';
$path = $_SERVER['DOCUMENT_ROOT'] . '/images/my-img.jpg';
file_put_contents($path, file_get_contents($url));*/


// завантаження прайса з 'opt.brain.com.ua' напряму по урл
/* $ch = curl_init('http://128.199.52.146/index.php?time=1594156050&companyID=4826&userID=10145&targetID=14&format=xlsx&lang=ru&token=4d4899e4dd3ae4e8a31faa105fbca4bee7c21c553ba4c64882738e8b110b2bd571ce44d31b77dbd9007dbd64c32c227669241d5c5b89fb8300ab442590a165cf&full=1');
$fp = fopen(__DIR__ . '/attach' . '/brain' . '.xlsx', 'wb');
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_exec($ch);
curl_close($ch);
fclose($fp);

echo "\n File Brain.xlsx was downloaded \n "; */
/*$brain = __DIR__ . '/attach/' . 'brain' . '.xlsx';
chmod($brain, 0644);*/


//https://technoplus-pro.com/get_price
//https://katran.vn.ua/b2b/api/csv?key=2627-cf9768 основний
//https://katran.vn.ua/b2b/api/xls?key=2627-cf9768 повний

/*$ch = curl_init('https://katran.vn.ua/b2b/api/csv?key=2627-cf9768');
$fp = fopen(safe_file(__DIR__ . '/attach/' . 'katran_main' . '.zip'), 'wb');
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_exec($ch);
curl_close($ch);
fclose($fp);*/


//Katan
/*$ch = curl_init('https://katran.vn.ua/b2b/api/xls?key=2627-cf9768');
$fp = fopen(safe_file(__DIR__ . '/attach/' . 'katran_full' . '.zip'), 'wb');
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_exec($ch);
curl_close($ch);
fclose($fp);*/


////  Вибірка архівів .zip з папки /attach для подальшого видобування з них прайсів
$dir = realpath(__DIR__ . '/attach');
$fileSPLObjects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST);

foreach ($fileSPLObjects as $fullFileName => $fileSPLObject) {
    if ($fileSPLObject->isFile()) {
        $info = new SplFileInfo($fullFileName);
        if ($info->getExtension() === 'zip') {
            if (filesize($fullFileName) >= 10) {
                exZip(basename($fullFileName));
            }
        }
    }
}

echo "\n Архіви розпаковано \n ";

// Вибірка прайсів з розширенням .xlsx >= 15 Mb та подальша конвертація в .csv файл
/* foreach ($fileSPLObjects as $fullFileName => $fileSPLObject) {
    if ($fileSPLObject->isFile()) {
        $info = new SplFileInfo($fullFileName);
        if ($info->getExtension() === 'xlsx') {
            if (filesize($fullFileName) >= 13728640) {
                try {
                    xlsxToCsv(basename($fullFileName));
                } catch (Exception $e) {
                }
            }
        }
    }
}
echo (date("\n l dS of F Y h:i:s A \n"));
echo "Файл в .csv конвертовано \n "; */

////  Завантаження прайсу з Mobiking в форматі XML  ////
/* $url = 'https://httpclient.mobiking.com.ua:9443/8a4378756cae4f8a8b0a97a992741de4_9c42b2155d9449089347fae25181d2d5.xml';
$path = '/attach/Mobiking.xml';
file_put_contents($path, file_get_contents($url)); */




//copy('https://httpclient.mobiking.com.ua:9443/8a4378756cae4f8a8b0a97a992741de4_9c42b2155d9449089347fae25181d2d5.xml', __DIR__ . '/attach/Mobiking.xml');

/* $file = 'https://httpclient.mobiking.com.ua:9443/8a4378756cae4f8a8b0a97a992741de4_9c42b2155d9449089347fae25181d2d5.xml';

$output_filename = __DIR__ . '/attach' . '/Mobiking.ua' . '.xml';

$host = $file;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $host);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_AUTOREFERER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_REFERER, "https://httpclient.mobiking.com.ua");
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_HEADER, 0);
$result = curl_exec($ch);
curl_close($ch);

//print_r($result); // prints the contents of the collected file before writing..


// the following lines write the contents to a file in the same directory (provided permissions etc)
$fp = fopen($output_filename, 'w');
fwrite($fp, $result);
fclose($fp); */

//request1('https://httpclient.mobiking.com.ua:9443/8a4378756cae4f8a8b0a97a992741de4_9c42b2155d9449089347fae25181d2d5.xml');




////  Обрізання перших 4 строки в прайсі CSV  ////
 //$text = file_get_contents(__DIR__ . '/attach/mobiking.csv');
//$trimed = preg_replace('/\n/', '', $text);
/*unset($lines[0]);
unset($lines[1]);
unset($lines[2]);
unset($lines[3]);*/
//var_dump($trimed);
//file_put_contents(__DIR__ . '/files/mobiking.csv', $trimed);

////  Конвертування з UTF-8 в windows-1251  ////
/*$dir = __DIR__ . '/files';
$dirAtt = __DIR__ . '/attach';
foreach (glob("$dir/*.csv") as $file) {
    $f = file_get_contents(__DIR__ . '/files/' . (basename($file)));
    $csv = iconv('utf-8', 'windows-1251//IGNORE', $f);
    file_put_contents(__DIR__ . '/files/' . (basename($file)), $csv);
}
foreach (glob("$dirAtt/*.csv") as $file) {
    $f = file_get_contents(__DIR__ . '/attach/' . (basename($file)));
    $csv = iconv('utf-8', 'windows-1251//IGNORE', $f);
    file_put_contents(__DIR__ . '/attach/' . (basename($file)), $csv);
}*/

////  Обрізання перших 4 строки в прайсі CSV  ////
//$text = file_get_contents(__DIR__ . '/attach/mobiking.csv');
//$trimed = preg_replace('/\s\s+/', '', $text);
/*unset($lines[0]);
unset($lines[1]);  \\r\\t\\n\s\s+
unset($lines[2]);
unset($lines[3]);*/
//var_dump($trimed);
//file_put_contents(__DIR__ . '/files/mobiking.csv', $trimed);


//Сортування прайсів в CSV  по папках
$uploadDir = (__DIR__ . '/attach'); // Основна папка з EXEL файлами(прайсами)
$uploadCsvDir = (__DIR__ . '/files');
$pl = '/Pricelists';
$dir1 = ($pl . '/1'); // Папка в яку буде копіюватись прайс за назвою
$dir2 = ($pl . '/2');
$dir3 = ($pl . '/3');
$dir4 = ($pl . '/4');
$dir5 = ($pl . '/5');
$dir6 = ($pl . '/6');
$dir7 = ($pl . '/7');
$dir8 = ($pl . '/8');
$dir9 = ($pl . '/9');
$dir10 = ($pl . '/10');
$dir11 = ($pl . '/11');
$dir12 = ($pl . '/12');
$dir13 = ($pl . '/13');
$dir14 = ($pl . '/14');



// // цикл для виборки прайсів з папки /files  та завантаження по папках в Pricelists
foreach (glob("$uploadDir/pric*") as $csvFile) {
    $name = basename($csvFile);
    uploadFtp($csvFile, ($dir1 . "/" . $name));
}

foreach (glob("$uploadDir/Pric*") as $csvFile) {
    $name = basename($csvFile);
    uploadFtp($csvFile, ($dir1 . "/" . $name));
}

foreach (glob("$uploadDir/*NewPrice*") as $csvFile) {
    $name = basename($csvFile);
    uploadFtp($csvFile, ($dir2 . "/" . $name));
}

foreach (glob("$uploadDir/TDB*") as $csvFile) {
    $name = basename($csvFile);
    uploadFtp($csvFile, $dir3 . "/" . $name);
}

foreach (glob("$uploadDir/*tplus*") as $csvFile) {
    $name = basename($csvFile);
    uploadFtp($csvFile, $dir4 . "/" . $name);
}

// /*foreach (glob("$uploadDir/*temaf1*") as $csvFile) {
//     $name = basename($csvFile);
//     copy($csvFile, $dir5."/".$name);
// }*/

/* foreach (glob("$uploadCsvDir/brain*") as $csvFile) {
    $name = basename($csvFile);
    uploadFtp($csvFile, $dir6."/".$name);
} */

foreach (glob("$uploadDir/*ipc*") as $csvFile) {
    $name = basename($csvFile);
    uploadFtp($csvFile, $dir7 . "/" . $name);
}

/* foreach (glob("$uploadCsvDir/mobi*") as $csvFile) {
    $name = basename($csvFile);
    uploadFtp($csvFile, $dir8 . "/" . $name);
} */

// foreach (glob("$uploadDir/") as $csvFile) {
//     $name = basename($csvFile);
//     copy($csvFile, $dir10."/".$name);
// }

// /*foreach (glob("$uploadDir/*atran") as $csvFile) {
//     $name = basename($csvFile);
//     copy($csvFile, $dir11."/".$name);
// }*/

/* foreach (glob("$uploadCsvDir/*dclink*") as $csvFile) {
    $name = basename($csvFile);
    uploadFtp($csvFile, $dir12 . "/" . $name);
} */

/* foreach (glob("$uploadDir/*Sales*") as $csvFile) {
    $name = basename($csvFile);
    copy($csvFile, $dir13."/".$name); 
}*/

/*foreach (glob("$uploadDir/*temal1*") as $csvFile) {
    $name = basename($csvFile);
    copy($csvFile, $dir14."/".$name);
}*/


echo "\n End! \n ";
echo (date("\n l dS of F Y h:i:s A \n"));
