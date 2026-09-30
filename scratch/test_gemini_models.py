import urllib.request
import json

models_to_test = [
    "gemini-2.5-flash",
    "gemini-2.0-flash",
    "gemini-1.5-flash",
    "gemini-2.5-pro",
    "gemini-1.5-pro"
]

print("Testing Gemini model names against v1beta API...")
for m in models_to_test:
    url = f"https://generativelanguage.googleapis.com/v1beta/models/{m}"
    try:
        req = urllib.request.Request(url)
        with urllib.request.urlopen(req) as response:
            print(f"Model {m}: SUCCESS (200)")
    except Exception as e:
        print(f"Model {m}: {e}")
