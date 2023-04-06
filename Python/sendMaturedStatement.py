import os
import ftplib
from mailgun.api import Mailgun

# UNTESTED, DO NOT COMMIT

# Vars needed
oldPath = "/path/to/old"
newPath = "/path/to/new"
newName = "new_file_name"
ftpUrl = "ftp.example.com"
ftpUser = "ftp_username"
ftpPwd = "ftp_password"
mailgunApi = "MAILGUN_API_KEY"
mailgunDomain = "MAILGUN_DOMAIN"
emailFrom = "from@example.com"
emailTo = ["to@example.com"]
emailCC = ["cc@example.com"]
emailSubject = "File uploaded to FTP"
emailMessage = "The file {0} has been uploaded to FTP successfully. Page count: {1}"

# Function to print usage instructions
def usage():
    print("\n\npython script.py #pagecount <fileNameInDownloadFolder>\n\n")

# Get script arguments
script = os.path.basename(__file__)
pagecount = int(sys.argv[1])
file = sys.argv[2]

oldFile = os.path.join(oldPath, file)
print("\n" + str(pagecount))
print("\n" + oldFile)

if not os.path.isfile(oldFile):
    usage()
    exit()

sent = False

# FTP the file
with ftplib.FTP(ftpUrl, ftpUser, ftpPwd) as ftp:
    ftp.set_pasv(True)
    finalPath = os.path.join(newPath, newName)
    os.rename(oldFile, finalPath)
    remoteName = os.path.basename(finalPath)
    with open(finalPath, 'rb') as file:
        if ftp.storbinary(f'STOR {remoteName}', file):
            sent = True
            print(f'Uploaded {finalPath} to FTP server')
        else:
            print(f'Error uploading {remoteName} to FTP server')

# Email confirmation with page count
if sent:
    mg = Mailgun(mailgunApi)
    mg.send_email(
        emailFrom,
        emailTo,
        emailSubject,
        emailMessage.format(newName, pagecount),
        cc=emailCC,
        domain=mailgunDomain
    )
    print("Email sent")
else:
    print("File not sent, something went wrong with ftp")
