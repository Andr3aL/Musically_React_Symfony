import { useState } from 'react';
import { authService } from '../services/api';
import logo from '../assets/logo.png';
import logoText from '../assets/logo-text.png';

interface LoginProps {
    onLogin: () => void;
    onShowRegister: () => void;
}

function Login({ onLogin, onShowRegister }: LoginProps) {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError('');

        try {
            await authService.login(email, password);
            onLogin();
        } catch (err) {
            setError('Email ou mot de passe incorrect');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="login-page">
            <div className="login-box">
                <img src={logo} alt="Music'Ally" className="login-logo" />
                <h1>Connexion</h1>

                {error && <div className="error">{error}</div>}

                <form onSubmit={handleSubmit}>
                    <input
                        type="email"
                        placeholder="Email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                    />
                    <input
                        type="password"
                        placeholder="Mot de passe"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        required
                    />
                    <button type="submit" disabled={loading}>
                        {loading ? 'Connexion...' : 'Se connecter'}
                    </button>
                </form>

                <p className="register-link">
                    Pas encore de compte ? <span onClick={onShowRegister}>S'inscrire</span>
                </p>
            </div>
        </div>
    );
}

export default Login;