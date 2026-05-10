// ============================================
// Mock API Service - Simulates Backend Data
// ============================================

class MockAPI {
    constructor() {
        // Initialize sample data
        this.initializeData();
    }

    initializeData() {
        // Water Sources
        this.waterSources = [
            { id: 1, name: "Mankon Spring A", location_desc: "Near Mankon Palace", source_type: "spring", latitude: 5.9667, longitude: 10.1500, safety_status: "safe", last_test_date: "2024-02-15", created_at: "2024-01-01" },
            { id: 2, name: "Mankon Well B", location_desc: "Commercial Avenue", source_type: "well", latitude: 5.9680, longitude: 10.1480, safety_status: "caution", last_test_date: "2024-02-10", created_at: "2024-01-01" },
            { id: 3, name: "Mankon Tap C", location_desc: "Government School Area", source_type: "tap", latitude: 5.9700, longitude: 10.1520, safety_status: "unsafe", last_test_date: "2024-02-01", created_at: "2024-01-01" },
            { id: 4, name: "Mankon Borehole D", location_desc: "Market Square", source_type: "borehole", latitude: 5.9640, longitude: 10.1470, safety_status: "safe", last_test_date: "2024-02-12", created_at: "2024-01-01" },
            { id: 5, name: "Mankon Spring E", location_desc: "Nkwen Road", source_type: "spring", latitude: 5.9720, longitude: 10.1550, safety_status: "caution", last_test_date: "2024-02-08", created_at: "2024-01-01" },
            { id: 6, name: "Mankon Well F", location_desc: "Bamenda Main Road", source_type: "well", latitude: 5.9620, longitude: 10.1450, safety_status: "unsafe", last_test_date: "2024-01-28", created_at: "2024-01-15" }
        ];

        // Official Tests
        this.officialTests = {
            1: [
                { id: 1, test_date: "2024-02-15", ph_level: 7.2, turbidity: "Low", coliform_present: false, tested_by: "MINEE", notes: "Water quality good" },
                { id: 2, test_date: "2024-01-15", ph_level: 7.0, turbidity: "Low", coliform_present: false, tested_by: "MINEE", notes: "" }
            ],
            2: [
                { id: 3, test_date: "2024-02-10", ph_level: 6.8, turbidity: "Medium", coliform_present: false, tested_by: "MINEE", notes: "Slight turbidity" },
                { id: 4, test_date: "2024-01-10", ph_level: 6.5, turbidity: "Medium", coliform_present: true, tested_by: "MINEE", notes: "Coliform detected" }
            ],
            3: [
                { id: 5, test_date: "2024-02-01", ph_level: 6.2, turbidity: "High", coliform_present: true, tested_by: "MINEE", notes: "High contamination" }
            ],
            4: [
                { id: 6, test_date: "2024-02-12", ph_level: 7.1, turbidity: "Low", coliform_present: false, tested_by: "MINEE", notes: "" }
            ],
            5: [
                { id: 7, test_date: "2024-02-08", ph_level: 6.7, turbidity: "Medium", coliform_present: true, tested_by: "MINEE", notes: "Coliform detected" }
            ],
            6: [
                { id: 8, test_date: "2024-01-28", ph_level: 6.3, turbidity: "High", coliform_present: true, tested_by: "MINEE", notes: "Severe contamination" }
            ]
        };

        // Community Reports
        this.communityReports = [
            { id: 1, source_id: 1, report_date: "2024-02-14", observation_type: "clear", description: "Water looks clean today", reporter_name: "John", reporter_contact: "", status: "approved", photo_url: null },
            { id: 2, source_id: 2, report_date: "2024-02-12", observation_type: "cloudy", description: "Water appears cloudy this morning", reporter_name: "Paul", reporter_contact: "paul@email.com", status: "approved", photo_url: null },
            { id: 3, source_id: 3, report_date: "2024-02-05", observation_type: "bad_smell", description: "Strong sulfur smell coming from the water", reporter_name: "James", reporter_contact: "", status: "approved", photo_url: null },
            { id: 4, source_id: 2, report_date: "2024-02-09", observation_type: "bad_taste", description: "Water has a metallic taste today", reporter_name: "Sarah", reporter_contact: "sarah@email.com", status: "pending", photo_url: null },
            { id: 5, source_id: 6, report_date: "2024-02-11", observation_type: "dumping", description: "People are dumping waste near the water source", reporter_name: "Community Member", reporter_contact: "", status: "pending", photo_url: null }
        ];

        // Health Facilities
        this.healthFacilities = [
            { id: 1, name: "Mankon District Hospital", latitude: 5.9675, longitude: 10.1515, 
              demo_data: { typhoid: 12, malaria: 45, diarrhea: 8, week: "Week 7" } },
            { id: 2, name: "Mankon Health Center", latitude: 5.9690, longitude: 10.1495, 
              demo_data: { typhoid: 8, malaria: 32, diarrhea: 5, week: "Week 7" } },
            { id: 3, name: "St. Joseph Clinic", latitude: 5.9650, longitude: 10.1530, 
              demo_data: { typhoid: 5, malaria: 28, diarrhea: 3, week: "Week 7" } }
        ];

        // Disease Data
        this.diseaseData = {
            weekly: [
                { week: "Week 1", typhoid: 15, malaria: 65, diarrhea: 12, cholera: 0 },
                { week: "Week 2", typhoid: 18, malaria: 72, diarrhea: 15, cholera: 0 },
                { week: "Week 3", typhoid: 22, malaria: 85, diarrhea: 18, cholera: 1 },
                { week: "Week 4", typhoid: 25, malaria: 92, diarrhea: 22, cholera: 2 },
                { week: "Week 5", typhoid: 20, malaria: 78, diarrhea: 16, cholera: 1 },
                { week: "Week 6", typhoid: 16, malaria: 68, diarrhea: 12, cholera: 0 },
                { week: "Week 7", typhoid: 12, malaria: 58, diarrhea: 9, cholera: 0 }
            ]
        };

        // Users
      this.users = [
    { id: 1, name: "John Admin", email: "admin@example.com", role: "admin", password: "password123", created_at: "2024-01-01" },
    { id: 2, name: "Sarah Clerk", email: "clerk@example.com", role: "data_entry", password: "password123", created_at: "2024-01-15" },
    { id: 3, name: "Dr. Michael", email: "health@example.com", role: "health_worker", password: "password123", created_at: "2024-02-01" },
    { id: 4, name: "Community User", email: "user@example.com", role: "registered", password: "password123", created_at: "2024-02-10" },
    { id: 5, name: "Water Officer", email: "water@example.com", role: "data_entry", password: "password123", created_at: "2024-02-15" }
];
    }

