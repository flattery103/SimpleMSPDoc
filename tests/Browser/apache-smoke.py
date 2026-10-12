"""Run against a fresh packaged Apache deployment prepared by CI."""
import http.cookiejar, os, re, urllib.request, urllib.parse
jar=http.cookiejar.CookieJar(); client=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar)); base=os.environ.get('BASE_URL','http://127.0.0.1:8080')
def get(path):
 with client.open(base+path,timeout=10) as r:return r.read().decode()
def post(path,data):
 with client.open(urllib.request.Request(base+path,data=urllib.parse.urlencode(data).encode()),timeout=10) as r:return r.read().decode()
def csrf(body):return re.search(r'name="_csrf" value="([a-f0-9]+)"',body).group(1)
body=get('/install');assert 'Fresh installation' in body
token=open(os.environ['SETUP_TOKEN_FILE']).read().strip().split(': ',1)[1]
body=post('/install',{'_csrf':csrf(body),'setup_token':token,'database_host':'127.0.0.1','database_port':'3306','database_name':'simplemspdoc_apache_test','database_user':'root','database_password':'testing','username':'admin','name':'Apache Test','password':'Correct-password-123'})
assert 'Sign in' in body
body=post('/login',{'_csrf':csrf(body),'username':'admin','password':'Correct-password-123'})
assert 'Organizations' in body
body=post('/organizations/save',{'_csrf':csrf(get('/organizations/new')),'name':'Apache Customer'})
assert 'Apache Customer' in body
assert 'Server recovery' not in body
assert get('/assets/app.css').startswith(':root')
for path in ['/config/local.php','/vendor/autoload.php','/install.php']:
 try:get(path)
 except urllib.error.HTTPError as e:assert e.code in (403,404)
 else:raise AssertionError('Private or legacy path accessible: '+path)
print('Packaged Apache fresh installation, rewrite routing, login, forms and private paths passed.')
