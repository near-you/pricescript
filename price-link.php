<?php
ini_set('max_execution_time', 5900);
require_once __DIR__ . '/funcs.php';

echo (date("\nl dS of F Y h:i:s A \n"));
//clearDir(__DIR__ . '/ecache');   //// очищення тимчасової папки  ////
clearAttach();  ////  очищення папки /attach  ////
clearFiles();   ////   очищення папки /files  ////

////  API для запити для завантаження прайсу DCLink  ////
$dclink = '/opt.dclink.com.ua';
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

echo "\n Файл Dclink.xml завантажено та конвертовано в CSV \n ";

// Завантаження прайсу від Mobiking
$ch = curl_init('https://httpclient.mobiking.com.ua:9443/8a4378756cae4f8a8b0a97a992741de4_9c42b2155d9449089347fae25181d2d5.xml');
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

////  Конвертування прайсу з XML в CSV ////
$filexml = (__DIR__ . '/attach/Mobiking.xml');
if (filesize($filexml) >= 10000) {
    if (file_exists($filexml)) {
        $xml = simplexml_load_file($filexml);
        $foo = fopen(__DIR__ . '/files/mobiking.csv', 'w');
        createCsv($xml, $foo);
        fclose($foo);
    }
}

//// Вирізання надлишкових знаків "пробіл" ////
$text = file_get_contents(__DIR__ . '/files/mobiking.csv');
$delSpace = preg_replace('/\s\s+/', '', $text);
file_put_contents(__DIR__ . '/files/mobiking.csv', $delSpace);

echo "\n Файл Mobiking.xml завантажено та конвертовано в CSV \n ";

// завантаження прайса з 'opt.brain.com.ua' напряму по урл
$ch = curl_init('http://128.199.52.146/index.php?time=1594156050&companyID=4826&userID=10145&targetID=14&format=xlsx&lang=ru&token=4d4899e4dd3ae4e8a31faa105fbca4bee7c21c553ba4c64882738e8b110b2bd571ce44d31b77dbd9007dbd64c32c227669241d5c5b89fb8300ab442590a165cf&full=1');
$fp = fopen(__DIR__ . '/attach' . '/brain' . '.xlsx', 'wb');
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_exec($ch);
curl_close($ch);
fclose($fp);

echo "\n Файл Brain.xlsx завантажено \n ";

$dir = realpath(__DIR__ . '/attach');
$fileSPLObjects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST);

// Вибірка прайсів з розширенням .xlsx >= 15 Mb та подальша конвертація в .csv файл
foreach ($fileSPLObjects as $fullFileName => $fileSPLObject) {
    if ($fileSPLObject->isFile()) {
        $info = new SplFileInfo($fullFileName);
        if ($info->getExtension() === 'xlsx') {
            if (filesize($fullFileName) >= 10728640) {
                    xlsxToCsv(basename($fullFileName));
            }
        }
    }
}
echo(date("\n l dS of F Y h:i:s A \n"));

echo "Файл Brain.xlsx конвертовано в CSV\n ";

////  Конвертування з UTF-8 в windows-1251  ////
$dir = __DIR__ . '/files';
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
}

////  Сортування прайсів в CSV  по папках   ////
$uploadDir = (__DIR__ . '/attach'); // Основна папка з EXEL файлами(прайсами)
$uploadCsvDir = (__DIR__ . '/files');
$pl = '/Pricelists';
$dir6 = ($pl . '/6');
$dir8 = ($pl . '/8');
$dir12 = ($pl . '/12');

// // цикл для виборки прайсів з папки /files  та завантаження по папках в Pricelists

foreach (glob("$uploadCsvDir/brain*") as $csvFile) {
    $name = basename($csvFile);
    uploadFtp($csvFile, $dir6."/".$name);
}

foreach (glob("$uploadCsvDir/mobi*") as $csvFile) {
    $name = basename($csvFile);
    uploadFtp($csvFile, $dir8 . "/" . $name);
}

foreach (glob("$uploadCsvDir/*dclink*") as $csvFile) {
    $name = basename($csvFile);
    uploadFtp($csvFile, $dir12 . "/" . $name);
}

echo (date("\n l dS of F Y h:i:s A \n"));