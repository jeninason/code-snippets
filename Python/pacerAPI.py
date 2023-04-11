
# DEV NOTES:
# login to PACER
# test pacer details
# qa url: https://qa-login.uscourts.gov/
# production url: pacer.login.uscourts.gov
# endpoint: /services/cso-auth
# run search
# store date results in csv (will change to database later)

import requests
import json
import os

def get_saved_token(file_path):
    if not os.path.exists(file_path):
        return None

    with open(file_path, 'r') as token_file:
        token = token_file.read().strip()

    if token:
        return token
    else:
        return None


def search(token, search_url, case_id, case_number_full, court_id, federal_bankruptcy_chapter, jurisdiction_type='bk', client_code=None):
    headers = {
        'Content-type': 'application/json',
        'X-NEXT-GEN-CSO': token,
        'Accept': 'application/json'
    }

    if client_code:
        headers['X-CLIENT-CODE'] = client_code

        request_body = {
        'jurisdictionType': jurisdiction_type,
        'caseId': case_id,
        'caseNumberFull': case_number_full,
        'courtId': court_id,
        'federalBankruptcyChapter': federal_bankruptcy_chapter
    }

    response = requests.post(search_url, headers=headers, data=json.dumps(request_body))

    if response.status_code == 200:
        search_result = response.json()
        print(search_result)
    else:
        print(f'Search request failed. Status code: {response.status_code}')

# Load credentials from config.json
with open('configPacer.json', 'r') as config_file:
    config = json.load(config_file)

username = config['username']
password = config['password']
client_code = config['client_code']

authentication_url = 'https://qa-login.uscourts.gov/services/cso-auth'
#authentication_url = 'https://pacer.login.uscourts.gov/services/cso-auth'
pcl_url = 'qa-pcl.uscourts.gov'
#pcl_url = 'pcl.uscourts.gov'

token_file_path = 'next_gen_cso_token.txt'

# Check if the token file exists and has a token
token = get_saved_token(token_file_path)

if token is None:
    headers = {
        'Content-type': 'application/json',
        'Accept': 'application/json'
    }

    request_body = {
        'loginId': username,
        'password': password,
        'clientCode': client_code,
        'redactFlag': 1
    }

    response = requests.post(authentication_url, headers=headers, data=json.dumps(request_body))

    # Check if the request was successful
    if response.status_code == 200:
        # Parse the JSON response and extract the nextGenCSO token
        response_json = response.json()
        next_gen_cso = response_json.get('nextGenCSO')
        #print(response.json())

        # Save the nextGenCSO token for future use
        with open('next_gen_cso_token.txt', 'w') as token_file:
            token_file.write(next_gen_cso)
        #print(next_gen_cso)

        # how do I tell if the token is expired?
        print('Authentication successful. NextGenCSO token saved in next_gen_cso_token.txt.')

        # Create a session and set the nextGenCSO token as a cookie
        session = requests.Session()
        session.cookies.set('nextGenCSO', next_gen_cso)

        # If client code is provided, set the PacerClientCode cookie
        if client_code:
            session.cookies.set('PacerClientCode', client_code)

        # Now you can use the session to make future requests with the necessary cookies
    else:
        print(f'Authentication failed. Status code: {response.status_code}')

else:
    print('Using existing token')
    
    # Set your search parameters
    case_id = 123456
    case_number_full = 'XX-YYYY-ZZZZZ'
    court_id = 'AA123'
    federal_bankruptcy_chapter = '7'

    search(token, pcl_url, case_id, case_number_full, court_id, federal_bankruptcy_chapter, client_code=client_code)


