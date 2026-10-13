"""Run against a flat packaged deployment with Apache rewrites/overrides disabled."""
import http.cookiejar,os,re,urllib.request,urllib.parse,urllib.error
jar=http.cookiejar.CookieJar();client=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar));base=os.environ.get('BASE_URL','http://127.0.0.1:8080')
def url(path):return base+('/index.php?route='+urllib.parse.quote(path,safe='/') if not path.startswith('/assets/') and not path.startswith('/data/') else path)
def get(path):
 with client.open(url(path),timeout=10) as r:return r.read().decode()
def post(path,data):
 with client.open(urllib.request.Request(url(path),data=urllib.parse.urlencode(data).encode()),timeout=10) as r:return r.read().decode()
def csrf(body):return re.search(r'name="_csrf" value="([a-f0-9]+)"',body).group(1)
body=get('/install');assert 'name="database_host"' in body;assert 'setup.key' not in body
body=post('/install',{'_csrf':csrf(body),'database_host':'127.0.0.1','database_port':'3306','database_name':'simplemspdoc_apache_test','database_user':'root','database_password':'testing','username':'admin','name':'Apache Test','password':'Correct-password-123'})
assert 'Sign in' in body
body=post('/login',{'_csrf':csrf(body),'username':'admin','password':'Correct-password-123'});assert 'Organizations' in body
body=post('/organizations/save',{'_csrf':csrf(get('/organizations/new')),'name':'Apache Customer'});assert 'Apache Customer' in body
assert get('/assets/app.css').startswith(':root')
try:get('/data/config.php')
except urllib.error.HTTPError as e:assert e.code==404
else:raise AssertionError('Private configuration accessible')
print('Flat packaged Apache installation and file protection passed without rewrite rules or overrides.')
