import urllib.request, json, mimetypes, uuid, os
BASE='http://127.0.0.1:8000/api'
def req(method,url,headers=None,data=None):
    r=urllib.request.Request(url,data=data,headers=headers or {},method=method)
    try:
        with urllib.request.urlopen(r) as resp:
            return resp.status, resp.read().decode()
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode()

# login
data=json.dumps({'email':'admin@example.com','password':'password'}).encode()
s,b=req('POST',f'{BASE}/auth/login',{'Content-Type':'application/json','Accept':'application/json'},data)
print('LOGIN', s)
tok=json.loads(b)['data']['token']
H={'Authorization':f'Bearer {tok}','Accept':'application/json'}

# get project 2
s,b=req('GET',f'{BASE}/admin/projects/2',H)
print('GET P2', s)
proj=json.loads(b)['data']
print('title=',proj['title'],'cat=',proj.get('category',{}).get('id'),'images=',len(proj.get('images',[])))
for img in proj.get('images',[]):
    print('  img id=',img['id'],'sort=',img['sort_order'],'type=',img.get('type'))

# build multipart update keeping all existing images (no new uploads, no removals)
boundary='----b'+uuid.uuid4().hex
def fld(n,v):
    return f'--{boundary}\r\nContent-Disposition: form-data; name="{n}"\r\n\r\n{v}\r\n'.encode()
body=b''
body+=fld('title',proj['title']+' updated')
body+=fld('description',proj.get('description') or 'd')
body+=fld('content',proj.get('content') or '<p>x</p>')
body+=fld('featured','1')
body+=fld('category_id',str(proj['category']['id']))
for i,img in enumerate(proj.get('images',[])):
    body+=fld(f'existing_images[{i}][id]',str(img['id']))
    body+=fld(f'existing_images[{i}][sort_order]',str(i+1))
    body+=fld(f'existing_images[{i}][alt_text]',img.get('alt_text') or '')
    body+=fld(f'existing_images[{i}][title]',img.get('title') or '')
body+=f'--{boundary}--\r\n'.encode()
H2={**H,'Content-Type':f'multipart/form-data; boundary={boundary}','Origin':'http://localhost:3000'}
s,b=req('POST',f'{BASE}/admin/projects/2',H2,body)
print('UPDATE P2', s)
print(b[:1500])
