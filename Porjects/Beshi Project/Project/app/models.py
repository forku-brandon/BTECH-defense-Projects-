from app import db, login_manager
from flask_login import UserMixin
from werkzeug.security import generate_password_hash, check_password_hash
from datetime import datetime
import json

@login_manager.user_loader
def load_user(id):
    return User.query.get(int(id))

class User(db.Model, UserMixin):
    __tablename__ = 'users'
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(64), index=True, unique=True, nullable=False)
    password_hash = db.Column(db.String(128))

    def set_password(self, password):
        self.password_hash = generate_password_hash(password)

    def check_password(self, password):
        return check_password_hash(self.password_hash, password)

class Device(db.Model):
    __tablename__ = 'devices'
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(100), nullable=False)
    host = db.Column(db.String(100), nullable=False)
    port = db.Column(db.Integer, default=443)
    api_key = db.Column(db.String(256), nullable=False)  # Encrypted
    is_active = db.Column(db.Boolean, default=True)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    
    deployments = db.relationship('RuleDeployment', backref='device', lazy='dynamic', cascade="all, delete-orphan")

class Rule(db.Model):
    __tablename__ = 'rules'
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(100), nullable=False)
    action = db.Column(db.String(10), nullable=False)  # 'allow' or 'block'
    protocol = db.Column(db.String(10), nullable=False) # 'tcp', 'udp', 'icmp', 'any'
    source_ip = db.Column(db.String(50), default='any')
    source_port = db.Column(db.String(20), default='any')
    dest_ip = db.Column(db.String(50), default='any')
    dest_port = db.Column(db.String(20), default='any')
    interface = db.Column(db.String(20), default='wan')
    description = db.Column(db.String(200))
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    
    deployments = db.relationship('RuleDeployment', backref='rule', lazy='dynamic', cascade="all, delete-orphan")

class RuleDeployment(db.Model):
    __tablename__ = 'rule_deployments'
    id = db.Column(db.Integer, primary_key=True)
    rule_id = db.Column(db.Integer, db.ForeignKey('rules.id'), nullable=False)
    device_id = db.Column(db.Integer, db.ForeignKey('devices.id'), nullable=False)
    status = db.Column(db.String(20), default='pending') # 'pending', 'success', 'failed'
    deployed_at = db.Column(db.DateTime)
    error_message = db.Column(db.Text)
    pfsense_rule_id = db.Column(db.String(100)) # ID returned by pfSense if any

class Log(db.Model):
    __tablename__ = 'logs'
    id = db.Column(db.Integer, primary_key=True)
    timestamp = db.Column(db.DateTime, default=datetime.utcnow)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=True)
    action = db.Column(db.String(100), nullable=False)
    details = db.Column(db.Text)
    
    def set_details(self, details_dict):
        self.details = json.dumps(details_dict)
        
    def get_details(self):
        try:
            return json.loads(self.details)
        except:
            return {}
