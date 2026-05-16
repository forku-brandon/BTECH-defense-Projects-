from flask import Blueprint, render_template
from flask_login import login_required
from app.models import Log

logs_bp = Blueprint('logs', __name__)

@logs_bp.route('/')
@login_required
def index():
    logs = Log.query.order_by(Log.timestamp.desc()).all()
    return render_template('logs.html', logs=logs)
