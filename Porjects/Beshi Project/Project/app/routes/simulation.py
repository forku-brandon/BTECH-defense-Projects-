from flask import Blueprint, render_template, request, current_app
from flask_login import login_required
from app.models import Device, Rule

simulation_bp = Blueprint('simulation', __name__)

@simulation_bp.route('/')
@login_required
def index():
    event = request.args.get('event')
    item = request.args.get('item')
    devices = Device.query.all()
    rules = Rule.query.all()
    simulation_mode = current_app.config.get('SIMULATION_MODE', False)
    simulation_devices = [d for d in devices if str(d.host).lower().startswith('mock-')]
    live_devices = [d for d in devices if not str(d.host).lower().startswith('mock-')]

    return render_template(
        'simulation.html',
        devices=devices,
        rules=rules,
        event=event,
        item=item,
        simulation_mode=simulation_mode,
        simulation_count=len(simulation_devices),
        live_count=len(live_devices)
    )
