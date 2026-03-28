import { useState, useEffect } from 'react';
import { Link, useLocation } from 'react-router-dom';
import logo from '../assets/logo.png';
import { invitationsService, bandsService } from '../services/api';
import type { User } from '../types';

interface NavbarProps {
    user: User | null;
    onLogout: () => void;
}

function Navbar({ user, onLogout }: NavbarProps) {
    const location = useLocation();
    const [pendingCount, setPendingCount] = useState(0);
    const [pendingSetupCount, setPendingSetupCount] = useState(0);

    useEffect(() => {
        if (user) {
            loadPendingInvitations();
            loadPendingSetup();
        }
    }, [user, location.pathname]);

    const loadPendingInvitations = async () => {
        try {
            const response = await invitationsService.getPending();
            setPendingCount(response.data.length);
        } catch (err) {
            console.error('Erreur chargement invitations:', err);
        }
    };

    const loadPendingSetup = async () => {
        try {
            const response = await bandsService.getPendingSetup();
            setPendingSetupCount(response.data.length);
        } catch (err) {
            console.error('Erreur chargement groupes à configurer:', err);
        }
    };

    const totalNotifications = pendingCount + pendingSetupCount;

    const isActive = (path: string): string => location.pathname === path ? 'active' : '';

    return (
        <nav className="navbar">
            <div className="navbar-brand">
                <Link to="/">
                    <img src={logo} alt="Music'Ally" className="logo" />
                </Link>
            </div>

            <div className="navbar-menu">
                <Link to="/" className={isActive('/')}>Accueil</Link>
                <Link to="/musiciens" className={isActive('/musiciens')}>Musiciens</Link>
                <Link to="/groupes" className={isActive('/groupes')}>Groupes</Link>
                <Link to="/a-propos" className={isActive('/a-propos')}>A propos</Link>
                {user?.roles?.includes('ROLE_ADMIN') && (
                    <a href="http://localhost:8000/admin" target="_blank" rel="noreferrer">Backoffice</a>
                )}
            </div>

            <div className="navbar-user">
                <Link to="/invitations" className={`invitations-link ${isActive('/invitations')}`} title="Notifications">
                    <span className="bell-icon">🔔</span>
                    {totalNotifications > 0 && (
                        <span className="notification-badge">{totalNotifications}</span>
                    )}
                </Link>
                <Link to="/profil" className="user-info">
                    <img 
                        src={user?.image ? `http://localhost:8000/uploads/users/${user.image}` : '/default-avatar.png'} 
                        alt={user?.firstName || 'User'} 
                        className="user-avatar"
                    />
                    <span>{user?.firstName || 'Utilisateur'}</span>
                </Link>
                <button onClick={onLogout} className="logout-btn" title="Déconnexion">
                    <span className="logout-icon">⏻</span>
                </button>
            </div>
        </nav>
    );
}

export default Navbar;