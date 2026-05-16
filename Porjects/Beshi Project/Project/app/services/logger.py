from app import db
from app.models import Log
from flask_login import current_user

def log_action(action, details=None):
    """Log an action to the database."""
    user_id = current_user.id if current_user and current_user.is_authenticated else None
    log_entry = Log(user_id=user_id, action=action)
    if details:
        log_entry.set_details(details)
    db.session.add(log_entry)
    try:
        db.session.commit()
    except Exception as e:
        db.session.rollback()
        print(f"Failed to save log: {str(e)}")
