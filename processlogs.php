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
 * @version    0.01
 * @link       https://github.com/inigoflores/helium-miner-log-analyzer
  */

$logsFolder = './';

$logsFolders = [
        'Docker'         => '/var/log/miner/', //Also Controllino
        'Pisces P100'    => '/home/pi/hnt/miner/log/',
        'Sensecap M1'    => 'in /mnt/data/docker/volumes/xxxxxxx_miner-log/_data/', 
        'Milesight UG65' => '/mnt/mmcblk0p1/miner_data/log/', //adding it for future use,as PHP and opkg are missing (OpenWrt)
        'Panther X2'     => '/opt/panther-x2/miner_data/log/',
        'RHF2S308'       => '/opt/helium/miner_data/log/'
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
    $failedMaxRetry = 0;
    $failedIncomplete = 0;
    $failedUnkown = 0;

    $failedNotFound = 0;
    $failedTimeout = 0;
    $failedNoListenAddress = 0;
    $failedConRefused = 0;
    $failedHostUnreach = 0;

    $relayed = 0;
    $notRelayed = 0;

    foreach ($beacons as $beacon){

        // General Witnesses Overview
        if ($beacon['status']=='successfully sent') {
            $successful++;
        } else if ($beacon['status']=='failed max retry') {
            $failedMaxRetry++;
        } else if ($beacon['status']=='failed to dial' || $beacon['status']=='incomplete') {
            $failedIncomplete++;
        } else {
            $failedUnkown++;
        }

        // Failure Reasons
        if ($beacon['status']=='failed max retry') {
            if (!empty($beacon['reasonShort'])) {
                if ($beacon['reasonShort']=='not found') {
                    $failedNotFound++;
                } else if ($beacon['reasonShort']=='timeout') {
                    $failedTimeout++;
                } else if ($beacon['reasonShort']=='no listen address') {
                    $failedNoListenAddress++;
                } else if ($beacon['reasonShort']=='connection refused') {
                    $failedConRefused++;
                } else if ($beacon['reasonShort']=='host unreachable') {
                    $failedHostUnreach++;
                }
            }
        }

        //Relayed Challengers
        if (@$beacon['relayed'] == "yes") {
            $relayed++;
        } else if (@$beacon['relayed'] == "no") {
            $notRelayed++;
        }
    }

    $total = sizeOf($beacons);
    $totalFailed = $total - $successful;
    $totalPerHour = round($total / $intervalInHours,2);

    $totalFailedOther = $failedNoListenAddress +  $failedConRefused + $failedHostUnreach;

    $percentageSuccessful = round($successful/$total*100,2);
    $percentageFailed = round($totalFailed/$total*100,2);
    $percentageFailedMaxRetry = round($failedMaxRetry/$total*100,2);
    $percentageFailedIncomplete = round($failedIncomplete/$total*100,2);

    $percentageFailedNotFound = round($failedNotFound/$total*100,2);
    $percentageFailedTimeout = round($failedTimeout/$total*100,2);
    $percentageFailedOther = round($totalFailedOther/$total*100,2);


    $percentageNotRelayed = round($notRelayed/$total*100,2);
    $percentageRelayed = round($relayed/$total*100,2);
    $percentageRelayUnknown = round(($total-$notRelayed-$relayed)/$total*100,2);

    $output = "\nGeneral Witnesses Overview  \n";
    $output.= "----------------------------------\n";
    $output.= "Total witnesses                   = ". str_pad($total, 5, " ", STR_PAD_LEFT) .
        str_pad(" ({$totalPerHour}/hour)", 13, " ", STR_PAD_LEFT)  . "\n";
    $output.= "Succesfully delivered             = ". str_pad($successful, 5, " ", STR_PAD_LEFT) .
        str_pad("({$percentageSuccessful}%)", 9, " ", STR_PAD_LEFT)  . "\n";
    $output.= "Failed                            = ". str_pad($totalFailed, 5, " ", STR_PAD_LEFT) .
        str_pad("({$percentageFailed}%)", 9, " ", STR_PAD_LEFT) . " \n";
    $output.= "  ├── Max retry    = ". str_pad($failedMaxRetry, 4, " ", STR_PAD_LEFT) .
        str_pad("({$percentageFailedMaxRetry}%)", 9, " ", STR_PAD_LEFT) . " \n";
    $output.= "  └── Crash/reboot = ". str_pad($failedIncomplete, 4, " ", STR_PAD_LEFT) .
        str_pad("({$percentageFailedIncomplete}%)", 9, " ", STR_PAD_LEFT) . " \n";


    $output.= "\nMax Retry Failure Reasons \n";
    $output.= "----------------------------------\n";
    $output.= "Timeout                           = ". str_pad($failedTimeout, 5, " ", STR_PAD_LEFT) .
        str_pad("({$percentageFailedTimeout}%)", 9, " ", STR_PAD_LEFT) . " \n";
    $output.= "Not Found                         = ". str_pad($failedNotFound, 5, " ", STR_PAD_LEFT) .
        str_pad("({$percentageFailedNotFound}%)", 9, " ", STR_PAD_LEFT) . " \n";

    $output.= "Other challenger issues           = ". str_pad($totalFailedOther, 5, " ", STR_PAD_LEFT) .
        str_pad("({$percentageFailedOther}%)", 9, " ", STR_PAD_LEFT) . " \n";

    $output.= "\nChallengers \n";
    $output.= "----------------------------------\n";
    $output.= "Not Relayed                       = ". str_pad($notRelayed, 5, " ", STR_PAD_LEFT) .
        str_pad("({$percentageNotRelayed}%)", 9, " ", STR_PAD_LEFT) . " \n";
    $output.= "Relayed                           = ". str_pad($relayed, 5, " ", STR_PAD_LEFT) .
        str_pad("({$percentageRelayed}%)", 9, " ", STR_PAD_LEFT) . " \n";
    $output.= "Unknown (Probably Not Relayed)    = ". str_pad($total-$notRelayed-$relayed, 5, " ", STR_PAD_LEFT) .
        str_pad("({$percentageRelayUnknown}%)", 9, " ", STR_PAD_LEFT) . " \n";

    return $output;
}

