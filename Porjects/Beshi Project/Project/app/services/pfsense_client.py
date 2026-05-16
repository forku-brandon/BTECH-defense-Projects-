import requests
from flask import current_app
from app.utils.encryption import decrypt_api_key

class PfSenseClient:
    def __init__(self, device):
        self.device = device
        self.base_url = f"https://{device.host}:{device.port}/api/v1"
        # In a real environment, you'd decrypt the API key
        self.api_key = decrypt_api_key(device.api_key)
        self.headers = {
            "X-API-Key": self.api_key,
            "Content-Type": "application/json"
        }
        self.timeout = 5
        
        # If host starts with 'mock', enable mock mode
        self.mock_mode = self.device.host.lower().startswith('mock')

    def check_connection(self):
        """Check if the device is reachable and API key is valid."""
        if self.mock_mode:
            return True, "Mock connection successful"
            
        try:
            response = requests.get(
                f"{self.base_url}/system/status",
                headers=self.headers,
                timeout=self.timeout,
                verify=False # Accept self-signed certificates
            )
            if response.status_code == 200:
                return True, "Connection successful"
            return False, f"API Error: HTTP {response.status_code}"
        except requests.exceptions.RequestException as e:
            return False, f"Connection failed: {str(e)}"

    def get_rules(self):
        """List all firewall rules from the device."""
        if self.mock_mode:
            return True, [], "Mock mode rules retrieved"
            
        try:
            response = requests.get(
                f"{self.base_url}/firewall/rule",
                headers=self.headers,
                timeout=self.timeout,
                verify=False
            )
            if response.status_code == 200:
                data = response.json()
                return True, data.get('data', []), "Success"
            return False, [], f"API Error: HTTP {response.status_code}"
        except requests.exceptions.RequestException as e:
            return False, [], f"Connection failed: {str(e)}"

    def create_rule(self, rule_data):
        """Deploy a new rule to the device."""
        if self.mock_mode:
            return True, {"id": "mock_123"}, "Mock rule deployed successfully"
            
        # Transform standard DB rule data into pfSense API expected format
        pfsense_payload = {
            "type": "pass" if rule_data.action == 'allow' else "block",
            "interface": rule_data.interface,
            "ipprotocol": "inet",
            "protocol": rule_data.protocol,
            "src": rule_data.source_ip,
            "srcport": rule_data.source_port if rule_data.source_port != 'any' else "",
            "dst": rule_data.dest_ip,
            "dstport": rule_data.dest_port if rule_data.dest_port != 'any' else "",
            "descr": rule_data.description or rule_data.name
        }
        
        try:
            response = requests.post(
                f"{self.base_url}/firewall/rule",
                headers=self.headers,
                json=pfsense_payload,
                timeout=self.timeout,
                verify=False
            )
            
            if response.status_code in [200, 201]:
                resp_data = response.json()
                # Try to extract the pfSense internal rule ID if returned
                pfsense_id = str(resp_data.get('data', {}).get('tracker', ''))
                return True, {"id": pfsense_id}, "Rule deployed successfully"
            
            error_msg = response.text
            try:
                error_msg = response.json().get('message', response.text)
            except:
                pass
            return False, None, f"Deploy failed: HTTP {response.status_code} - {error_msg}"
        except requests.exceptions.RequestException as e:
            return False, None, f"Connection failed: {str(e)}"
            
    def delete_rule(self, tracker_id):
        """Delete a rule by its tracker ID."""
        if self.mock_mode:
            return True, "Mock rule deleted successfully"
            
        try:
            response = requests.delete(
                f"{self.base_url}/firewall/rule?tracker={tracker_id}",
                headers=self.headers,
                timeout=self.timeout,
                verify=False
            )
            if response.status_code == 200:
                return True, "Rule deleted successfully"
            return False, f"API Error: HTTP {response.status_code}"
        except requests.exceptions.RequestException as e:
            return False, f"Connection failed: {str(e)}"

# Suppress InsecureRequestWarning for testing with self-signed certs
import urllib3
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)
