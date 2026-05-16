import os
from dotenv import load_dotenv

basedir = os.path.abspath(os.path.dirname(__file__))
load_dotenv(os.path.join(basedir, '.env'))

class Config:
    SECRET_KEY = os.environ.get('SECRET_KEY') or 'dev-key-change-in-production'
    SQLALCHEMY_DATABASE_URI = os.environ.get('DATABASE_URL') or \
        'sqlite:///' + os.path.join(basedir, 'instance', 'database.db')
    SQLALCHEMY_TRACK_MODIFICATIONS = False
    
    # API Settings
    API_TIMEOUT = 5  # seconds
    API_RETRY_COUNT = 1
    
    # Encryption key for API keys (32 url-safe base64-encoded bytes)
    # Generate in production using: cryptography.fernet.Fernet.generate_key()
    ENCRYPTION_KEY = os.environ.get('ENCRYPTION_KEY') or b'2jV_4P-B0fUvBv7B1f7aL6O1Q0lH7y3gC1x5A8o8rI4='
    
    # Logging
    LOG_FILE = os.path.join(basedir, 'logs', 'app.log')
    LOG_LEVEL = 'INFO'
