from flask import Flask, request

# Initialize Flask app
app = Flask(__name__)

# Your custom verification token
VERIFY_TOKEN = "agrachat_test"

@app.route('/webhook', methods=['GET'])
def verify():
    """
    Verifies the webhook with Facebook during the setup.
    Facebook sends a GET request with hub.verify_token and hub.challenge.
    If the token matches, respond with hub.challenge.
    """
    token = request.args.get('hub.verify_token')  # Extract verify token
    challenge = request.args.get('hub.challenge')  # Extract challenge
    if token == VERIFY_TOKEN:
        return challenge, 200  # Respond with the challenge value
    return "Forbidden", 403  # Invalid token

@app.route('/webhook', methods=['POST'])
def handle_webhook():
    """
    Handles incoming webhook events from Facebook.
    Logs the data for debugging or further processing.
    """
    data = request.json  # Parse incoming JSON payload
    print("Received Webhook Event:", data)

    # Add your processing logic here (optional)

    return "OK", 200  # Respond with "OK" to acknowledge receipt

if __name__ == "__main__":
    # Run the Flask app on port 5000
    app.run(host='0.0.0.0', port=5000)
