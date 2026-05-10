// ============================================
// API Client - With Correct Method Names
// ============================================

// Use the mock API for development
const USE_MOCK_API = true;

class API {
    static async get(endpoint, params = {}) {
        console.log(`API GET: ${endpoint}`, params);
        
        try {
            if (USE_MOCK_API) {
                // Make sure mockAPI is loaded
                if (typeof mockAPI === 'undefined') {
                    console.error('mockAPI not loaded!');
                    return { success: false, error: 'API not initialized', data: [] };
                }
                
                switch(endpoint) {
                    case '/sources':
                        return await mockAPI.getWaterSources();
                    case '/sources/detail':
                        return await mockAPI.getWaterSource(params.id);
                    case '/tests':
                        return await mockAPI.getTests(params.source_id);
                    case '/reports':
                        return await mockAPI.getReports(params.source_id);
                    case '/health-facilities':
                        return await mockAPI.getHealthFacilities();
                    case '/disease-data':
                        return await mockAPI.getDiseaseData();
                    case '/users':
                        return await mockAPI.getUsers();
                    default:
                        return { success: false, error: 'Endpoint not found', data: [] };
                }
            }
            
            // Real API call would go here
            const response = await fetch(`/api${endpoint}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                }
            });
            return await response.json();
            
        } catch (error) {
            console.error('API Error:', error);
            return { success: false, error: error.message, data: [] };
        }
    }
    
    static async post(endpoint, data) {
        console.log(`API POST: ${endpoint}`, data);
        
        try {
            if (USE_MOCK_API) {
                if (typeof mockAPI === 'undefined') {
                    return { success: false, error: 'API not initialized' };
                }
                
                switch(endpoint) {
                    case '/reports':
                        return await mockAPI.addReport(data);
                    case '/tests':
                        return await mockAPI.addTestResult(data);
                    case '/sources':
                        return await mockAPI.addWaterSource(data);
                    case '/disease-data':
                        return await mockAPI.addDiseaseData(data);
                    case '/auth/login':
                        return await mockAPI.login(data.email, data.password);
                    case '/auth/register':
                        return await mockAPI.register(data);
                    case '/users':
                        return await mockAPI.addUser(data);
                    default:
                        return { success: false, error: 'Endpoint not found' };
                }
            }
            
            const response = await fetch(`/api${endpoint}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                },
                body: JSON.stringify(data)
            });
            return await response.json();
            
        } catch (error) {
            console.error('API Error:', error);
            return { success: false, error: error.message };
        }
    }
    
    static async put(endpoint, data) {
        console.log(`API PUT: ${endpoint}`, data);
        
        try {
            if (USE_MOCK_API) {
                if (typeof mockAPI === 'undefined') {
                    return { success: false, error: 'API not initialized' };
                }
                
                switch(endpoint) {
                    case '/reports/moderate':
                        return await mockAPI.moderateReport(data);
                    case '/reports/update':
                        return await mockAPI.updateReport(data);
                    case '/sources':
                        return await mockAPI.updateWaterSource(data);
                    case '/users':
                        return await mockAPI.updateUser(data);
                    default:
                        return { success: false, error: 'Endpoint not found' };
                }
            }
            
            const response = await fetch(`/api${endpoint}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                },
                body: JSON.stringify(data)
            });
            return await response.json();
            
        } catch (error) {
            console.error('API Error:', error);
            return { success: false, error: error.message };
        }
    }
    
    static async delete(endpoint, id) {
        console.log(`API DELETE: ${endpoint}/${id}`);
        
        try {
            if (USE_MOCK_API) {
                if (typeof mockAPI === 'undefined') {
                    return { success: false, error: 'API not initialized' };
                }
                
                switch(endpoint) {
                    case '/reports':
                        return await mockAPI.deleteReport(id);
                    case '/sources':
                        return await mockAPI.deleteWaterSource(id);
                    case '/users':
                        return await mockAPI.deleteUser(id);
                    default:
                        return { success: false, error: 'Endpoint not found' };
                }
            }
            
            const response = await fetch(`/api${endpoint}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                }
            });
            return await response.json();
            
        } catch (error) {
            console.error('API Error:', error);
            return { success: false, error: error.message };
        }
    }
}

// Make API globally available
window.API = API;