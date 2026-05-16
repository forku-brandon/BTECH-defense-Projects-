from flask import Blueprint, render_template, redirect, url_for, flash, request
from flask_login import login_required
from app import db
from app.models import Rule, Device, RuleDeployment
from app.services.logger import log_action
from app.services.policy_engine import deploy_rule_to_device, sync_all_pending_rules

deploy_bp = Blueprint('deploy', __name__)

@deploy_bp.route('/')
@login_required
def index():
    devices = Device.query.all()
    rules = Rule.query.all()
    deployments = RuleDeployment.query.all()
    
    # Structure for the view: rule_id -> device_id -> deployment status
    status_map = {}
    for rule in rules:
        status_map[rule.id] = {}
        for device in devices:
            status_map[rule.id][device.id] = 'not_deployed'
            
    for dep in deployments:
        if dep.rule_id in status_map and dep.device_id in status_map[dep.rule_id]:
            status_map[dep.rule_id][dep.device_id] = dep.status
            
    return render_template('deploy.html', devices=devices, rules=rules, status_map=status_map)

@deploy_bp.route('/rule/<int:rule_id>/device/<int:device_id>', methods=['POST'])
@login_required
def deploy_single(rule_id, device_id):
    rule = Rule.query.get_or_404(rule_id)
    device = Device.query.get_or_404(device_id)
    
    success, msg = deploy_rule_to_device(rule, device)
    if success:
        flash(f'Rule "{rule.name}" successfully deployed to {device.name}', 'success')
    else:
        flash(f'Failed to deploy rule to {device.name}: {msg}', 'danger')
        
    return redirect(url_for('deploy.index'))

@deploy_bp.route('/sync/<int:device_id>', methods=['POST'])
@login_required
def sync_device(device_id):
    device = Device.query.get_or_404(device_id)
    results = sync_all_pending_rules(device)
    
    success_count = sum(1 for r in results if r['success'])
    fail_count = len(results) - success_count
    
    if len(results) == 0:
        flash(f'No pending rules to deploy for {device.name}.', 'info')
    else:
        flash(f'Sync complete for {device.name}: {success_count} succeeded, {fail_count} failed.', 
              'success' if fail_count == 0 else 'warning')
              
    return redirect(url_for('deploy.index'))
