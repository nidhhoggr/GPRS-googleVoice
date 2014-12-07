# GPRS-Google Voice

 >Author: Joseph Persie | Version: 0.1

The purpose of the repository is to house multiple libraries and useful scripts related to interfacing a GPRS shield and Google voice.

###SMSForwarder

this is a script that connects to a GRPS shield through USB serial port. The script checks for any new SMS text messages and saves them to a database. to process the SMS records the script utilizes the google translate API to download an MP3 containing the message in the SMS record and plays the record using Mplayer. After the record is played the record is updated as is_sent(boolean).

#####TODO

* add functionality to store phone number and keep a record of a name associated to the phone number
* In the MP3 for the text message the voice will provide who texted you what such as "Bob said [contents of sms message]" when the phone number is associated to a user name bob
* When a SMS message is recieved an SMS message will be sent asking for a name if the number doesn't already have an associated name

##### Installation

* Create a database named GPRS and configure the database username and password. Thats it!

```sh
mysql> CREATE DATABASE gprs;
```

* You need to install mapler to play the MP3's

```sh
$ sudo apt-get install mplayey
```

##### Usage

Plugin in your device with USB serial. The default baud rate is 9600.If another baud rate is desired you will have to explicity configure in the SMSForwarder instantion with a thrid parameter on the gprs script.

1) Send a text message to your Device

2) probe the shield for the new SMS message

```sh
$ ./gprs -d /dev/ttyACMO
```

3) probe the shield again. This time an MP3 will be played containing the latest SMS text message

```sh
$ ./gprs -d /dev/ttyACMO
```

###GoogleVoice

##### Usage

```sh
$ ./googleVoice
```

#####LINKS
* GPRS Shield Documentation
http://www.seeedstudio.com/wiki/GPRS_Shield_V1.0
* SIM900  AT Commands Manual
http://www.seeedstudio.com/wiki/images/a/a8/SIM900_AT_Command_Manual_V1.03.pdf
* Youtube videos coming soon

