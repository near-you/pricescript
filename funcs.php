<?php
use \PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use \PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

require __DIR__ . '/vendor/autoload.php';

//ini_set('max_execution_time', 5900);
error_reporting(-1);
/* ini_set('error_log', __DIR__ . '/php-errors.log');
error_log('Запись в лог', 0); */

//ini_set('max_execution_time', 2900);

//Завантаження вкладень з ел. пошти
function emailAttach()
{
    //set_time_limit(1300);
    //масив з адресами email, з яких будуть братись вкладення
    $names = array(
        //'test@test.com',
        'test@test.com',
        'test@test.com',
        //'test@test.com',
        'test@test.com',
        'test@test.com',
        'test@test.com'
    );

    $hostname = '{imap01.servage.net:993/imap/ssl/novalidate-cert}INBOX';
    $username = 'test@test.com'; # например somebody@gmail.com
    $password = '********';


    $inbox = imap_open($hostname, $username, $password) or die('Cannot connect to servage.net: ' . imap_last_error());

    $emails = imap_search($inbox, 'UNDELETED', FT_UID);

    $max_emails = 5; //максимальна кількість листів

    if ($emails) {

        $count = 1;
        rsort($emails);
        foreach ($emails as $email_number) {

            /* get information specific to this email*/
            $result = imap_fetch_overview($inbox, $email_number, FT_UID);
            foreach ($result as $overview) {
                //$from = "#{$overview->msgno} ({$overview->date}) - From: {$overview->from} {$overview->subject}\n";
                $from = ($overview->from);
                $spec = (stristr(imap_utf8($from), '@', false));
                $f = array("@", ">");
                $info = str_replace($f, '', $spec);
            }

            //get mail message
            //$message = imap_fetchbody($inbox, $email_number, 1);


            /* get mail structure */
            $structure = imap_fetchstructure($inbox, $email_number, FT_UID);
            $attachments = array();

            imap_delete($inbox, $email_number, FT_UID); // Помечаем письмо как удаленное

            /* if any attachments found... */
            if (isset($structure->parts) && count($structure->parts)) {
                for ($i = 0; $i < count($structure->parts); $i++) {
                    $attachments[$i] = array(
                        'is_attachment' => false,
                        'filename' => '',
                        'name' => '',
                        'attachment' => '',
                    );


                    if ($structure->parts[$i]->ifdparameters) {
                        foreach ($structure->parts[$i]->dparameters as $object) {
                            if (strtolower($object->attribute) == 'filename') {
                                $attachments[$i]['is_attachment'] = true;
                                $attachments[$i]['filename'] = $object->value;
                            }
                        }
                    }

                    if ($structure->parts[$i]->ifparameters) {
                        foreach ($structure->parts[$i]->parameters as $object) {
                            if (strtolower($object->attribute) == 'name') {
                                $attachments[$i]['is_attachment'] = true;
                                $attachments[$i]['name'] = $object->value;
                            }
                        }
                    }

                    if ($attachments[$i]['is_attachment']) {
                        $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i + 1, FT_UID);

                        /* 4 = QUOTED-PRINTABLE encoding */
                        if ($structure->parts[$i]->encoding == 3) {
                            $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                        } /* 3 = BASE64 encoding */ elseif ($structure->parts[$i]->encoding == 4) {
                            $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                        }
                    }
                }
            }

            /* iterate through each attachment and save it */
            foreach ($attachments as $attachment) {
                if ($attachment['is_attachment'] == 1) {
                    $filename = $attachment['name'];
                    $filename = imap_utf8($filename);
                    if (empty($filename)) $filename = $attachment['filename'];

                    if (empty($filename)) $filename = time() . ".dat";


                    /* prefix the email number to the filename in case two emails
                     * have the attachment with the same file name.
                     */


                    if ((ext($filename) == 'xlsx') || (ext($filename) == 'xls') || (ext($filename) == 'XLSX') || (ext($filename) == 'XLS') || (ext($filename) == 'zip')) {
                        foreach ($names as $v) {
                            if (basename(strval($info) . "-" . $filename)) {
                                $attach = (__DIR__ . '/attach/'); //міняємо директорію завантаження файла
                                $fp = fopen($attach . (strval($info)) . "-" . $filename, "w+"); //-- з додаванням UID email`у --->  $email_number  <---

                                fwrite($fp, $attachment['attachment']);
                                fclose($fp);
                            }
                        }
                    }
                }
            }

            if ($count++ >= $max_emails) break;
        }
    }

    //imap_expunge($inbox);  // Удаление помеченных писем

    /* close the connection */
    imap_close($inbox);

    echo "\n Email Attachment Done \n";
}

