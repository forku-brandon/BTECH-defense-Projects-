// js/ocr.js
// Initialize Tesseract
const Tesseract = window.Tesseract;

class OCRProcessor {
    static async recognizePlate(imageFile) {
        const worker = await Tesseract.createWorker();
        await worker.loadLanguage('eng');
        await worker.initialize('eng');
        
        // Set parameters for license plate recognition
        await worker.setParameters({
            tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            tessedit_pageseg_mode: 7, // Single text line
        });
        
        const { data: { text } } = await worker.recognize(imageFile);
        await worker.terminate();
        
        // Clean and format the plate number
        let plateNumber = text.replace(/\s/g, '').toUpperCase();
        plateNumber = plateNumber.match(/[A-Z0-9]{5,8}/)?.[0] || '';
        
        return plateNumber;
    }
    
    static validatePlateFormat(plate) {
        // Cameroon plate format: 2 letters + 3 numbers + 2 letters (example: AB123CD)
        const cameroonPlateRegex = /^[A-Z]{2}[0-9]{3}[A-Z]{2}$/;
        return cameroonPlateRegex.test(plate);
    }
}

// js/camera.js
class CameraHandler {
    constructor() {
        this.stream = null;
        this.videoElement = null;
        this.canvas = null;
    }
    
    async startCamera() {
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment' } // Use back camera
            });
            
            this.videoElement = document.getElementById('camera-feed');
            if (this.videoElement) {
                this.videoElement.srcObject = this.stream;
                await this.videoElement.play();
            }
            
            return true;
        } catch (error) {
            console.error('Camera error:', error);
            showAlert('Unable to access camera. Please check permissions.', 'danger');
            return false;
        }
    }
    
    stopCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        if (this.videoElement) {
            this.videoElement.srcObject = null;
        }
    }
    
    captureImage() {
        if (!this.videoElement) return null;
        
        const canvas = document.createElement('canvas');
        canvas.width = this.videoElement.videoWidth;
        canvas.height = this.videoElement.videoHeight;
        const context = canvas.getContext('2d');
        context.drawImage(this.videoElement, 0, 0, canvas.width, canvas.height);
        
        return canvas.toDataURL('image/jpeg', 0.8);
    }
    
    async captureAndRecognize() {
        const imageData = this.captureImage();
        if (!imageData) return null;
        
        // Convert dataURL to File object
        const blob = await (await fetch(imageData)).blob();
        const file = new File([blob], 'plate.jpg', { type: 'image/jpeg' });
        
        showAlert('Processing image...', 'info');
        const plateNumber = await OCRProcessor.recognizePlate(file);
        
        if (plateNumber && OCRProcessor.validatePlateFormat(plateNumber)) {
            return plateNumber;
        } else {
            showAlert('Could not recognize plate. Please try again.', 'danger');
            return null;
        }
    }
}

// Initialize camera handler
const cameraHandler = new CameraHandler();

// Open camera modal
function openCamera(onCapture) {
    const modal = document.getElementById('camera-modal');
    if (!modal) return;
    
    modal.style.display = 'block';
    cameraHandler.startCamera().then(success => {
        if (!success) {
            modal.style.display = 'none';
        }
    });
    
    // Setup capture button
    const captureBtn = document.getElementById('capture-btn');
    if (captureBtn) {
        const newCaptureBtn = captureBtn.cloneNode(true);
        captureBtn.parentNode.replaceChild(newCaptureBtn, captureBtn);
        newCaptureBtn.onclick = async () => {
            const plateNumber = await cameraHandler.captureAndRecognize();
            if (plateNumber && onCapture) {
                onCapture(plateNumber);
                closeCamera();
            }
        };
    }
    
    // Setup close button
    const closeBtn = document.getElementById('close-camera');
    if (closeBtn) {
        const newCloseBtn = closeBtn.cloneNode(true);
        closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
        newCloseBtn.onclick = closeCamera;
    }
}

function closeCamera() {
    cameraHandler.stopCamera();
    const modal = document.getElementById('camera-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}