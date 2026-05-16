from app import db
from app.models import RuleDeployment
from app.services.pfsense_client import PfSenseClient
from app.services.logger import log_action
from datetime import datetime

def deploy_rule_to_device(rule, device):
    """Deploy a single rule to a specific device."""
    client = PfSenseClient(device)
    
    # Check if a deployment record exists, if not create one
    deployment = RuleDeployment.query.filter_by(rule_id=rule.id, device_id=device.id).first()
    if not deployment:
        deployment = RuleDeployment(rule_id=rule.id, device_id=device.id)
        db.session.add(deployment)
    
    deployment.status = 'pending'
    db.session.commit()
    
    # Attempt deployment
    success, result_data, message = client.create_rule(rule)
    
    if success:
        deployment.status = 'success'
        deployment.error_message = None
        deployment.pfsense_rule_id = result_data.get('id')
        deployment.deployed_at = datetime.utcnow()
        log_action('RULE_DEPLOYED', {'rule_id': rule.id, 'device_id': device.id, 'status': 'success'})
    else:
        deployment.status = 'failed'
        deployment.error_message = message
        log_action('RULE_DEPLOY_FAILED', {'rule_id': rule.id, 'device_id': device.id, 'error': message})
        
    db.session.commit()
    return success, message

def sync_all_pending_rules(device):
    """Deploy all rules that are not currently 'success' on this device."""
    from app.models import Rule
    
    rules = Rule.query.all()
    results = []
    
    for rule in rules:
        deployment = RuleDeployment.query.filter_by(rule_id=rule.id, device_id=device.id).first()
        if not deployment or deployment.status != 'success':
            success, msg = deploy_rule_to_device(rule, device)
            results.append({'rule': rule.name, 'success': success, 'message': msg})
            
    return results