/**
 * @param $beacons
 * @return string
 */
function generateList($beacons) {
    $output = "Date                    | Session     | RSSI | Freq  | SNR   | Noise  | Challenger                                           | Relay | Status            | Fails | Reason \n";
    $output.= "------------------------------------------------------------------------------------------------------------------------------------------------------------------------ \n";

    foreach ($beacons as $beacon){

        $rssi = str_pad($beacon['rssi'], 4, " ", STR_PAD_LEFT);
        $snr = str_pad($beacon['snr'], 5, " ", STR_PAD_LEFT);
        $noise = str_pad(number_format((float) ($beacon['rssi'] - $beacon['snr']),1),6,  " ", STR_PAD_LEFT);
        $status = str_pad($beacon['status'], 17, " ", STR_PAD_RIGHT);
        $failures = str_pad(empty($beacon['failures'])?0:$beacon['failures'], 5, " ", STR_PAD_LEFT);
        $challenger = @str_pad($beacon['challenger'],52, " ", STR_PAD_RIGHT);
        $relayed = @str_pad($beacon['relayed'],5, " ", STR_PAD_RIGHT);
        $reasonShort = @$beacon['reasonShort'];
        $reason = @$beacon['reason'];
        $session = str_pad($beacon['session'],11, " ", STR_PAD_LEFT);;

        $output.=@"{$beacon['datetime']} | {$session} | {$rssi} | {$beacon['freq']} | {$snr} | {$noise} | {$challenger} | $relayed | {$status} | {$failures} | {$reasonShort} \n";

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

            if (preg_match('/miner_onion_server:send_witness:{[0-9]+,[0-9]+} (?:re-)?sending witness at RSSI/', $line) ||
                preg_match('/miner_onion_server:send_witness:{[0-9]+,[0-9]+} failed to dial challenger/', $line) ||
                preg_match('/miner_onion_server:send_witness:{[0-9]+,[0-9]+} successfully sent witness to challenger/', $line) ||
                preg_match('/miner_onion_server:send_witness:{[0-9]+,[0-9]+} failed to send witness, max retry/', $line) ||
                preg_match('/libp2p_transport_relay:connect_to:{[0-9]+,[0-9]+} init relay transport with/', $line)
                )
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
                $status = "incomplete";
                $beacons[$session] = array_merge((array)@$beacons[$session], compact('datetime', 'session', 'rssi', 'freq', 'snr', 'status'));
                continue;
            }

            if (preg_match('/failed to dial challenger/', $line)) {
                $challenger = substr($fields[9], 6, -2);
                $reason = $fields[10];
                if (strpos($line,'p2p-circuit')){
                    $relayed = 'yes';
                } else {
                    $relayed = 'no';
                }

                switch (true) {
                    case strpos($reason,'not_found') !== FALSE:
                        $reasonShort = "not found";
                        break;
                    case strpos($reason,'timeout') !== FALSE:
                        $reasonShort = "timeout";
                        break;
                    case strpos($reason,'econnrefused') !== FALSE:
                        $reasonShort = "connection refused";
                        break;
                    case strpos($reason,',ehostunreach') !== FALSE:
                        $reasonShort = "host unreachable";
                        break;
                    case strpos($reason,'no_listen_addr') !== FALSE:
                        $reasonShort = "no listen address";
                        break;
                    default:
                        $reasonShort = "";
                };

                $failures = @$beacons[$session]['failures'] + 1;
                $status = "failed to dial";
                $beacons[$session] = array_merge((array)@$beacons[$session], compact('datetime', 'session', 'challenger', 'status', 'reason','reasonShort', 'relayed','failures'));
                continue;
            }

            if (preg_match('/successfully sent witness to challenger/', $line)) {
                $challenger = substr($fields[10], 6, -1);
                $rssi = str_pad(substr($fields[13], 0, -1), 4, " ", STR_PAD_LEFT);
                $freq = substr($fields[15], 0, -1);
                $snr = $fields[17];
                $status = "successfully sent";
                $reason = "";
                $reasonShort = "";
                $beacons[$session] = array_merge((array)@$beacons[$session], compact('datetime', 'session', 'challenger', 'rssi', 'freq', 'snr', 'status', 'reason','reasonShort'));
                continue;
            }

            if (preg_match('/failed to send witness, max retry/', $line)) {

                $status = "failed max retry";
                $beacons[$session] = array_merge((array)@$beacons[$session], compact('datetime', 'session', 'status'));
                continue;
            }

            if (preg_match('/init relay transport/', $line)) {
                $relayed = 'yes';
                $beacons[$session] = array_merge((array)@$beacons[$session], compact('relayed'));
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
    $columns = ['Date','Session','RSSI','Freq','SNR','Noise','Challenger','Relay','Status','Fails','Reason'];
    $data = array2csv($columns);
    foreach ($beacons as $beacon){
        $noise = number_format((float) ($beacon['rssi'] - $beacon['snr']),1);
        $failures = empty($beacon['failures'])?0:$beacon['failures'];
        $data.= @array2csv([
            $beacon['datetime'],$beacon['session'],$beacon['rssi'],
            $beacon['freq'],$beacon['snr'],$noise,$beacon['challenger'],
            $beacon['relayed'],$beacon['status'],$failures,$beacon['reasonShort']]);

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

