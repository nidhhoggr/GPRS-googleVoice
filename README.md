# GPRS-Goole Voice

> Author: Joseph Persie
> Version: $id$

The purpose of the repository is to house multiple libraries and useful scripts related to interfacing a GPRS shield and goofle voice.

###SMSForwarder

this is a script that connects to a GRPS shield through USB serial port. The script checks for any new SMS text messages and saves them to a database. to process the SMS records the script utilizes the google translate API to download an MP3 containing the message in the SMS record and plays the record using Mplayer. After the record is played the record is updated as is_sent(boolean).

#####TODO

* add functionality to store phone number and keep a record of a name associated to the phone number
* In the MP3 for the text message the voice will provide who texted you what such as "Bob said [contents of sms message]" when the phone number is associated to a user name bob
* When a SMS message is recieved an SMS message will be sent asking for a name if the number doesn't already have an associated name

##### Installation

* Create a database named GPRS and configure the database username and password. Thats it!

```sh
$ mysql> CREATE DATABASE gprs;
```

* sudo apt-get install mplayer

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




