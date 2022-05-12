#!/usr/bin/php
<?php
/**
 * processlogs.php
 *
 * Extracts witness data from Helium miner logs
 *
 * @author     Iñigo Flores
 * @copyright  2022 Iñigo Flores
 * @license    https://opensource.org/licenses/MIT  MIT License
 * @version    0.02
 * @link       https://github.com/inigoflores/helium-miner-log-analyzer
  */

$logsFolder = './';

$logsFolders = [
        'Docker'         => '/var/log/miner/', //Also Controllino
        'Pisces P100'    => '/home/pi/hnt/miner/log/',
        'Milesight UG65' => '/mnt/mmcblk0p1/miner_data/log/', //adding it for future use,as PHP and opkg are missing (OpenWrt)
        'Panther X2'     => '/opt/panther-x2/miner_data/log/',
        'RHF2S308'       => '/opt/helium/miner_data/log/',
        'HeliumDIY'      => '/home/pi/miner_data/log/'
];

foreach ($logsFolders as $folder){
    if (is_dir($folder)) {
        $logsFolder = $folder;
        break;
    }
}

$startDate = "2000-01-01";
$endDate = "2030-01-01";

// Command line options
$options = ["p:","s:","e:","a","l","c::"];
$opts = getopt(implode("",$options));

// Defaults to stats when called
if (!(isset($opts['l']) || isset($opts['c']))) {
    $opts['a']=true;
}

foreach ($options as $key=>$val){
    $options[$key] = str_replace(":","",$val);
}

uksort($opts, function ($a, $b) use ($options) {
    $pos_a = array_search($a, $options);
    $pos_b = array_search($b, $options);
    return $pos_a - $pos_b;
});

// Handle command line arguments
foreach (array_keys($opts) as $opt) switch ($opt) {
    case 'p':
        $logsFolder = $opts['p'];
        if (substr($logsFolder,strlen($logsFolder)-1) != "/"){
            $logsFolder.="/";
        };
        break;
    case 's':
        if (!DateTime::createFromFormat('Y-m-d',  $opts['s'])){
            exit("Wrong date format");
        }
        $startDate = $opts['s'];
        break;
    case 'e':
        if (!DateTime::createFromFormat('Y-m-d',  $opts['e'])){
            exit("Wrong date format");
        }
        $endDate = $opts['e'];
        break;
    case 'a':
        echo "\nUsing logs in folder {$logsFolder}\n\n";
        $beacons = extractData($logsFolder,$startDate,$endDate);
        echo generateStats($beacons);
        exit(1);

    case 'l':
        echo "\nUsing logs in folder {$logsFolder}\n\n";
        $beacons = extractData($logsFolder,$startDate,$endDate);
        echo generateList($beacons);
        exit(1);
        
    case 'c':
        $beacons = extractData($logsFolder,$startDate,$endDate);
        $filename = $opts['c'];
        echo generateCSV($beacons,$filename);
        exit(1);        
}


/*
 * -------------------------------------------------------------------------------------------------
 * Functions
 * -------------------------------------------------------------------------------------------------
 */

/**
 * @param $beacons
 * @return string
 */
function generateStats($beacons) {


    if (empty($beacons)) {
        exit("No witnesses found\n");
    }

    $startTime = DateTime::createFromFormat('Y-m-d H:i:s',explode('.',$beacons[0]['datetime'])[0]);
    $endTime = DateTime::createFromFormat('Y-m-d H:i:s',explode('.',end($beacons)['datetime'])[0]);
    $intervalInHours = ($endTime->getTimestamp() - $startTime->getTimestamp())/3600;

    $successful = 0;

    foreach ($beacons as $beacon){

        // General Witnesses Overview
        if ($beacon['status']=='successfully sent') {
            $successful++;
        }

    }

    $total = sizeOf($beacons);
    $totalFailed = $total - $successful;
    $totalPerHour = round($total / $intervalInHours,2);

    $percentageSuccessful = round($successful/$total*100,2);
    $percentageFailed = round($totalFailed/$total*100,2);

    $output = "\nGeneral Witnesses Overview  \n";
    $output.= "----------------------------------\n";
    $output.= "Total witnesses                   = ". str_pad($total, 5, " ", STR_PAD_LEFT) .
        str_pad(" ({$totalPerHour}/hour)", 13, " ", STR_PAD_LEFT)  . "\n";
    $output.= "Succesfully delivered             = ". str_pad($successful, 5, " ", STR_PAD_LEFT) .
        str_pad("({$percentageSuccessful}%)", 9, " ", STR_PAD_LEFT)  . "\n";
    $output.= "Failed                            = ". str_pad($totalFailed, 5, " ", STR_PAD_LEFT) .
        str_pad("({$percentageFailed}%)", 9, " ", STR_PAD_LEFT) . " \n";


    return $output;
}

/**
 * @param $beacons
 * @return string
 */
