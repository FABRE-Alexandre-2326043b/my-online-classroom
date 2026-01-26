import axios from 'axios';

const API_URL = 'http://localhost:8000/api';

const api = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json',
    }
});

// Intercepteur pour JWT
api.interceptors.request.use(config => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Services pour les cours
export const courseService = {
    getAll: () => api.get('/courses'),
    getOne: (id) => api.get(`/courses/${id}`),
    getLessons: (courseId) => api.get(`/courses/${courseId}/lessons`),
};

// Services pour l'IA
export const aiService = {
    generateQuiz: (lessonId, questions = 5) => 
        api.post(`/ai/lesson/${lessonId}/quiz`, { questions }),
    
    askQuestion: (courseId, question) => 
        api.post(`/ai/course/${courseId}/ask`, { question }),
    
    generateSummary: (courseId) => 
        api.post(`/ai/course/${courseId}/summary`),
};

// Service d'authentification
export const authService = {
    login: (email, password) => 
        api.post('/login', { email, password }),
    
    register: (userData) => 
        api.post('/register', userData),
};

export default api;