    // Simulate network delay
    async delay(ms = 300) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // Water Sources API
    async getWaterSources() {
        await this.delay();
        return { success: true, data: this.waterSources };
    }

    async getWaterSource(id) {
        await this.delay();
        const source = this.waterSources.find(s => s.id === parseInt(id));
        return { success: true, data: source };
    }

    async addWaterSource(data) {
        await this.delay();
        const newId = this.waterSources.length + 1;
        const newSource = {
            id: newId,
            ...data,
            safety_status: "no-data",
            last_test_date: new Date().toISOString().split('T')[0],
            created_at: new Date().toISOString().split('T')[0]
        };
        this.waterSources.push(newSource);
        return { success: true, data: newSource, message: "Water source added successfully!" };
    }

    async updateWaterSource(data) {
        await this.delay();
        const index = this.waterSources.findIndex(s => s.id === data.id);
        if (index !== -1) {
            this.waterSources[index] = { ...this.waterSources[index], ...data };
            return { success: true, message: "Water source updated successfully!" };
        }
        return { success: false, error: "Source not found" };
    }

    async deleteWaterSource(id) {
        await this.delay();
        const index = this.waterSources.findIndex(s => s.id === id);
        if (index !== -1) {
            this.waterSources.splice(index, 1);
            return { success: true, message: "Water source deleted successfully!" };
        }
        return { success: false, error: "Source not found" };
    }

    // Tests API
    async getTests(sourceId) {
        await this.delay();
        const tests = this.officialTests[sourceId] || [];
        return { success: true, data: tests };
    }

    async addTestResult(data) {
        await this.delay();
        const newId = Date.now();
        const newTest = {
            id: newId,
            ...data,
            created_at: new Date().toISOString()
        };
        
        if (!this.officialTests[data.source_id]) {
            this.officialTests[data.source_id] = [];
        }
        this.officialTests[data.source_id].unshift(newTest);
        
        // Update source safety status
        await this.updateSourceSafetyStatus(data.source_id);
        
        return { success: true, message: "Test result added successfully!" };
    }

    async updateSourceSafetyStatus(sourceId) {
        const tests = this.officialTests[sourceId] || [];
        const lastTest = tests[0];
        
        if (!lastTest) {
            const source = this.waterSources.find(s => s.id === sourceId);
            if (source) source.safety_status = "no-data";
            return;
        }
        
        let newStatus = "safe";
        if (lastTest.coliform_present) {
            newStatus = "unsafe";
        } else if (lastTest.turbidity === "High") {
            newStatus = "unsafe";
        } else if (lastTest.turbidity === "Medium") {
            newStatus = "caution";
        } else if (lastTest.ph_level && (lastTest.ph_level < 6.5 || lastTest.ph_level > 8.5)) {
            newStatus = "caution";
        }
        
        const source = this.waterSources.find(s => s.id === sourceId);
        if (source) {
            source.safety_status = newStatus;
            source.last_test_date = lastTest.test_date;
        }
    }

