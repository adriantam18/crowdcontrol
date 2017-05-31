# Crowd Control

Crowd Control is a system that tracks the crowdedness of any indoor area and delivers the information to its users. The motivation behind the project is to  allow users to determine how crowded an area is before going to it. 
    
# Installation
## A. OpenShift

1. Create a PHP 7 application on OpenShift (When choosing a type of application, search for PHP 7)
2. Add MySQL 5.5 and phpMyAdmin 4.0 to your newly created application
3. Create a database using phpMyAdmin and import tables into your database using [company.sql](Web/Data/company.sql), [branch.sql](Web/Data/branch.sql), and [room.sql](Web/Data/room.sql) 
4. Clone the created git repo to your local machine using the url given to you on the application page
5. Copy the files in this repo's [Web](Web) folder to the www folder of your OpenShift's local git repo then commit and push those changes to the remote repo on OpenShift
        
## B. Raspberry Pi 2
1. You need the following libraries: numpy, json, imutils, picamera, OpenCV 3.0
2. In [crowdcontrol.py](Pi-Client/crowdcontrol.py), you have to change the values of the following placeholders: 'password', 'id', "YOUR PATCH URL", "recorded_video4.mp4", password value in json data {"crowd"...:"password"}
3. Run on your Raspberry Pi 2 or local machine (python crowdcontrol.py)
        
## C. Mobile Apps
1. iOS: Open workspace in Xcode, select iPhone 5s from drop down menu and press the play button
2. Android: Using Android Studio, build the project and run on your phone. In [ServiceGenerator.java](Mobile%20Apps/Android/CrowdControl/app/src/main/java/adriantam18/crowdcontrol/CrowdControlAPI/ServiceGenerator.java) replace API_BASE_URL with your url. You must sign up for a google maps api key and register your app if you want the app to be location-aware
        
# For a Basic Demo:
1. Add an entry for a company, branch, and room in your database (Can be your house, it doesn't matter). For the room's password just put in anything you want. You will use that password in [crowdcontrol.py](Pi-Client/crowdcontrol.py)
2. In the update function of [RoomPdoRepository.php](Web/Data/RoomPdoRepository.php), comment out the password check
3. In [crowdcontrol.py](Pi-Client/crowdcontrol.py), change the following: all password value -> password you created before, "YOUR POST URL" -> your url, "recorded_video4.mp4" -> filename of video you recorded. Finally, run [crowdcontrol.py](Pi-Client/crowdcontrol.py)
4. Check your app or database for the updated values

# Team Members:
* Tenzin Choeden
* Michael Loconte
* Robert Ozimek
* Adrian Tamayo
