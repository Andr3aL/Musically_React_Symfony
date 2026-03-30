import axios from 'axios';
import type { AxiosResponse, InternalAxiosRequestConfig } from 'axios';
import type { User, Band, Style, Instrument, HydraCollection, LoginResponse } from '../types';

const API_URL = 'http://localhost:8000/api';

const api = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json',
    },
});

// Ajouter le token JWT à chaque requête
api.interceptors.request.use((config: InternalAxiosRequestConfig) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Service d'authentification
export const authService = {
    login: async (email: string, password: string): Promise<LoginResponse> => {
    const response = await axios.post<LoginResponse>(`${API_URL}/login`, { email, password });
    console.log('Login response:', response.data); // Debug
    if (response.data.token) {
        localStorage.setItem('token', response.data.token);
        console.log('Token saved:', response.data.token); // Debug
    }
    return response.data;
},
    register: async (userData: {
        email: string;
        password: string;
        firstName: string;
        lastName: string;
        city?: string;
        country?: string;
    }) => {
        const response = await axios.post(`${API_URL}/register`, userData);
        return response.data;
    },
    logout: (): void => {
        localStorage.removeItem('token');
    },
    isAuthenticated: (): boolean => {
        return !!localStorage.getItem('token');
    },
};

// Services pour les entités
export const stylesService = {
    getAll: (): Promise<AxiosResponse<HydraCollection<Style>>> => api.get('/styles'),
    getOne: (id: number): Promise<AxiosResponse<Style>> => api.get(`/styles/${id}`),
    create: (data: Partial<Style>): Promise<AxiosResponse<Style>> => api.post('/styles', data),
    update: (id: number, data: Partial<Style>): Promise<AxiosResponse<Style>> => api.put(`/styles/${id}`, data),
    delete: (id: number): Promise<AxiosResponse<void>> => api.delete(`/styles/${id}`),
};

export const instrumentsService = {
    getAll: (): Promise<AxiosResponse<HydraCollection<Instrument>>> => api.get('/instruments'),
    getOne: (id: number): Promise<AxiosResponse<Instrument>> => api.get(`/instruments/${id}`),
};

export const usersService = {
    getAll: (): Promise<AxiosResponse<HydraCollection<User>>> => api.get('/users'),
    getOne: (id: number): Promise<AxiosResponse<User>> => api.get(`/users/${id}`),
    getByEmail: (email: string): Promise<AxiosResponse<HydraCollection<User>>> => api.get(`/users?email=${email}`),
    search: (query: string): Promise<AxiosResponse<User[]>> => api.get(`/search/users?q=${encodeURIComponent(query)}`),
    update: (id: number, data: Partial<User>): Promise<AxiosResponse<User>> => api.patch(`/users/${id}`, data, {
        headers: {
            'Content-Type': 'application/merge-patch+json',
        },
    }),
    getCurrentUser: async (): Promise<User | null> => {
        const token = localStorage.getItem('token');
        if (token) {
            const payload = JSON.parse(atob(token.split('.')[1]));
            const response = await api.get<HydraCollection<User>>(`/users?email=${payload.username}`);
            const users = response.data['hydra:member'];
            return users.length > 0 ? users[0] : null;
        }
        return null;
    },
};

export const bandsService = {
    getAll: (): Promise<AxiosResponse<HydraCollection<Band>>> => api.get('/bands'),
    getOne: (id: number): Promise<AxiosResponse<Band>> => api.get(`/bands/${id}`),
    search: (query: string): Promise<AxiosResponse<Band[]>> => api.get(`/search/bands?q=${encodeURIComponent(query)}`),
    create: (data: { nameBand: string; description?: string }) => api.post('/bands/create', data),
    getPendingSetup: () => api.get('/profile/bands-pending-setup'),
    setupBand: (bandId: number, data: { nameBand: string; description?: string; styleId?: number | null }) => 
        api.post(`/bands/${bandId}/setup`, data),
    uploadImage: (bandId: number, imageFile: File) => {
        const formData = new FormData();
        formData.append('image', imageFile);
        return api.post(`/bands/${bandId}/upload-image`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    },
};

export const profileService = {
    getPreferences: () => api.get('/profile/preferences'),
    savePreferences: (data: {
        styles: { styleId: number; isPrincipal: boolean }[];
        instruments: { instrumentId: number; isMain: boolean; niveau: string }[];
    }) => api.post('/profile/preferences', data),
    uploadPhoto: (file: File) => {
        const formData = new FormData();
        formData.append('photo', file);
        return api.post('/profile/upload-photo', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
};

export const invitationsService = {
    checkShareBand: (userId: number) => api.get(`/users/${userId}/share-band`),
    sendInvitation: (receiverId: number) => api.post('/invitations/send', { receiverId }),
    sendBandInvitation: (bandId: number, receiverId: number) => api.post(`/bands/${bandId}/invite`, { receiverId }),
    getPending: () => api.get('/invitations/pending'),
    respond: (invitationId: number, accept: boolean) => api.post(`/invitations/${invitationId}/respond`, { accept }),
};

export default api;