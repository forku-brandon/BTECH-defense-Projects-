from cryptography.fernet import Fernet
from flask import current_app

def get_fernet():
    key = current_app.config['ENCRYPTION_KEY']
    return Fernet(key)

def encrypt_api_key(plain_text):
    if not plain_text:
        return None
    f = get_fernet()
    return f.encrypt(plain_text.encode('utf-8')).decode('utf-8')

def decrypt_api_key(cipher_text):
    if not cipher_text:
        return None
    f = get_fernet()
    return f.decrypt(cipher_text.encode('utf-8')).decode('utf-8')