// Функція для завантаження файлів на віддалений сервер по FTP
function uploadFtp($fileWith, $fileTo)
{
    $ftp_serv = '********.net';
    $ftp_user = '*********';
    $ftp_pass = '*********';

    //Встановити з'єднання або вийти
    $conn_id = ftp_connect($ftp_serv) or die("Не вдалось з'єднатись з $ftp_serv \n");

    //Вхід по FTP
    if (ftp_login($conn_id, $ftp_user, $ftp_pass)) {
        ftp_pasv($conn_id, true); //включение пассивного режима
        echo "Успішно з'єднались з $ftp_serv під користувачем $ftp_user \n";
    } else {
        echo "Не вдалось увійти під користувачем $ftp_user \n";
    }
        if (ftp_put($conn_id, $fileTo, $fileWith, FTP_BINARY)) {
            echo "Файл успішно завантажений на сервер \n";
        } else {
            ftp_close($conn_id);
            echo "Не вдалось завантажити файл на сервер \n";
        }

    //Закриваєм з'єднання
    ftp_close($conn_id);
}

//Конвертація файлів з розширенням .xls. бібліотека "PhpSpreadsheet"
function xlsToCsv($fileXls)
{
    

    // Read the Excel file.
    $reader = new Xlsx();
    //$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load(__DIR__ . '/attach/' . $fileXls);
    $info = pathinfo($fileXls);
    $loadedSheetNames = $spreadsheet->getSheetNames();
    $writer = new Csv($spreadsheet);

    // Export to CSV file.
    //$writer = IOFactory::createWriter($spreadsheet, "Csv");
    /* $writer->setDelimiter(';');
    $writer->setEnclosure('"');
    $writer->setLineEnding("\r\n");
    $writer->setSheetIndex(0); */
    foreach($loadedSheetNames as $sheetIndex => $loadedSheetName) {
        $writer->setSheetIndex($sheetIndex);
        $writer->setDelimiter(';');
    $writer->setEnclosure('"');
    $writer->setLineEnding("\r\n");
    //$writer->setSheetIndex(0);
        $writer->save(__DIR__ . '/files/' . $loadedSheetName.'.csv');
    }

    //$writer->save(safe_file(__DIR__ . '/files/brain.csv'));
}

// конвертація великих Exel файлів в Csv
function xlsxToCsv($filesXlsx)
{
    //set_time_limit(4300);
    //$customTempFolderPath = ('ecache');
    // Read the Excel file.
    $reader = ReaderEntityFactory::createReaderFromFile(__DIR__ . '/attach/' . $filesXlsx);
    //$reader->setTempFolder($customTempFolderPath);
    $reader->setShouldFormatDates(false);
    $reader->open(__DIR__ . '/attach/' . $filesXlsx);

    $info = pathinfo($filesXlsx);

    // Export to CSV file.
    $writer = WriterEntityFactory::createCSVWriter();
    $writer->setShouldAddBOM(false);
    $writer->openToFile(__DIR__ . '/files/brain.csv');
    $writer->setFieldDelimiter(";");  // Set delimiter.
    foreach ($reader->getSheetIterator() as $sheet) {
        foreach ($sheet->getRowIterator() as $row) {
            // ... and copy each row into the new spreadsheet
            $writer->addRow($row);
        }
    }

    $reader->close();
    $writer->close();
}

function getFileToFtp()
{
    $local_file = __DIR__ . '/attach' . '/mobiking' . '.xml';
    $ftp_server = 'ftpclient.com.ua';
    $ftp_user_name = 'test';
    $ftp_user_pass = '*********';

    $handle = fopen($local_file, 'w');

    //Установить соединение или выйти
    $conn_id = ftp_connect($ftp_server, 2323) or die("Не удалось установить соединение с $ftp_server \n");

    //Вход по FTP
    if (ftp_login($conn_id, $ftp_user_name, $ftp_user_pass)) {
        ftp_pasv($conn_id, true); //включение пассивного режима
        echo "Успешно соединились с $ftp_server под пользователем $ftp_user_name \n";
    } else {
        echo "Не удалось соединиться под пользователем $ftp_user_name \n";
    }

    //Вичислюємо імя останнього файла на ФТП та передаємо для запису
    $buff = ftp_rawlist($conn_id, '/');
    $file = end($buff);
    $file1 = (stristr(($file), '2020', false));
    $remote_file = ($file1);

    if (ftp_fget($conn_id, $handle, $remote_file, FTP_BINARY, 0)) {
        echo "Произведена запись в $local_file\n";
    } else {
        echo "При скачке $remote_file в $local_file произошла проблема\n";
    }

    // закрытие соединения и локального файла
    ftp_close($conn_id);
    fclose($handle);
}


