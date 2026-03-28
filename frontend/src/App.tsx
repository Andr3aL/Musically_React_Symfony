import { useState, useEffect } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { authService, usersService } from './services/api';
import Navbar from './components/Navbar';
import Footer from './components/Footer';
import Login from './pages/Login';
import Register from './pages/Register';
import Home from './pages/Home';
import Musiciens from './pages/Musiciens';
import Groupes from './pages/Groupes';
import Profil from './pages/Profil';
import type { User } from './types';
import './App.css';
import MusicienDetail from './pages/MusicienDetail';
import GroupeDetail from './pages/GroupeDetail';
import Chat from './pages/Chat';
import Invitations from './pages/Invitations';
import APropos from './pages/APropos';

function App() {
    const [isAuthenticated, setIsAuthenticated] = useState<boolean>(authService.isAuthenticated());
    const [currentUser, setCurrentUser] = useState<User | null>(null);
    const [loading, setLoading] = useState<boolean>(true);
    const [showRegister, setShowRegister] = useState<boolean>(false);

    useEffect(() => {
        if (isAuthenticated) {
            loadCurrentUser();
        } else {
            setLoading(false);
        }
    }, [isAuthenticated]);

    const loadCurrentUser = async (): Promise<void> => {
        try {
            const user = await usersService.getCurrentUser();
            setCurrentUser(user);
        } catch (error) {
            // console.error('Erreur:', error);
            // authService.logout();
            // setIsAuthenticated(false);
        } finally {
            setLoading(false);
        }
    };

    const handleLogin = (): void => {
        setIsAuthenticated(true);
    };

    const handleLogout = (): void => {
        authService.logout();
        setIsAuthenticated(false);
        setCurrentUser(null);
    };

    const handleRegisterSuccess = (): void => {
        setIsAuthenticated(true);
        setShowRegister(false);
    };

    const handleUserUpdate = (updatedUser: User): void => {
        setCurrentUser(updatedUser);
    };

    if (loading) {
        return <div className="loading">Chargement...</div>;
    }

    if (!isAuthenticated) {
        if (showRegister) {
            return (
                <Register 
                    onRegisterSuccess={handleRegisterSuccess} 
                    onBackToLogin={() => setShowRegister(false)} 
                />
            );
        }
        return (
            <Login 
                onLogin={handleLogin} 
                onShowRegister={() => setShowRegister(true)} 
            />
        );
    }

    return (
        <BrowserRouter>
            <div className="app">
                <Navbar user={currentUser} onLogout={handleLogout} />
                <main className="main-content">
                    <Routes>
                        <Route path="/" element={<Home currentUser={currentUser} />} />
                        <Route path="/musiciens" element={<Musiciens />} />
                        <Route path="/groupes" element={<Groupes />} />
                        <Route path="/groupe/:id" element={<GroupeDetail currentUser={currentUser} />} />
                        <Route path="/profil" element={<Profil currentUser={currentUser} onUserUpdate={handleUserUpdate} />} />
                        <Route path="/musicien/:id" element={<MusicienDetail currentUser={currentUser} />} />
                        <Route path="/chat/:userId" element={<Chat currentUser={currentUser} />} />
                        <Route path="/invitations" element={<Invitations />} />
                        <Route path="/a-propos" element={<APropos />} />
                        <Route path="*" element={<Navigate to="/" />} />
                    </Routes>
                </main>
                <Footer />
            </div>
        </BrowserRouter>
    );
}

export default App;