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

## Downloading

You can clone the repository with:

    git clone https://github.com/inigoflores/helium-miner-log-analyzer

Or download the tool with:

    wget -O processlogs.php https://raw.githubusercontent.com/inigoflores/helium-miner-log-analyzer/main/processlogs.php; chmod a+x processlogs.php
    

## Tool usage

    $ ./processlogs.php [-a] [-l] [-s MMMM-dd-yy] [-e MMMM-dd-yy] [-p /FULL/PATH/TO/LOGS]


    Options

            -a      Show witness statistics

            -l      Show witness list 

            -s      Specify a start date in MMMM-dd-yy format      

            -e      Specify an end date in MMMM-dd-yy format

            -p      Specify a full path to the miner logs folder


## Examples

### Show the stats for all the log files

    $ ./processlogs.php
    
    Using logs in folder /home/pi/hnt/miner/log/


    General Witnesses Overview
    ----------------------------------
    Total witnesses                   =   176
    Succesfully delivered             =   129  (73.3%)
    Failed                            =    47  (26.7%)
    ├── Max retry    =   37 (21.02%)
    └── Crash/reboot =   10  (5.68%)
    
    Max Retry Failure Reasons
    ----------------------------------
    Timeout                           =    31 (83.78%)
    Not Found                         =     5 (13.51%)
    Other challenger issues           =     1   (2.7%)
    
    Challengers
    ----------------------------------
    Not Relayed                       =   139 (78.98%)
    Relayed                           =    27 (15.34%)
    Unknown Relay Status              =    10  (5.68%)


