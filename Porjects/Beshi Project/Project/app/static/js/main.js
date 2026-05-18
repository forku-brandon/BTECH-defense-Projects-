// Main JS for Firewall Automation System
function createLogEntry(message, variant = 'info') {
    const log = document.getElementById('simulationLog');
    if (!log) return;
    const item = document.createElement('li');
    item.className = 'list-group-item terminal-block';
    item.innerHTML = `<span class="text-${variant === 'error' ? 'danger' : variant === 'success' ? 'success' : 'info'}">⟩ SYSTEM</span> ${message}`;
    log.insertBefore(item, log.firstChild);
}

function pulseNetwork() {
    const hub = document.querySelector('.network-hub');
    if (!hub) return;
    const pulse = document.createElement('div');
    pulse.className = 'network-pulse';
    hub.appendChild(pulse);
    setTimeout(() => pulse.remove(), 1200);
}

function spawnPacket(type, label) {
    const layer = document.getElementById('packetLayer');
    if (!layer) return;
    const packet = document.createElement('div');
    packet.className = `packet-beam ${type === 'block' ? 'block' : 'allow'}`;
    packet.setAttribute('title', label);

    const bounds = layer.getBoundingClientRect();
    const startX = type === 'block' ? bounds.width * 0.08 : bounds.width * 0.92;
    const startY = bounds.height * (0.25 + Math.random() * 0.5);
    const targetX = bounds.width * 0.5 - 7;
    const targetY = bounds.height * 0.5 - 7 + (Math.random() * 40 - 20);

    packet.style.left = `${startX}px`;
    packet.style.top = `${startY}px`;
    layer.appendChild(packet);

    packet.animate([
        { transform: 'translate(0, 0) scale(0.9)', opacity: 0.4 },
        { transform: `translate(${targetX - startX}px, ${targetY - startY}px) scale(1.2)`, opacity: 1 }
    ], {
        duration: 1400,
        easing: 'cubic-bezier(.22,.61,.36,1)',
        fill: 'forwards'
    });

    setTimeout(() => {
        packet.remove();
    }, 1500);
}

function startSimulation() {
    const data = window.simulationData;
    if (!data || !document.getElementById('packetLayer')) {
        return;
    }

    const summary = data.event ? `${data.event.replace('_', ' ')} triggered for ${data.item || 'system update'}` : 'Real-time simulation of current network state.';
    createLogEntry(summary, 'success');
    pulseNetwork();

    const steps = [];
    if (data.event === 'device_added') {
        steps.push({ message: `Node ${data.item} has been registered and is being validated.`, type: 'allow' });
        steps.push({ message: 'Secure API tunnel established with the firewall node.', type: 'allow' });
        steps.push({ message: 'Node activation confirmed; protection mesh updated.', type: 'allow' });
    } else if (data.event === 'rule_added') {
        steps.push({ message: `Policy ${data.item} has been compiled into the enforcement engine.`, type: 'allow' });
        steps.push({ message: 'Traffic inspection rules are being analyzed and signed.', type: 'allow' });
        steps.push({ message: 'Policy activated in the simulation core.', type: data.item && data.item.toLowerCase().includes('block') ? 'block' : 'allow' });
    } else if (data.event === 'rule_deployed') {
        steps.push({ message: `Deploying ${data.item} into the simulated firewall mesh.`, type: 'allow' });
        steps.push({ message: `Rule propagation event applied to simulated node.`, type: 'allow' });
        steps.push({ message: `Simulation confirms ${data.item} is now active.`, type: 'allow' });
    } else if (data.event === 'sync_complete') {
        steps.push({ message: `Synchronization completed for ${data.item}.`, type: 'allow' });
        steps.push({ message: 'All simulated deployment targets are now reconciled.', type: 'allow' });
        steps.push({ message: 'Traffic flow monitoring continues without interruption.', type: 'allow' });
    } else {
        steps.push({ message: 'Topology scan complete; all devices and rules are synced.', type: 'allow' });
    }

    let delay = 0;
    steps.forEach((step) => {
        delay += 800;
        setTimeout(() => {
            createLogEntry(step.message, step.type === 'block' ? 'error' : 'success');
            spawnPacket(step.type, step.message);
            pulseNetwork();
        }, delay);
    });
}

function autoDismissAlerts() {
    setTimeout(function() {
        let alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            let bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
}

document.addEventListener('DOMContentLoaded', function() {
    autoDismissAlerts();
    startSimulation();
});
