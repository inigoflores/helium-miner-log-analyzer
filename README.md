# Helium Miner Logs Analyzer

Small tool that extracts witness data from Helium miner logs.

It currently works for miner version `miner-arm64_2022.01.29.0_GA`. 

It runs out of the box for the following miners:

* Controllino
* Panther X2
* Pisces P100
* Sensecap M1

It should work for other miners that have the logs folder mapped to the host, for which you will need to provide the folder path as a command-line argument.

If you want me to add your miner, please create an issue and provide me with the full path to the console.log folder.

Alternatively, you can run the tool inside docker.

## Requirements

The script needs PHP 7 to run. On Debian based miners you can install it with:

    sudo apt install php-cli

If you are unable to install PHP at the miner OS, you should be able to do so inside docker. You can access the container with:

    sudo docker exec -it miner sh   

You may need to replace "miner" with the name of the docker container running the Helium miner. 

Once inside the docker container, run the following to install PHP.

    apk add php-cli 

Note that any changes to the running docker container are not persistent and will be lost at the next reboot.


## Downloading

You can clone the repository with:

    git clone https://github.com/inigoflores/helium-miner-log-analyzer

Or download the tool with:

    wget -O processlogs.php https://raw.githubusercontent.com/inigoflores/helium-miner-log-analyzer/main/processlogs.php; chmod a+x processlogs.php


## Tool usage

    $ ./processlogs.php [-a] [-l] [-s YYYY-MM-DD] [-e YYYY-MM-DD] [-p /FULL/PATH/TO/LOGS]


    Options

            -a      Show witness statistics

            -l      Show witness list 

            -s      Specify a start date in YYYY-MM-DD format      

            -e      Specify an end date in YYYY-MM-DD format

            -p      Specify a full path to the miner logs folder


## Examples

### Show the stats for all the log files

    $ ./processlogs.php
    
    Using logs in folder /home/pi/hnt/miner/log/ 
    
    General Witnesses Overview
    ----------------------------------
    Total witnesses                   =   263
    Succesfully delivered             =   225 (85.55%)
    Failed                            =    38 (14.45%)
    ├── Max retry    =   38 (14.45%)
    └── Crash/reboot =    0     (0%)
    
    Max Retry Failure Reasons
    ----------------------------------
    Timeout                           =    29 (11.03%)
    Not Found                         =     1  (0.38%)
    Other challenger issues           =     8  (3.04%)
    
    Challengers
    ----------------------------------
    Not Relayed                       =   244 (92.78%)
    Relayed                           =    19  (7.22%)
    Unknown Relay Status              =     0     (0%)