    // Reports API
    async getReports(sourceId = null) {
        await this.delay();
        let reports = [...this.communityReports];
        if (sourceId) {
            reports = reports.filter(r => r.source_id === parseInt(sourceId));
        }
        return { success: true, data: reports };
    }

    async addReport(data) {
        await this.delay();
        const newId = this.communityReports.length + 1;
        const newReport = {
            id: newId,
            ...data,
            status: "pending",
            report_date: new Date().toISOString().split('T')[0]
        };
        this.communityReports.unshift(newReport);
        return { success: true, data: newReport, message: "Report submitted successfully!" };
    }

    async moderateReport(data) {
        await this.delay();
        const report = this.communityReports.find(r => r.id === data.id);
        if (report) {
            report.status = data.status;
            return { success: true, message: `Report ${data.status} successfully` };
        }
        return { success: false, error: "Report not found" };
    }

    async updateReport(data) {
        await this.delay();
        const index = this.communityReports.findIndex(r => r.id === data.id);
        if (index !== -1) {
            this.communityReports[index] = { ...this.communityReports[index], ...data };
            return { success: true, message: "Report updated successfully" };
        }
        return { success: false, error: "Report not found" };
    }

    async deleteReport(id) {
        await this.delay();
        const index = this.communityReports.findIndex(r => r.id === id);
        if (index !== -1) {
            this.communityReports.splice(index, 1);
            return { success: true, message: "Report deleted successfully!" };
        }
        return { success: false, error: "Report not found" };
    }

    // Health Facilities API
    async getHealthFacilities() {
        await this.delay();
        return { success: true, data: this.healthFacilities };
    }

    // Disease Data API
    async getDiseaseData() {
        await this.delay();
        return { success: true, data: this.diseaseData };
    }

    async addDiseaseData(data) {
        await this.delay();
        this.diseaseData.weekly.push(data);
        return { success: true, message: "Disease data submitted successfully!" };
    }

    // Auth API
    async login(email, password) {
        await this.delay(500);
        const user = this.users.find(u => u.email === email && u.password === password);
        if (user) {
            const { password, ...userWithoutPassword } = user;
            return { success: true, user: userWithoutPassword };
        }
        return { success: false, message: "Invalid email or password" };
    }

    async register(userData) {
        await this.delay(500);
        const existingUser = this.users.find(u => u.email === userData.email);
        if (existingUser) {
            return { success: false, message: "Email already registered" };
        }
        
        const newId = this.users.length + 1;
        const newUser = {
            id: newId,
            name: userData.name,
            email: userData.email,
            role: "registered",
            password: userData.password,
            created_at: new Date().toISOString().split('T')[0]
        };
        this.users.push(newUser);
        
        const { password, ...userWithoutPassword } = newUser;
        return { success: true, user: userWithoutPassword, message: "Registration successful!" };
    }

    // Users API (Admin only)
    async getUsers() {
        await this.delay();
        const usersWithoutPassword = this.users.map(({ password, ...user }) => user);
        return { success: true, data: usersWithoutPassword };
    }

    async addUser(userData) {
        await this.delay();
        const newId = this.users.length + 1;
        const newUser = {
            id: newId,
            name: userData.name,
            email: userData.email,
            role: userData.role,
            password: userData.password,
            created_at: new Date().toISOString().split('T')[0]
        };
        this.users.push(newUser);
        const { password, ...userWithoutPassword } = newUser;
        return { success: true, user: userWithoutPassword, message: "User created successfully!" };
    }

    async updateUser(userData) {
        await this.delay();
        const index = this.users.findIndex(u => u.id === userData.id);
        if (index !== -1) {
            this.users[index] = { ...this.users[index], ...userData };
            return { success: true, message: "User updated successfully!" };
        }
        return { success: false, error: "User not found" };
    }

    async deleteUser(id) {
        await this.delay();
        const index = this.users.findIndex(u => u.id === id);
        if (index !== -1) {
            this.users.splice(index, 1);
            return { success: true, message: "User deleted successfully!" };
        }
        return { success: false, error: "User not found" };
    }
}

// Create a single instance
const mockAPI = new MockAPI();

// Make it globally available
window.mockAPI = mockAPI;