function generateList($beacons) {

    $systemDate = new DateTime();

    $output = "Date                | Freq  | RSSI | SNR   | Noise  | Status  \n";
    $output.= "------------------------------------------------------------- \n";

    foreach ($beacons as $beacon){

        $datetime = DateTime::createFromFormat('Y-m-d H:i:s.u',$beacon['datetime'], new DateTimeZone( 'UTC' ));
        $datetime->setTimezone($systemDate->getTimezone());

        $rssi = str_pad($beacon['rssi'], 4, " ", STR_PAD_LEFT);
        $snr = str_pad($beacon['snr'], 5, " ", STR_PAD_LEFT);
        $noise = str_pad(number_format((float) ($beacon['rssi'] - $beacon['snr']),1),6,  " ", STR_PAD_LEFT);
        $status = str_pad($beacon['status'], 17, " ", STR_PAD_RIGHT);
        $reasonShort = @$beacon['reasonShort'];
        $datetimeStr = $datetime->format("d-m-Y H:i:s");
        //$reason = @$beacon['reason'];

        $output.=@"{$datetimeStr} | {$beacon['freq']} | {$rssi} | {$snr} | {$noise} | {$status}  \n";

    }
    return $output;
}

/**
 * @param $logsFolder
 * @return array
 */
function extractData($logsFolder, $startDate, $endDate){

    $beacons = [];
    $filenames = glob("{$logsFolder}console*.log*");

    if (empty($filenames)){
        exit ("No logs found. Please chdir to the Helium miner logs folder or specify a path.\n");
    }

    rsort($filenames); //Order is important, from older to more recent.

    foreach ($filenames as $filename) {

        $buf = file_get_contents($filename,);
        if(substr($filename, -3) == '.gz') {
            $buf = gzdecode($buf);
        }

        $lines = explode("\n", $buf);
        unset($buf);

        foreach ($lines as $line) {

            if (preg_match('/miner_onion_server_light:decrypt:{[0-9]+,[0-9]+} (?:re-)?sending witness at RSSI/', $line) ||
               (preg_match('/@miner_poc_grpc_client_statem:send_report:{[0-9]+,[0-9]+} failed to submit report/', $line)))
            {
                $fields = explode(' ', $line);
                $datetime = $fields[0] . " " . $fields[1];
                if ($datetime<$startDate || $datetime>$endDate) {
                    continue;
                }
                $session = explode('>',explode('<', $fields[4])[1])[0];
            } else {
                continue;
            }

            if (preg_match('/sending witness at RSSI/', $line)){
                $rssi = str_pad(substr($fields[9], 0, -1), 4, " ", STR_PAD_LEFT);
                $freq = substr($fields[11], 0, -1);
                $snr = $fields[13];
                $status = "successfully sent";
                $beacons[$rssi.$freq.$snr] = array_merge((array)@$beacons[$session], compact('datetime', 'rssi', 'freq', 'snr', 'status'));
                continue;
            }

            if (preg_match('/failed to submit report/', $line)){
                $temp = explode('<<',$fields[13]);
                $temp1 = explode("," , $temp[1]);
                $temp3 = explode("," , $temp[3]);

                $rssi = $temp1[sizeof($temp1)-2];
                $freq = $temp3[2];
                $snr = $temp3[1];
                $status = "failed";
                $beacons[$rssi.$freq.$snr] = array_merge((array)@$beacons[$session], compact('datetime', 'rssi', 'freq', 'snr', 'status'));
                continue;
            }

        }
    }
    //
    foreach ($beacons as $session => $beacon) {
        if (empty(@$beacon['rssi'])) {
           unset($beacons[$session]);
        }
    }

    usort($beacons, function($a, $b) {
        return $a['datetime'] <=> $b['datetime'];
    });

    return $beacons;
}


/**
 * @param $beacons
 * @return string
 */
function generateCSV($beacons, $filename=false) {
    $columns = ['Date','Freq','RSSI','SNR','Noise','Status'];
    $data = array2csv($columns);
    foreach ($beacons as $beacon){
        $noise = number_format((float) ($beacon['rssi'] - $beacon['snr']),1);
        $data.= @array2csv([
            $beacon['datetime'],$beacon['freq'],$beacon['rssi'],$beacon['snr'],$noise, $beacon['status']]);
    }

    if ($filename) {
        $data = "SEP=;" . $data;
        file_put_contents($filename,$data);
        return "Data saved to $filename\n";
    }

    return $data;

}

/**
 * @param $fields
 * @param string $delimiter
 * @param string $enclosure
 * @param string $escape_char
 * @return false|string
 */
function array2csv($fields, $delimiter = ",", $enclosure = '"', $escape_char = '\\')
{
    $buffer = fopen('php://temp', 'r+');
    fputcsv($buffer, $fields, $delimiter, $enclosure, $escape_char);
    rewind($buffer);
    $csv = fgets($buffer);
    fclose($buffer);
    return $csv;
}