### Show list of all witnesses between two dates

    $ ./processlogs.php -l -s 2022-02-20 -e 2022-02-21 
    
    Using logs in folder /home/pi/hnt/miner/log/
    
    Date                    | Session    | RSSI | Freq  | SNR   | Noise  | Challenger                                           | Relay | Status            | Fails | Reason
    -------------------------------------------------------------------------------------------------------------------------------------------------------------- 
    2022-02-20 04:19:11.348 | 0.25975.45 | -114 | 867.1 |  -1.8 | -112.2 | 11znrX7MGqUVnd2k3vGdLX91LAckZkqkFaL6i4aoWN2cd8E3Mk5  | no    | successfully sent |     1 |
    2022-02-20 04:49:31.411 | 0.31449.45 | -100 | 867.9 |   4.8 | -104.8 | 11Gex6jFZfiYkoPQ87op9SGECSKGydZzSCz7kkywK5CG48V8tUS  | no    | successfully sent |     1 |
    2022-02-20 05:02:13.911 |  0.1032.46 | -105 | 868.1 |   2.2 | -107.2 | 112c1TTd7k7Lmwpjdx3GdPTpxk5iyLaJuiJc3a8jN54CHPuwASyE | no    | successfully sent |     1 |
    2022-02-20 05:22:36.083 |  0.4394.46 | -133 | 867.7 | -19.0 | -114.0 | 112biVDpww7iWqgXp5MGRd43G2XKNbFVor8ZiyFn6wd7cJPvXLE2 | no    | successfully sent |     4 |
    2022-02-20 05:38:09.854 |  0.7595.46 | -124 | 867.3 | -18.5 | -105.5 | 119o3eRSBrMthjGJKbrsZzs78dtdQuBN1oH5L7R5oGSY619maqd  | no    | successfully sent |     0 |
    2022-02-20 06:09:41.288 |  0.6097.46 | -128 | 867.7 | -14.0 | -114.0 | 112uoTfKktCUTBhqohr2K1PUgGg5Nb3YqDjH9hYPCpt4xHnzjSRn | no    | successfully sent |     1 |
    2022-02-20 06:20:46.867 |  0.1481.46 | -105 | 867.7 |   4.5 | -109.5 | 11PyLymSPsn4ksChXDjU29HcZ5Vn1z4W3DrNe5EAqyLWVqhYx8D  | no    | successfully sent |     0 |
    2022-02-20 06:24:20.695 | 0.14977.46 | -108 | 868.1 |   2.0 | -110.0 | 1121X95UuHpS5Bk2LSUtwWo6GefMzU6YyRHBke9F9RrcUvNT2s63 | no    | successfully sent |     0 |
    2022-02-20 06:51:10.031 | 0.19652.46 | -128 | 867.1 | -15.0 | -113.0 | 112rW9WtJr43877AZP5r5tAQEg23KDikWNyZeXEtkmnm8dzmJqPx | no    | successfully sent |     0 |
    2022-02-20 06:54:08.130 | 0.19916.46 | -119 | 868.1 | -23.8 |  -95.2 | 112TZqXZmbUHs6CH24ifCY2FwzBGWcEmiADdLbV7Zt9ami6rEJyx | no    | successfully sent |     0 |
    2022-02-20 06:56:22.342 | 0.20257.46 | -125 | 867.9 | -10.2 | -114.8 | 11sguTvedrPY9VvdW3KqY8rYW3WgvG9KvSTu7tvkediiAnUacfJ  | no    | successfully sent |     1 |
    2022-02-20 07:01:23.343 | 0.21164.46 | -120 | 867.3 | -13.5 | -106.5 | 112mJqMfqYzG8dpp47fv6vnJgPTEsmoopiJc3KzzwgrJeeHEydAb | no    | successfully sent |     1 |
    2022-02-20 07:34:28.668 | 0.25319.46 | -135 | 867.1 | -21.0 | -114.0 | 11LEnQCBWkFW53FtfcppHdxSdUENvxZCXPyupDSFfZvzvqgbNRx  | no    | failed max retry  |    10 | timeout
    2022-02-20 07:35:27.100 | 0.26440.46 | -118 | 867.3 |  -8.8 | -109.2 | 11m28NPmPnQqj69zXUVQUbykgo7zTMnyuYmrVfx7bKoJZRLbeds  | no    | successfully sent |     0 |
    2022-02-20 07:38:16.972 | 0.27228.46 |  -93 | 867.3 |  -9.2 |  -83.8 | 11c7fu69LWdW7gobTQhsQzr7rgAWMoG5Y99iToxpAwRxHYKumUf  | no    | successfully sent |     0 |
    2022-02-20 07:38:46.537 | 0.27179.46 |  -19 | 868.3 |   9.2 |  -28.2 | 11c7fu69LWdW7gobTQhsQzr7rgAWMoG5Y99iToxpAwRxHYKumUf  | no    | successfully sent |     1 |
    2022-02-20 07:50:04.167 | 0.28432.46 | -115 | 868.1 |  -5.0 | -110.0 | 11BJmGHrJ8p5WiUQLyWi5zRzVaMF9LZXeBrGymZkwU1ZrbBDSbA  | no    | failed max retry  |    10 | connection refused
    2022-02-20 07:50:47.934 | 0.27968.46 | -128 | 867.3 | -19.2 | -108.8 | 112N8Me8mzB8ReUWrtkJUknm5jtE2HopuAXY6KkQC6N1cBb5YybZ | no    | successfully sent |     1 |
    2022-02-20 08:08:56.199 | 0.32135.46 | -110 | 867.3 |   0.8 | -110.8 | 112b2XhAfFbqfSWMJWRBtWiQmr3hy2J9wtLX6Jb8Ma61H67Jr8ih | no    | successfully sent |     0 |
    2022-02-20 08:25:49.989 |  0.2352.47 |  -19 | 867.7 |   8.2 |  -27.2 | 11vuHDVp6B6hi5Tv3mV286sCjcANuHGxwNTEwHuFvPtGH1JcN45  | no    | successfully sent |     0 |
    2022-02-20 08:27:39.903 |  0.2293.47 | -130 | 867.3 | -18.2 | -111.8 | 11jJ5pCEZ2z6aW8nzvWHgWVp5Q3Ny4UbXSNQXhddb8pNHgYSJZM  | no    | successfully sent |     1 |
    2022-02-20 08:30:56.630 |  0.1001.47 | -137 | 867.7 | -23.0 | -114.0 | 11G8n7hdnCjXHSYuTTuUzXgDDwFXiVXrt57PN5m2zUBmn8DXY3y  | no    | failed max retry  |    10 | no listen address
    2022-02-20 08:35:37.009 |  0.2591.47 | -102 | 867.1 |   3.0 | -105.0 | 1123jR8U5ohTxPeYiPKrjCfmxzSJASEEFAT4q7JuSspfcBp211ZG | no    | successfully sent |     0 |
    2022-02-20 08:39:45.705 |  0.3716.47 | -123 | 868.1 | -13.2 | -109.8 | 11V33zM7tq5JGR3ZsR6WbHgTFCfPmJfu6xukAXfG6YPjXsJVWD2  | no    | successfully sent |     1 |
    2022-02-20 08:40:54.342 |   0.817.47 | -113 | 867.1 |  -0.5 | -112.5 | 112jkcPTa1cqAcTjsYyGr7ikmhnhoJdZgcD1Ho2dmSeCcAyNZwVw | no    | successfully sent |     1 |
    2022-02-20 08:48:43.159 |  0.5583.47 | -131 | 867.5 | -20.0 | -111.0 | 1128iApaSAAFfdCWMtXNY5oiexmGG9enw3oNmohKu1R5fzd9VzQf | no    | successfully sent |     0 |
    2022-02-20 08:49:13.328 |  0.5586.47 | -115 | 868.3 |  -7.5 | -107.5 | 11252SfKNhTcQjYbpoywFHeYUML8C6LQP11fXES4zyuwha6vodgn | no    | successfully sent |     1 |
    2022-02-20 09:20:21.776 | 0.11383.47 | -133 | 867.3 | -24.5 | -108.5 | 112HCrae8bb94PbABVCqgBwcnVdcpbsi9V7SDHKUsjiSQnyikDLr | no    | successfully sent |     1 |
    2022-02-20 09:26:42.491 | 0.13453.47 |  -69 | 867.3 |   7.2 |  -76.2 | 11wptzrZdYV3pZzG3XSKHcLRFNx3GFfJ3pvvzNijbpsU5LcbSdW  | no    | successfully sent |     1 |
    2022-02-20 09:34:08.566 | 0.14213.47 | -107 | 867.5 |  -1.8 | -105.2 | 112hGkwtk1J15JcsSZSMaEa9WeL5mcZicM3RaJHtEqhmZE9bV8iY | no    | successfully sent |     1 |
    2022-02-20 09:48:45.665 | 0.14942.47 | -129 | 867.9 | -14.0 | -115.0 | 112Z2Z9UvDESgx6Bg2ZVbonWuHFinbp2XGRLANLCdoXyc3uTEEih | no    | failed max retry  |    10 | timeout
    2022-02-20 09:53:15.698 | 0.17435.47 | -127 | 867.5 | -13.0 | -114.0 | 112J8NsUXn11dRMmS9yvp2G9LGqEaGAgZnmp2R3PKv7pbr2XiTtd | no    | successfully sent |     1 |
    2022-02-20 10:56:23.532 | 0.27998.47 | -112 | 867.7 |   0.5 | -112.5 | 1122t3Z4ERK3CdSBnVVTj3sLkzWfhjMDRzsUrWknXmzm1Xn2gQoz | no    | successfully sent |     0 |
    2022-02-20 11:26:50.735 | 0.19570.47 | -128 | 868.3 | -19.2 | -108.8 | 112nHRtEZ1z5gbBtquU9WLQVsLNdiprWRT44G6L9wU2NoFV3VCFo | yes   | failed max retry  |    10 | timeout
    2022-02-20 11:40:33.536 |  0.1662.48 | -120 | 867.9 |  -5.8 | -114.2 | 11aa6p7q12rRp9gNtWQ84xddYy7UKSaRshCQ3EE1ByoyUC9jg14  | yes   | failed max retry  |    10 | timeout
    2022-02-20 11:44:13.400 |  0.3442.48 | -135 | 867.3 | -22.8 | -112.2 | 112VkmtFD6CtWyaj6Qk1wwQobSEWLDhruY2j8dM8oKwbjYHG9mNC | no    | successfully sent |     0 |
    2022-02-20 12:30:24.369 | 0.10811.48 | -132 | 867.7 | -19.2 | -112.8 | 1149GZrwT3JfyLusW8EE1VLAdLMGT6ExstJkLp4yCTA6f8VnkMy  | no    | successfully sent |     2 |
    2022-02-20 12:58:20.550 |  0.8020.48 | -120 | 867.9 |  -7.2 | -112.8 | 112jjLnbMsrqTWKHCbpKbZN7hFW9sLox9toQYKUD8hRD9eoX1knx | no    | successfully sent |     1 |
    2022-02-20 13:10:05.479 | 0.17528.48 | -106 | 868.1 |   1.5 | -107.5 | 112azQheNgRwwWJztykdygnz9TDCjJXfpBtr7S99jsk4yhiXnDge | no    | failed max retry  |    10 | timeout
    2022-02-20 13:14:22.705 | 0.14186.48 | -127 | 867.5 | -19.8 | -107.2 | 1128danF2LZfKKNaRuHgWEr2PmqWXYzhLsTdBTsYjqPYzd2bYdbF | no    | failed max retry  |    10 | timeout
    2022-02-20 13:20:52.881 | 0.20242.48 | -128 | 867.3 | -18.2 | -109.8 | 112Cech1D9waERW1toMjZsJWmtPCm8oAdAiKgp4es3rQ75vZSLTj | no    | successfully sent |     1 |
    2022-02-20 13:30:21.538 | 0.19200.48 | -116 | 867.1 |  -3.8 | -112.2 | 112n7s9VJDjYgL4VeNTWYowyvZiChVLwDScndpdiBLPu9SAgtnuC | no    | successfully sent |     1 |

## To Do

* Add option to export to CSV.
* Run as a service and store data in a local database.


## Understanding lost witnesses

Only a fraction of the beacons witnessed by the miner make it to the blockchain.

Reasons:

* **Witnesses lost at the local miner** bacause of failure of delivery to the challenger due to:<br/><br/>
  * **P2P or network errors**. These include:<br/><br/>
    * **Timeout**, usually due to a relayed challenger.
    * Challenger P2P address **not found** in the peer book.
    * Other challenger related problems, like "no listen address", "host unreachable" or "connection refused".<br/><br/>
  * **Miner crashes/reboots**.<br/><br/>
* **Witnesses lost at the challenger**: After successfuly delivering the witness to the challenger, the transaction fails to complete due to P2P or network problems at the challenger. In such cases, at Helium Explorer All Activity tab, the challenger shows a `Constructed Challenge` event, but not a `Challenged Beaconer` event.<br/><br/>
* **Witnesses lost at the "max hotspots lottery"**: When the number of hotspots that witness a beacon exceed 14 (current `poc_max_witness_per_hop`), only 14 are selected to receive rewards.

This tool lets you analyze the causes for the witnesses lost at the local miner.