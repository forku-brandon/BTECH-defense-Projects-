from flask import Blueprint, render_template, redirect, url_for, flash, request
from flask_login import login_required
from app import db
from app.models import Device
from app.utils.encryption import encrypt_api_key
from app.services.logger import log_action
from app.services.pfsense_client import PfSenseClient

devices_bp = Blueprint('devices', __name__)

@devices_bp.route('/')
@login_required
def index():
    devices = Device.query.all()
    return render_template('devices.html', devices=devices)

@devices_bp.route('/add', methods=['POST'])
@login_required
def add():
    name = request.form.get('name')
    host = request.form.get('host')
    port = request.form.get('port', 443, type=int)
    api_key = request.form.get('api_key')
    
    if not all([name, host, api_key]):
        flash('Name, host, and API key are required.', 'danger')
        return redirect(url_for('devices.index'))
        
    encrypted_key = encrypt_api_key(api_key)
    new_device = Device(name=name, host=host, port=port, api_key=encrypted_key)
    
    # Test connection before saving
    client = PfSenseClient(new_device)
    success, msg = client.check_connection()
    
    if not success:
        flash(f'Device connection failed: {msg}. Please check the details and try again.', 'danger')
        return redirect(url_for('devices.index'))
        
    db.session.add(new_device)
    db.session.commit()
    log_action('DEVICE_ADDED', {'device_id': new_device.id, 'name': new_device.name})
    flash('Device added successfully.', 'success')
    return redirect(url_for('devices.index'))

@devices_bp.route('/<int:id>/delete', methods=['POST'])
@login_required
def delete(id):
    device = Device.query.get_or_404(id)
    log_action('DEVICE_DELETED', {'device_id': device.id, 'name': device.name})
    db.session.delete(device)
    db.session.commit()
    flash('Device deleted successfully.', 'success')
    return redirect(url_for('devices.index'))
    
@devices_bp.route('/<int:id>/test')
@login_required
def test_connection(id):
    device = Device.query.get_or_404(id)
    client = PfSenseClient(device)
    success, msg = client.check_connection()
    if success:
        flash(f'Connection successful: {msg}', 'success')
    else:
        flash(f'Connection failed: {msg}', 'danger')
    return redirect(url_for('devices.index'))
