import time
import numpy as np
import cv2
import imutils
import requests
import json
import argparse
from datetime import datetime

ap = argparse.ArgumentParser()
ap.add_argument("-k", "--key",
	help="key for post requests", default='password')
ap.add_argument("-r", "--room_id", default='id',
	help="the id for the room")
args = vars(ap.parse_args())

# post request information
url = "YOUR POST URL"
headers = {'Content-type': 'application/json'}

# grab camera capture
camera = cv2.VideoCapture("recorded_video4.mp4")

# people counters
totalCount = 0
inCount = 0
outCount = 0

# allow the camera to warmup
time.sleep(0.1)

# setup background subraction
bgSub = cv2.BackgroundSubtractorMOG2()

# booleans for zone used for counting
zone1 = False
zone2 = False
zone3 = False
resetIn = False
resetOut = False
timerCount = 0

skipCounter = 0  # counter used to skip first inital frames

# capture frames from the camera
while(True):
    # grab frame from camera
    f, image = camera.read()

    # skip first frame that produce noise
    while(skipCounter < 40):
        f, image = camera.read()
        skipCounter = skipCounter + 1

    # draw lines for all the counting zones
    cv2.line(image, (0, 75), (320, 75), (0, 255, 0), 1)
    cv2.line(image, (0, 115), (320, 115), (0, 0, 255), 1)
    cv2.line(image, (0, 155), (320, 155), (0, 0, 255), 1)
    cv2.line(image, (0, 195), (320, 195), (0, 255, 0), 1)
    cv2.line(image, (117, 0), (117, 480), (255, 0, 0), 2)

    # apply blur and background subtraction
    mask = cv2.GaussianBlur(image, (21, 21), 0)
    mask = bgSub.apply(mask)

    # apply threshold
    ret, mask = cv2.threshold(mask, 20, 255, cv2.THRESH_BINARY)

    # erode and dilate image to reduce noise and fill object
    mask = cv2.erode(mask, None, iterations=2)
    mask = cv2.dilate(mask, None, iterations=2)

    # find the contours/blob of objects
    contours, h = cv2.findContours(mask, cv2.RETR_TREE, cv2.CHAIN_APPROX_SIMPLE)

    aCount = 0

	# loop through objects
    for m in contours:
		# ignore the first few caused by noise
        if timerCount <= 4:
            timerCount += 1
            continue

        if aCount >= 1:
            continue

		# ignore objects that have a small area
        if cv2.contourArea(m) < 2000:
            continue

		# determine size and point of object
        (x, y, w, h) = cv2.boundingRect(m)

		# ignore object that don't fit criteria of a person
        if w > 140 and w < 70 and h > 140 and h < 70 and h > w * 2.75:
            continue

        aCount += 1

		# calculate center of object
        center = (x + (w / 2), y + (h / 2))

		# ignore objects that cast shadow on door
        if center[0] < 117:
            continue

		# if object goes through the zone set flags for counting
        if center[1] > 75 and center[1] < 115:
            resetOut = True
        if center[1] >= 115 and center[1] < 125:
            zone1 = True
        if center[1] >= 125 and center[1] < 143:
            zone2 = True
        if center[1] >= 145 and center[1] < 155:
            zone3 = True
        if center[1] >= 155 and center[1] < 195:
            resetIn = True

		# determine if person just left the room
        if (resetOut) and (zone1 or zone2 or zone3):
            totalCount += 1
            outCount += 1
			# reset zones
            zone1 = False
            zone2 = False
            zone3 = False
            resetOut = False
            resetIn = False
        elif (resetIn) and (zone1 or zone2 or zone3):
            totalCount += 1
            inCount += 1
			# reset zones
            zone1 = False
            zone2 = False
            zone3 = False
            resetOut = False
            resetIn = False
    	else:
            resetOut = False
            resetIn = False

		# display number of object in the image
        cv2.putText(image, "{}".format(aCount), center,
                    cv2.FONT_HERSHEY_SIMPLEX, 0.65, (0, 255, 0), 2)

	# display total, in, out count of people in the room
    cv2.putText(image, "total: {}".format(inCount - outCount), (10, 30),
                cv2.FONT_HERSHEY_SIMPLEX, 0.65, (0, 255, 0), 2)
    cv2.putText(image, "In: {}".format(inCount), (10, 60),
                cv2.FONT_HERSHEY_SIMPLEX, 0.65, (0, 255, 0), 2)
    cv2.putText(image, "Out: {}".format(outCount), (10, 90),
                cv2.FONT_HERSHEY_SIMPLEX, 0.65, (0, 255, 0), 2)
    prevCount = totalCount

	# show a window with the video feed
    cv2.imshow("OG", image)

	# when user presses 'q' exit the application
    key = cv2.waitKey(1) & 0xFF
    if key == ord("q"):
        break

crowd = inCount - outCount;
currDate = datetime.now().strftime("%Y-%m-%d")
currTime = datetime.now().strftime("%H:%M:%S")
data = {"crowd" : crowd, "date" : currDate, "time" : currTime, "password" : "password"}
r = requests.patch(url, data=json.dumps(data), headers=headers)
print r
# cleanup
camera.release()
cv2.destroyAllWindows()
