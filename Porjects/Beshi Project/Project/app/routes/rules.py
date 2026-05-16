from flask import Blueprint, render_template, redirect, url_for, flash, request
from flask_login import login_required
from app import db
from app.models import Rule, Device, RuleDeployment
from app.services.logger import log_action
from app.services.rule_validator import validate_rule_data

rules_bp = Blueprint('rules', __name__)

@rules_bp.route('/')
@login_required
def index():
    rules = Rule.query.all()
    devices = Device.query.all()
    return render_template('rules.html', rules=rules, devices=devices)

@rules_bp.route('/add', methods=['POST'])
@login_required
def add():
    data = {
        'name': request.form.get('name'),
        'action': request.form.get('action'),
        'protocol': request.form.get('protocol'),
        'source_ip': request.form.get('source_ip') or 'any',
        'source_port': request.form.get('source_port') or 'any',
        'dest_ip': request.form.get('dest_ip') or 'any',
        'dest_port': request.form.get('dest_port') or 'any',
        'interface': request.form.get('interface'),
        'description': request.form.get('description')
    }
    
    is_valid, error_msg = validate_rule_data(data)
    if not is_valid:
        flash(error_msg, 'danger')
        return redirect(url_for('rules.index'))
        
    new_rule = Rule(**data)
    db.session.add(new_rule)
    db.session.commit()
    log_action('RULE_CREATED', {'rule_id': new_rule.id, 'name': new_rule.name})
    flash('Rule created successfully.', 'success')
    return redirect(url_for('rules.index'))

@rules_bp.route('/<int:id>/delete', methods=['POST'])
@login_required
def delete(id):
    rule = Rule.query.get_or_404(id)
    # Could potentially un-deploy from devices here if needed, but for SRS scope just delete from DB
    log_action('RULE_DELETED', {'rule_id': rule.id, 'name': rule.name})
    db.session.delete(rule)
    db.session.commit()
    flash('Rule deleted successfully.', 'success')
    return redirect(url_for('rules.index'))
