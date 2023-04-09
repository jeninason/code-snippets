import requests
import os

# Make an API request to get the APOD data
response = requests.get("https://api.nasa.gov/planetary/apod?api_key=DEMO_KEY")
data = response.json()

# Get the URL of the image from the APOD data
image_url = data['url']

# Download the image and save it to a file
image_response = requests.get(image_url)
image_file = open("apod.jpg", "wb") #add path here too
image_file.write(image_response.content)
image_file.close()