function extractZip($filesZip)
{
    // создаём объект
    $zip = new ZipArchive();

    // открываем архив
    if ($zip->open(__DIR__ . '/attach/' . $filesZip) !== TRUE) {
        die("Не могу открыть архив");
    }

    // переменовываем файл в архиве по его индексу
    //$zip->renameIndex(0, 'renamedByIndex.txt') or die("ERROR: не могу переменовать файл");

    // переменовываем файл в архиве по его имени
    //$zip->renameName("test3.txt", "renamedByName.txt") or die("ERROR: не могу переменовать файл");

    // извлекаем содержимое в папку назначения
    $zip->extractTo(__DIR__ . '/attach/');

    // закрываем и сохраняем архив
    $zip->close();
    //echo "Файл усепшно переменован в архиве archive2.zip!";
}

function exZip($filesZip)
{
    $archive = new PclZip(__DIR__ . '/attach/' . $filesZip);
    $result = $archive->extract(PCLZIP_OPT_PATH, __DIR__ . '/attach/');
    if($result == 0) echo $archive->errorInfo(true);
}

function safe_file($filename)
{
    $dir = dirname($filename);
    /*if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }*/

    $info = pathinfo($filename);
    $name = $dir . '/' . $info['filename'];
    $prefix = '';
    $ext = (empty($info['extension'])) ? '' : '.' . $info['extension'];

    if (is_file($name . $ext)) {
        $i = 1;
        $prefix = '_' . $i;
        while (is_file($name . $prefix . $ext)) {
            $prefix = '_' . ++$i;
        }
    }

    return $name . $prefix . $ext;
}

// Если в директории есть файл log.txt, файл будет сохранен с названием log_1.txt
//file_put_contents(safe_file(__DIR__ . '/log.txt'), $text);



function clearDir($dir, $rmdir = false)
{
    if ($objs = glob($dir . '/*')) {
        foreach ($objs as $obj) {
            is_dir($obj) ? clearDir($obj, true) : unlink($obj);
        }
    }
    if ($rmdir) {
        rmdir($dir);
    }
}

// очищення папки "/files" на початку роботи
function clearFiles()
{
    $dir = __DIR__ . '/files';
    $leave = array('index.html', '.htaccess');

    foreach (glob($dir . '/*') as $file) {
        if (!in_array(basename($file), $leave) && is_file($file)) {
            unlink($file);
        }
    }
}

// очищення папки "/attach" на початку роботи
function clearAttach()
{
    $dir = __DIR__ . '/attach';
    $leave = array('index.html', '.htaccess');

    foreach (glob($dir . '/*') as $file) {
        if (!in_array(basename($file), $leave) && is_file($file)) {
            unlink($file);
        }
    }
}

// конвертує xml в csv
function createCsv($xml, $f)
{

    foreach ($xml->children() as $item) {
        fputcsv($f, get_object_vars($item), ';', '"');
    }
}



function ext($filename)
{
    return mb_strtolower(mb_substr(mb_strrchr($filename, '.'), 1));
}

function request1($url, $postdata = null)
{
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    //curl_setopt($ch, CURLOPT_HEADER, true);

    curl_setopt($ch, CURLOPT_USERAGENT, "Googlebot/2.1 (http://www.googlebot.com/bot.html)");

    curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/tmp/cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/tmp/cookie.txt');
    //curl_setopt($ch, CURLOPT_COOKIESESSION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1000);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1000);

    //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);



    //curl_setopt($ch, CURLOPT_PROXY, '12.11.59.114:1080');
    //curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);


    if ($postdata) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    }

    $html = curl_exec($ch);
    $fp = fopen(__DIR__ . '/attach/Mobiking.xml', "wb");
    fwrite($fp, $html);
    //
    curl_close($ch);
    //return $html;
}
//file_put_contents(__DIR__ . '/tmp/cookie.txt', '');
