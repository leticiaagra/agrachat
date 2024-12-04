from flask import Flask, request, jsonify

app = Flask(__name__)

VERIFY_TOKEN = "your_verify_token"

@app.route('/webhook', methods=['GET', 'POST'])
def webhook():
    if request.method == 'GET':
        # Verification handshake with Facebook
        if request.args.get('hub.verify_token') == VERIFY_TOKEN:
            return request.args.get('hub.challenge'), 200
        return "Forbidden", 403
    elif request.method == 'POST':
        # Handle incoming webhook events
        data = request.json
        print("Received Webhook Data:", data)
        # Process the data (e.g., respond to comments or DMs)
        return "OK", 200

if __name__ == "__main__":
    app.run(port=5000)