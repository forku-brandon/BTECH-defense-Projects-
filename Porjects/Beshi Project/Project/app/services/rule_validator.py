def validate_rule_data(data):
    """
    Validates rule form data before saving to the database.
    Returns (is_valid, error_message).
    """
    # Required fields
    required = ['name', 'action', 'protocol', 'interface']
    for field in required:
        if not data.get(field):
            return False, f"Field '{field}' is required."

    if data['action'] not in ['allow', 'block']:
        return False, "Action must be 'allow' or 'block'."

    if data['protocol'] not in ['tcp', 'udp', 'icmp', 'any']:
        return False, "Invalid protocol selected."

    # IP validation could be added here (using ipaddress module)
    # Port validation (checking if it's 'any' or a valid integer 1-65535)

    return True, None
