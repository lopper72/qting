import requests

# Login first
login = requests.post('https://hongbiennhanh.xyz/admin-api/v1/login', json={'username':'admin','password':'123456'})
print('Login Status:', login.status_code)
token = login.json()['data']['token']
headers = {'Authorization': token}

# Test upload endpoint with real file
with open('test_upload.txt', 'rb') as f:
    files = {'file': ('test_upload.txt', f, 'text/plain')}
    r = requests.post('https://hongbiennhanh.xyz/admin-api/v1/upload', headers=headers, files=files)
print('Upload Status:', r.status_code)
print('Upload Response:', r.text)
