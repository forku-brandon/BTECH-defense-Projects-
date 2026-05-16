from flask import Blueprint, render_template
from flask_login import login_required
from app.models import Device, Rule, RuleDeployment, Log

dashboard_bp = Blueprint('dashboard', __name__)

@dashboard_bp.route('/')
@login_required
def index():
    device_count = Device.query.count()
    rule_count = Rule.query.count()
    active_devices = Device.query.filter_by(is_active=True).count()
    recent_logs = Log.query.order_by(Log.timestamp.desc()).limit(10).all()
    recent_deployments = RuleDeployment.query.order_by(RuleDeployment.deployed_at.desc()).limit(5).all()
    
    return render_template('dashboard.html', 
                           device_count=device_count, 
                           rule_count=rule_count,
                           active_devices=active_devices,
                           recent_logs=recent_logs,
                           recent_deployments=recent_deployments)
