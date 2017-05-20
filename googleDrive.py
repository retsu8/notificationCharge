import json
import sys
from apiclient.discovery import build
from httplib2 import Http
from oauth2client.service_account import ServiceAccountCredentials
from apiclient.http import MediaFileUpload


scopes = ['https://www.googleapis.com/auth/drive']

credentials = ServiceAccountCredentials.from_json_keyfile_name(
    'service_secret.json', scopes)
delegated_credentials = credentials.create_delegated('phpcli@even-blueprint-161416.iam.gserviceaccount.com')
http_auth = credentials.authorize(Http())
service = build('drive','v3',  credentials=credentials)

def upload(content, name, location):
    file_metadata = {
      'name' : name,
      'parents': [ location ]
    }
    print(location)
    media = MediaFileUpload(location, mimetype='application/pdf')
    file = service.files().create(body=file_metadata,media_body=media,fields='id').execute()
    print(file.get('id'))
    return(file.get('id'))

def createFolder(name, location):
    file_metadata = {
      'name' : name,
      'mimeType' : 'application/vnd.google-apps.folder',
      'parents' : [location]
    }
    file = service.files().create(body=file_metadata,fields='id').execute()
    print(file.get('id'))
    return(file.get('id'))

def searchDrive(name, location):
    page_token = None
    while True:
        response = service.files().list(q="mimeType='application/vnd.google-apps.folder',"+location+" in parents",
                                             spaces='drive',
                                             fields='nextPageToken, files(id, name)',
                                             pageToken=page_token).execute()
        for file in response.get('files', []):
            # Process change
            print 'Found file: %s (%s)' % (file.get('name'), file.get('id'))
        page_token = response.get('nextPageToken', None)
        if page_token is None:
            break;
def main(argv):
    """Shows basic usage of the Google Drive API.

    Creates a Google Drive API service object and outputs the names and IDs
    for up to 10 files.
    """
    print ', '.join(argv)
    todo = argv[1]
    name = argv[2]
    location = argv[3]
    if(len(argv) > 4):
        print(argv[4])
        content = open(argv[4], "r")
    print(name)

    if(todo == '0'):
        createFolder(name, location)
    elif(todo == '1'):
        upload(content, name, location)
    elif(todo == '2'):
        searchDrive(name, location)
    else:
        print("Invalid todo needs a todo to to-do")
if __name__ == '__main__':
    main(sys.argv)
