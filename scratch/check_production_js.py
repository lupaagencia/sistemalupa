import urllib.request
import ssl
import re

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

url = "https://5.9.79.107"
req = urllib.request.Request(url, headers={'Host': 'empaqueslupa.com'})
html = urllib.request.urlopen(req, context=ctx).read().decode('utf-8')
print("HTML length:", len(html))

js_matches = re.findall(r'src=["\']([^"\']+\.js)', html)
print("JS files found:", js_matches)

for js in js_matches:
    if js.startswith('/'):
        js_url = url + js
    elif js.startswith('http'):
        js_url = js
    else:
        js_url = url + '/' + js
    
    try:
        req_js = urllib.request.Request(js_url, headers={'Host': 'empaqueslupa.com'})
        content = urllib.request.urlopen(req_js, context=ctx).read().decode('utf-8')
        has_slider = 'slider/publicos' in content
        print(f"File: {js} | Len: {len(content)} | Contains 'slider/publicos': {has_slider}")
    except Exception as e:
        print(f"File: {js} | Error: {e}")
