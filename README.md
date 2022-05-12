# Helium Miner Logs Analyzer

Small tool that extracts witness data from Helium miner logs.

**Note**: This is the stripped-down version that works with light hotspots. With the activation of HIP55, hotspots no longer generate challenges. This task has now been assigned to validators. Therefore, the amount of data stored in the logs has been greatly reduced. 
For reference, the old version can be accessed at the [pre-hip55 branch](https://github.com/inigoflores/helium-miner-log-analyzer/tree/pre-hip55). 

This code has been tested for miner version `miner-arm64_2022.05.10.0_GA `. 

It runs out of the box for the following miners:

* Controllino
* Panther X2
* Pisces P100
* Rising HF RHF2S308
* HeliumDIY

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

    $ ./processlogs.php [-a] [-l] [-s YYYY-MM-DD] [-e YYYY-MM-DD] [-p /FULL/PATH/TO/LOGS]  [-c[FILENAME.CSV]]


    Options

            -a      Show witness statistics

            -l      Show witness list 

            -s      Specify a start date in YYYY-MM-DD format      

            -e      Specify an end date in YYYY-MM-DD format

            -p      Specify a full path to the miner logs folder

            -c      Create CSV. If no filename is provided, it outputs to stdout


## Examples

### Show the stats for all the log files

    $ ./processlogs.php
    
    Using logs in folder /home/pi/hnt/miner/log/ 
    
    General Witnesses Overview  
    ----------------------------------
    Total witnesses                   =    14  (1.04/hour)
    Succesfully delivered             =    12 (85.71%)
    Failed                            =     2 (14.29%)
  
### Show list of all witnesses between two dates

    $ ./processlogs.php  -l -s 2022-05-11 -e 2022-05-13

    Using logs in folder /var/log/miner/
    
    Date                | Freq  | RSSI | SNR   | Noise  | Status
    ------------------------------------------------------------- 
    11-05-2022 23:24:08 | 867.3 | -111 |   2.8 | -113.8 | successfully sent  
    12-05-2022 00:05:24 | 867.1 | -110 |   5.2 | -115.2 | successfully sent  
    12-05-2022 01:48:13 | 867.1 | -122 |  -2.8 | -119.2 | successfully sent  
    12-05-2022 04:15:59 | 867.7 | -131 | -12.0 | -119.0 | successfully sent  
    12-05-2022 05:35:58 | 867.5 | -101 |   6.0 | -107.0 | failed             
    12-05-2022 06:19:20 | 867.5 | -113 |   0.5 | -113.5 | successfully sent  
    12-05-2022 07:30:39 | 868.1 | -122 | -16.5 | -105.5 | successfully sent  
    12-05-2022 07:55:56 | 867.1 | -105 |   9.0 | -114.0 | successfully sent  
    12-05-2022 08:30:52 | 867.1 | -130 | -12.0 | -118.0 | successfully sent  
    12-05-2022 09:33:34 | 867.1 | -114 |  -1.5 | -112.5 | failed             
    12-05-2022 11:39:04 | 868.3 |  -13 |   9.8 |  -22.8 | successfully sent  
    12-05-2022 11:55:51 | 867.7 | -120 |  -2.5 | -117.5 | successfully sent  
    12-05-2022 12:31:59 | 867.7 | -129 | -11.0 | -118.0 | successfully sent  
    12-05-2022 12:50:58 | 867.1 | -109 |   5.0 | -114.0 | successfully sent


### Export to CSV

    $ ./processlogs.php -cwitnesses.csv 
    Data saved to witnesses.csv