### Show list of all witnesses between two dates

    $ ./processlogs.php -l -s 2022-02-07 -e 2022-02-08
    
    Using logs in folder /home/pi/hnt/miner/log/
    
    Date                    | Session    | RSSI | Freq  | SNR   | Challenger                                           | Relay | Status            | Fails | Reason
    -------------------------------------------------------------------------------------------------------------------------------------------------------------- 
    2022-02-07 00:05:34.025 |  0.29017.0 |  -86 | 867.3 |   6.0 | 11niYWTvewEMcdPK59Tazqhv4kCZ3Gpoe3yVZnfRgY9pmzML8ky  | yes   | incomplete        |     7 | timeout
    2022-02-07 00:36:28.908 |    0.615.1 | -133 | 867.1 | -22.0 | 11gRRMR29BW78XitZf6cs7y3gBmwupx4eoSSNQs3EziUSMsbmUX  | no    | incomplete        |     4 | no listen address
    2022-02-07 00:45:16.905 |   0.1576.1 | -108 | 868.1 |   0.2 | 11UvTXVboFiqtHMMxsf5oEyAhj8B3qsBjKkATR5ePN6XofHocai  | no    | successfully sent |     1 |  
    2022-02-07 01:35:01.192 |   0.8380.1 | -132 | 867.9 | -21.8 | 112F2QZoZegus7vLVPUkMDUtxYBm6nTjqdJyo3LPwxNoZRMi6sCM | no    | successfully sent |     1 |  
    2022-02-07 01:38:39.908 |   0.8782.1 | -121 | 867.3 | -15.5 | 11FeRqqXFKRBoN6XJVP4ANYBFfNKCWGgd9grPHo9pUp5q1fK8QJ  | no    | successfully sent |     1 |  
    2022-02-07 01:39:20.252 |   0.8825.1 | -113 | 867.9 |  -2.0 | 112KZA9reNWQnq9qhG5PMn7x9f2p9dc35LNCkiBcD5gjb7UghYog | no    | successfully sent |     1 |  
    2022-02-07 01:47:17.255 |   0.9732.1 | -114 | 868.3 |  -7.8 | 11zR51xa3snWKK3MvMTuqxdMd2pEBr5fi1aoTh5opWQoGpdkZ2n  | no    | successfully sent |     1 |  
    2022-02-07 02:46:57.971 |  0.17587.1 | -124 | 867.7 | -12.5 | 1122TC5BqLdnm1EQwtkigTYF6iwpdQTkvsa2MkkBzvkfaQPimUhN | no    | successfully sent |     0 |  
    2022-02-07 02:55:54.617 |  0.18484.1 | -130 | 867.5 | -18.8 | 11pU9oyLcCRm52vaoXYaeux42iBBuZpZQLHE9VpmKmfsTZsQE6i  | no    | successfully sent |     1 |  
    2022-02-07 03:03:50.745 |  0.18766.1 | -119 | 867.5 | -12.8 | 11SSEQrNh4HFyDZQVbHgN37gY93JhcmZ41QSL9VRmFGk6CKe6vd  | no    | failed max retry  |    10 | timeout
    2022-02-07 03:23:37.592 |  0.21872.1 | -133 | 867.7 | -22.2 | 115Pygt41v6Q62v4v9SwN7kZqznnWK9KUzDkhghwKobqVdrqSSS  | no    | successfully sent |     0 |  
    2022-02-07 03:39:11.445 |  0.23706.1 | -134 | 867.7 | -22.8 | 11hbSHhDpxF8q24sQHXsv6hhduoEvSV78sZwuASz3L2aLcan3BS  | no    | successfully sent |     2 |  
    2022-02-07 03:40:09.606 |  0.22970.1 | -129 | 867.9 | -17.0 | 112B5gsKJQNakrcbXjE7Q4eJ3hvnSqzVUjNaSACqbxoyM6KtNDba | yes   | failed max retry  |    10 | timeout
    2022-02-07 04:16:57.890 |  0.27833.1 | -124 | 867.7 | -12.0 | 112nPUEawDi68p2P17uCMCGqTwqZEVibZYAZj8QU7s45anemEQJw |       | incomplete        |     1 | not found
    2022-02-07 04:23:03.160 |  0.27676.1 | -130 | 867.7 | -19.2 | 112nfNdbiVqFq7UspMcWFTe732T4bQ8zLGcxPa47c2ecBX8x2mdZ | yes   | failed max retry  |    10 | timeout
    2022-02-07 04:28:04.779 |  0.29002.1 | -127 | 867.1 | -15.8 | 119urtr1g1mf8Hwp2zZY9ac67Bx7U9FxWR8yA3abDJm1VHRZJ5j  | no    | successfully sent |     0 |  
    2022-02-07 04:34:54.667 |  0.29737.1 | -111 | 867.1 |  -1.2 | 1128adyYt7fGn6RuGctLyLmk8jQeSmjvGoRFvHkbm4Cv1P6BhAGD | no    | successfully sent |     0 |  
    2022-02-07 04:41:31.663 |  0.29558.1 | -126 | 867.7 | -14.8 | 112WoqNjPxUe5H8kv3aR6WVUKccAToBVEAmtETyzHdtyc6LsoEm7 | yes   | failed max retry  |    10 | timeout
    2022-02-07 04:55:18.510 |  0.32320.1 | -113 | 867.9 |  -2.0 | 112eejjn6T4F9QwonttAR7ytEeTFta7LBuNRDaUAeKdUE4t9Q5pJ | no    | successfully sent |     2 |  
    2022-02-07 15:13:12.859 |  0.16228.6 | -130 | 867.5 | -19.2 | 11DMBKhcZyRqxKNJTRM87jYiuXn9ZvUs9g4oyPFtRgyffZ3EoZz  | yes   | failed max retry  |    10 | timeout
    2022-02-07 15:13:29.813 |  0.17080.6 | -118 | 868.1 | -10.5 | 11DYhps5kpkoQWrKqsQk2qp8pGQSznr7nG1tZZiXpZX23Q13BaY  | no    | successfully sent |     0 |  
    2022-02-07 15:15:49.157 |  0.17463.6 | -108 | 868.1 |  -1.2 | 11v7GAnsegKafk2xRLPBe5c23dJdgyNtqVRdL7jNKNTVpUd47RP  | no    | successfully sent |     0 |  
    2022-02-07 15:41:10.539 |  0.19480.6 | -120 | 868.1 | -12.5 | 11cg19DSHHq4CH47j13KZgBNKLAk1XyJvXymmBzdL1uhnuhhmkB  | yes   | failed max retry  |    10 | timeout
    2022-02-07 15:52:15.475 |  0.20238.6 | -127 | 867.7 | -16.2 | 112Tqn6sXrtq6y7rquCgWDXqUyUhURZEieKrUi54Rbyd54hjN3ho | yes   | failed max retry  |    10 | timeout
    2022-02-07 16:03:33.531 |  0.22011.6 | -108 | 867.7 |   0.0 | 11ksFDK45UKQpQBDD8GXxCen6tGiYN2TAkBPHzgS5bP2MrZbeua  | no    | successfully sent |     1 |  
    2022-02-07 16:11:21.482 |  0.22942.6 | -115 | 867.1 |  -6.5 | 11zxT6uTmuUWpF6syQ4TivUQBXidCcoBAb73nTyJQprkv34b1Fd  | no    | successfully sent |     1 |  
    2022-02-07 16:23:15.755 |  0.24723.6 | -113 | 868.3 | -10.5 | 112rCjPi82RrfX9nrp4v6kboTnybxtzEqJuoCWh7pGCWS77dtx7W | no    | successfully sent |     0 |  
    2022-02-07 16:53:39.602 |  0.28278.6 | -128 | 867.7 | -16.5 | 1126rKBPY9JVW7yUuT5JmnScsRES9Gx74Yp4DdXKcB6FR8V6UP3C | no    | successfully sent |     0 |  
    2022-02-07 17:28:21.098 |  0.32485.6 | -134 | 867.7 | -22.0 | 11E7hUwqQ19eWWK8swEH1x1qYmZ2zDovfhde4xGZEb5LArJwRRH  | no    | successfully sent |     1 |  
    2022-02-07 17:29:36.457 |  0.31650.6 | -104 | 868.5 | -19.2 | 112RD81VjLFxf5MCcQ6TC8mxrnK7G7tq3mQDXAFWNTmTmx4p7RAu | yes   | failed max retry  |    10 | timeout
    2022-02-07 17:57:16.654 |   0.3148.7 | -130 | 867.5 | -19.5 | 11DQJRHXQb2HsTsUm3ypWdaK3v8pg1bCZ5fao1f3jnFWtZeKWeV  | no    | successfully sent |     0 |  
    2022-02-07 18:03:36.011 |   0.3170.7 | -119 | 868.3 | -14.2 | 112bzG9JD48TNiZWu44EiC7ptw8QeGRzRFoo6az7jus3VFD38PwQ | yes   | failed max retry  |    10 | timeout
    2022-02-07 18:13:02.536 |   0.5087.7 | -125 | 867.9 | -14.2 | 112nQV6DjmfwkieWWjgVsx3HQft6HoripXPWEhdoeZGSPsntdbGo | no    | successfully sent |     0 |  
    2022-02-07 18:36:11.809 |   0.7057.7 | -120 | 868.3 | -12.8 | 112mcdkg1pXXf9z82gt1UjCdFaBuUrfZ3DXy1NM3xHzMeGmoLT3d | yes   | failed max retry  |    10 | timeout
    2022-02-07 18:40:04.299 |   0.7562.7 |  -98 | 867.7 |   4.5 | 112XXXPsNtmAPDjsGK6ep5zFrn9gquUpSA642XxTMFscHzCrAgWF | no    | failed max retry  |    10 | timeout
    2022-02-07 19:29:06.344 |  0.14484.7 | -125 | 867.7 | -13.2 | 11xcryDafroaWNY37tmbMwSTmjuY9vCaZewfZRbUohqzSKHeGKy  | no    | successfully sent |     2 |  
    2022-02-07 19:46:40.182 |  0.16224.7 | -126 | 868.3 | -20.2 | 11266VEDsHYoKYqZgrKH8XUM63rM7a4Qw3tR7hvYhnqZM2H78KSC | no    | successfully sent |     1 |  
    2022-02-07 19:58:57.512 |  0.17917.7 | -127 | 867.9 | -15.5 | 11M9sQ4PSoHr9KkjBdB3EKwfQeD3nadEPJso2593Ay7786SxQD8  | no    | successfully sent |     0 |  
    2022-02-07 20:09:17.051 |  0.19104.7 | -128 | 867.7 | -16.8 | 112VV6TV2UU6FRsrEBdLQRZUnVQsQFMtnSFPTJ4QXw4U517WU4AT | no    | successfully sent |     1 |  
    2022-02-07 21:39:50.709 |  0.31786.7 |  -98 | 867.7 |   7.0 | 11YWNBxXXFK3s63Wz5L6Z6uoZ6DHGb1DRnF5nVigfhj9sqiiYWw  | no    | successfully sent |     0 |  
    2022-02-07 21:48:47.593 |  0.32329.7 | -127 | 868.3 | -18.8 | 1121tjTVJsfBUtxH5NNzjG1hA52Q7Wr62GmpnQkJa4Pf8GCdFT2N | no    | successfully sent |     3 |  
    2022-02-07 21:49:49.562 |     0.46.8 | -127 | 868.3 | -18.8 | 112BGNwn3SVCyNWfZTkyDUuLXRysnpycgLrjtdJKjDVCpuf2d6eq | no    | successfully sent |     0 |  
    2022-02-07 22:06:06.238 |   0.2695.8 | -125 | 867.9 | -12.5 | 112aetNrJVQXKNyXL7jdKG7qRKzBoEadhKMfK9BTRY4sFJtknXaZ | no    | successfully sent |     0 |  
    2022-02-07 22:30:25.674 |   0.5418.8 |  -55 | 867.1 |   8.0 | 11QhFjuehz8QWLRn5qR9RjmDFxou6XpDQmwZzDgDJzMiC5sKLnV  | no    | successfully sent |     0 |  
    2022-02-07 23:14:10.411 |  0.10702.8 | -133 | 867.1 | -22.2 | 11pqVvosA8dDAVUSbNNLLNozjQshEscVzrnz2LTCzbaNkziMACw  | no    | successfully sent |     1 |  
    2022-02-07 23:41:39.322 |  0.13323.8 | -123 | 867.5 | -11.5 | 112Af5GCqsXsqaE4AWwiB5D5ARi9s8GwGrGB4h8dDdMiqTHteUUi | no    | successfully sent |     2 |  


## To Do

* Add support form miners that don't have the log folders from the docker image mapped to the host.
* Add option to export to CSV.
* Run as a service and store data in a local database.