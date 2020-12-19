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

foreach (glob("$uploadDir/*ipc*") as $csvFile) {
    $name = basename($csvFile);
    uploadFtp($csvFile, $dir7 . "/" . $name);
}

echo "\n End! \n ";
echo (date("\n l dS of F Y h:i:s A \n"));
