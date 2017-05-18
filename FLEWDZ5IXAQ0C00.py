#!/usr/bin/env python
"""
This script was written by Gregory Bolet for the Instructables Community

Description:
        This program was written to serve as a starting point for those
    experimenting with the Google Drive API for Python with a
    service account
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

#folder name on gdrive, will create a new folder every time even if already present...
GDRIVE_FOLDER_NAME = "my_sample_folder"

#A file in the system, preferrably in the same directory as this script
FILE_NAME = "someFile.txt"

#Email address of recipient of folder to be shared
#Can currently only share with one email address per call
#Make sure you spell it correctly
NOTIFICATION_EMIAL_ADDRESS = "flpmarketing1@gmail.com"

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

def createNewFolder(service,  name):
    """Will create a new folder in the root of the supplied GDrive,
    doesn't check if a folder with same name already exists.
    Retruns:
        The id of the newly created folder
    """
    folder_metadata = {
        'name' : name,
        'mimeType' : 'application/vnd.google-apps.folder'
    }
    folder = service.files().create(body=folder_metadata, fields='id, name').execute()
    print('Folder Creation Complete')
    folderID = folder.get('id')
    print('Folder Name: %s' % folder.get('name'))
    print('Folder ID: %s \n' % folderID)
    return folderID

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

    print("Service acquired!")
    return service

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

def uploadFileToFolder(service, folderID, fileName):
    """Uploads the file to the specified folder id on the said Google Drive
    Returns:
            fileID, A string of the ID from the uploaded file
    """
    file_metadata = None
    if folderID is None:
        file_metadata = {
            'name' : fileName
        }
    else:
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

def uploadFile(service, fileName):
    """Uploads the file to the root directory on the said Google Drive
    Returns:
            fileID, A string of the ID from the uploaded file
    """
    return uploadFileToFolder(service=service, folderID=None, fileName=fileName)


def main():
    print("This is an example script for working with a Google Drive API service account\n")

    #get the service object using the credentials file
    service = get_service()

    #creates a new folder
    folderID = createNewFolder(service=service,name=GDRIVE_FOLDER_NAME)

    #uploads a file to the specified GDrive folder
    uploadFileToFolder(service=service, folderID=folderID, fileName=FILE_NAME)

    #shares the folder via email to said recipient
    shareFileWithEmail(service=service, fileID=folderID, emailAddress=NOTIFICATION_EMIAL_ADDRESS)

    print("Requested operations complete. Exiting...\n")

if __name__ == '__main__':
    main()
