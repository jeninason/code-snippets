import os 
from ftplib import FTP
import requests 
from requests.auth import HTTPBasicAuth

#THIS IS COMPLETELY UNTESTED, DO NOT COMMIT

# MailGun API key and domain
MAILGUN_API_KEY = 'YOUR_MAILGUN_API_KEY'
MAILGUN_DOMAIN = 'YOUR_MAILGUN_DOMAIN'

# FTP credentials
FTP_HOST = 'YOUR_FTP_HOST'
FTP_USERNAME = 'YOUR_FTP_USERNAME'
FTP_PASSWORD = 'YOUR_FTP_PASSWORD'

# Function to rename the file
def rename_file(filename, new_filename):
    os.rename(filename, new_filename)
    print(f'{filename} renamed to {new_filename}')

# Function to upload the file to FTP
def upload_file(filename, new_filename):
    with FTP(FTP_HOST, FTP_USERNAME, FTP_PASSWORD) as ftp:
        with open(new_filename, 'rb') as file:
            ftp.storbinary(f'STOR {filename}', file)
    print(f'{new_filename} uploaded to FTP')

# Function to send confirmation email using MailGun
def send_email(subject, message, recipient):
    response = requests.post(
        f'https://api.mailgun.net/v3/{MAILGUN_DOMAIN}/messages',
        auth=HTTPBasicAuth('api', MAILGUN_API_KEY),
        data={
            'from': f'Mailgun User <mailgun@{MAILGUN_DOMAIN}>',
            'to': recipient,
            'subject': subject,
            'text': message
        }
    )
    print(f'Email sent to {recipient}. Response: {response.status_code}')

# Accepting filename and numeric variable from user
filename = input('Enter filename: ')
numeric_var = int(input('Enter numeric variable: '))

# Renaming the file
new_filename = f'{numeric_var}_{filename}'
rename_file(filename, new_filename)

# Uploading the file to FTP
upload_file(filename, new_filename)

# Sending confirmation email
subject = 'File uploaded to FTP'
message = f'The file {filename} has been uploaded to FTP successfully.'
recipient = 'recipient@example.com'
send_email(subject, message, recipient)
