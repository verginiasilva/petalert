#!/usr/bin/env python
import json
import os
import sys

import requests


def main():
    try:
        raw = sys.stdin.read()
        data = json.loads(raw or '{}')
        message = data.get('message') or data.get('mensagem')
        model = 'gemini-1.5-flash'

        if not message:
            print(json.dumps({"error": "Mensagem não informada"}))
            sys.exit(1)

        api_key = os.getenv('GOOGLE_API_KEY') or os.getenv('GEMINI_API_KEY') or ''
        url = f"https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent?key={api_key}"

        payload = {
            "contents": [
                {
                    "parts": [
                        {"text": message}
                    ]
                }
            ]
        }

        response = requests.post(url, json=payload, headers={'Content-Type': 'application/json'}, timeout=60)
        result = response.json()

        if response.status_code >= 400:
            print(json.dumps({"error": result.get('error', response.text)}))
            sys.exit(1)

        reply = result.get('candidates', [{}])[0].get('content', {}).get('parts', [{}])[0].get('text')
        if not reply:
            print(json.dumps({"error": "Resposta vazia da API do Gemini"}))
            sys.exit(1)

        print(json.dumps({"reply": reply}))

    except Exception as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)


if __name__ == '__main__':
    main()
