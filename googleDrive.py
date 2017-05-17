import os
import sys

import json

from httplib2 import Http

from oauth2client.service_account import ServiceAccountCredentials
from apiclient.discovery import build

# If modifying these scopes, delete your previously saved credentials
# at ~/.credentials/drive-python-CLI.json
scopes = ['https://www.googleapis.com/auth/drive']

credentials = ServiceAccountCredentials.from_json_keyfile_name(
    'service_secret.json', scopes)

def upload(content, name, location, service):
    file_metadata = {
      'name' : name,
      'parents': [ location ]
    }
    media = MediaFileUpload('files/photo.jpg',
                            mimetype='image/jpeg',
                            resumable=True)
    file = service.files().create(body=file_metadata,
                                        media_body=media,
                                        fields='id').execute()
    print('Folder ID: {}'.format(file.get('id')))

def createFolder(name, location, service):
    file_metadata = {
      'name' : name,
      'mimeType' : 'application/vnd.google-apps.folder',
      'parents' : [location]
    }
    file = service.files().create(body=file_metadata,
                                        fields='id').execute()
    print('Folder ID: {}'.format(file.get('id')))

def main(argv):
    """Shows basic usage of the Google Drive API.

    Creates a Google Drive API service object and outputs the names and IDs
    for up to 10 files.
    """
    name = argv[1]
    print(name)

    createFolder('0000', '0B7PSHsdd0u-CcThjazNVMnZ5Wms', service)

service = build('drive', 'v3', credentials)
if __name__ == '__main__':
    main(sys.argv)
