from flask import Blueprint, render_template, current_app
from flask_login import login_required
from app.models import Device, Rule, RuleDeployment, Log

dashboard_bp = Blueprint('dashboard', __name__)

@dashboard_bp.route('/')
@login_required
def index():
    device_count = Device.query.count()
    rule_count = Rule.query.count()
    active_devices = Device.query.filter_by(is_active=True).count()
    simulation_devices = Device.query.filter(Device.host.like('mock-%')).count()
    simulation_mode = current_app.config.get('SIMULATION_MODE', False)
    recent_logs = Log.query.order_by(Log.timestamp.desc()).limit(10).all()
    recent_deployments = RuleDeployment.query.order_by(RuleDeployment.deployed_at.desc()).limit(5).all()
    
    return render_template('dashboard.html', 
                           device_count=device_count, 
                           rule_count=rule_count,
                           active_devices=active_devices,
                           simulation_devices=simulation_devices,
                           simulation_mode=simulation_mode,
                           recent_logs=recent_logs,
                           recent_deployments=recent_deployments)
