#!/usr/bin/env python
"""
This script was written by Gregory Bolet for the Instructables Community

Description:
	This program uploads the specified folder to Google Drive storage.
    It zips the folder into one file using system calls, then uploads the file.
"""

from __future__ import print_function
import httplib2
import os
import time
from apiclient import discovery
from apiclient.http import MediaFileUpload
from oauth2client import client
from oauth2client import tools
from oauth2client.file import Storage
from oauth2client.service_account import ServiceAccountCredentials


try:
    import argparse
    flags = argparse.ArgumentParser(parents=[tools.argparser]).parse_args()
except ImportError:
    flags = None

#####################################  PREDEFINES  #####################################
########################################################################################
#Scroll down to main() to see what is happening...

#folder name on drive, if not present: will create new one
GDRIVE_FOLDER_NAME = "my_sample_folder"

#The directory of the folder that you would like to backup
#Example: This is zipping the 'apparmor' folder
#Zipped file gets placed in the same directory as this script
#Zipped fil will be deleted after being uploaded
FOLDER_TO_ZIP_DIRECTORY= "/lib/apparmor"

#Email address of recipient of folder to be shared
#Can currently only share with one email address per call
#Make sure you spell it correctly
NOTIFICATION_EMIAL_ADDRESS = "wjp@frontlineprocessing.com"

#This tells Google what API service you are trying to use (We are using drive)
#If you are backing up to gdrive, don't change this
SCOPES = 'https://www.googleapis.com/auth/drive'

#This points to the JSON key file for the service account
#You should have downloaded the file when creating your service account
#It should be in the same directory as this script
KEY_FILE_NAME = 'service_secret.json' 
########################################################################################
########################################################################################

def callback(request_id, response, exception):
    if exception:
        # Handle error
        print(exception)
    else:
        print("Permission Id: %s" % response.get('id'))


def get_service():
    """Get a service that communicates to a Google API.
    Returns:
      A service that is connected to the specified API.
    """
    print("Acquiring credentials...")
    credentials = ServiceAccountCredentials.from_json_keyfile_name(filename=KEY_FILE_NAME, scopes=SCOPES)

    #Has to check the credentials with the Google servers
    print("Authorizing...")
    http = credentials.authorize(httplib2.Http())

    # Build the service object for use with any API
    print("Acquiring service...")
    service = discovery.build(serviceName="drive", version="v3", http=http, credentials=credentials)

    print("Service acquired!\n")
    return service

def getIDfromName(service, name):
    """Gets the first item with the specified name in the Google Drive
    and returns its unique ID
	Returns:
		itemID, the unique ID for said G-Drive item name provided
		None (null value), if file not found
    """
    print("Looking for item with name of: "+name)
    results = service.files().list(q="name='"+name+"'", pageSize=1, fields="files(id, name)").execute()
    items = results.get('files', [])
    if not items:
        print("Item not found...\n")
        return None
    itemID = items[0]['id']
    print("Acquired item id: "+itemID+" for item called: "+items[0]['name']+"\n")
    return itemID

def createNewFolder(service,  name):
    """Will create a new folder in the root of the supplied GDrive
    Returns:
        The new folder ID, or the id of the already existing folder
    """
    folderID = getIDfromName(service=service, name=name)
    if folderID is not None:
        return folderID

    folder_metadata = {
        'name' : name,
        'mimeType' : 'application/vnd.google-apps.folder'
    }

    folder = service.files().create(body=folder_metadata, fields='id, name').execute()
    folderID = folder.get('id')
    print('Folder Creation Complete')
    print('Folder Name: %s' % folder.get('name'))
    print('Folder ID: %s \n' % folder.get('id'))
    return folderID

def getTimestampLabel():
	"""Creates and returns a legible timestamp string;
    used for naming the files and folders
	"""
	return "BACKUP_"+time.strftime("%x").replace("/","-")+"_"+time.strftime("%X")+"_"+str(int(round(time.time())))

def createNewTimestampedFolder(service):
    """Creates a folder with time-stamped name
    """
    name = getTimestampLabel()
    createNewFolder(service=service, name=name)

def uploadFileToFolder(service, folderID, fileName):
	"""Uploads the file to the specified folder id on the said Google Drive
	Returns:
		fileID, A string of the ID from the uploaded file
	"""
	print("Uploading file to: "+folderID)
	file_metadata = {
  		'name' : fileName,
  		'parents': [ folderID ]
	}
	media = MediaFileUpload(fileName, resumable=True)
	file = service.files().create(body=file_metadata, media_body=media, fields='name,id').execute()
	fileID = file.get('id')
	print('File ID: %s ' % fileID)
	print('File Name: %s \n' % file.get('name'))

	return fileID

def createZIPfileBackup(directory):
	"""Creates a ZIP file in the current working directory
	Uses system function calls because they are easier
	Returns:
		zipFileName, the name of the zip file that was created
	"""
	print("Creating ZIP folder to upload...")
	fileName = getTimestampLabel()
	zipFileName = fileName+".zip"
	os.system("zip -r "+zipFileName+" "+directory)
	print("ZIP folder created: "+zipFileName+"\n")
	return zipFileName

def removeZIP(zipFileName):
	"""Gets rid of the zip folder we created
	Uses system function calls because they are easier
	"""
	print("Removing ZIP folder from system...")
	os.system("rm "+zipFileName)
	print("ZIP file "+zipFileName+" removed from local system! :D\n")

def shareFileWithEmail(service, fileID, emailAddress):
    """Shares the specified file via email
    Grants 'writer' privileges by default, which allows
    one to delete the contents of the folder, but not the folder itself
    """
    print("Sharing file with email: "+emailAddress)
    batch = service.new_batch_http_request(callback=callback)
    user_permission = {
        'type': 'user',
        'role': 'writer',
        'emailAddress': emailAddress
    }
    batch.add(service.permissions().create(
        fileId=fileID,
        body=user_permission,
        fields='id',
    ))
    batch.execute()
    print("Sharing complete!\n")

def main():
    print("Starting filesystem backup...\n")
    service = get_service()

    #get the folder id where we will store the backups
    folderID = createNewFolder(service=service,name=GDRIVE_FOLDER_NAME)

    #create the ZIP file of said directory
    zipFileName = createZIPfileBackup(FOLDER_TO_ZIP_DIRECTORY)

    #upload the file to the Google Drive Folder
    uploadFileToFolder(service=service, folderID=folderID, fileName=zipFileName)

    #remove the zip that was created in this files' same directory
    removeZIP(zipFileName = zipFileName)

    #shares the folder in which the backup is located
    #serves as a notification that a backup is complete
    shareFileWithEmail(service=service, fileID=folderID, emailAddress=NOTIFICATION_EMIAL_ADDRESS)

    print("Filesystem backup complete...\n")

if __name__ == '__main__':
    main()
