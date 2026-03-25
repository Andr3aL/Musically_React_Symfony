import { useState } from 'react';
import { authService } from '../services/api';
import logo from '../assets/logo.png';

interface RegisterProps {
    onRegisterSuccess: () => void;
    onBackToLogin: () => void;
}

function Register({ onRegisterSuccess, onBackToLogin }: RegisterProps) {
    const [formData, setFormData] = useState({
        email: '',
        password: '',
        confirmPassword: '',
        firstName: '',
        lastName: '',
        city: '',
        country: '',
    });
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value,
        });
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError('');

        // Vérifier les mots de passe
        if (formData.password !== formData.confirmPassword) {
            setError('Les mots de passe ne correspondent pas');
            setLoading(false);
            return;
        }

        // Vérifier la longueur du mot de passe
        if (formData.password.length < 6) {
            setError('Le mot de passe doit contenir au moins 6 caractères');
            setLoading(false);
            return;
        }

        try {
            await authService.register({
                email: formData.email,
                password: formData.password,
                firstName: formData.firstName,
                lastName: formData.lastName,
                city: formData.city || undefined,
                country: formData.country || undefined,
            });
            
            // Connexion automatique après inscription
            await authService.login(formData.email, formData.password);
            onRegisterSuccess();
        } catch (err: unknown) {
            const error = err as { response?: { data?: { error?: string } } };
            setError(error.response?.data?.error || 'Erreur lors de l\'inscription');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="login-page">
            <div className="register-box">
                <img src={logo} alt="Music'Ally" className="login-logo" />
                <h1>Inscription</h1>
                <p className="subtitle">Rejoignez la communauté des musiciens</p>

                {error && <div className="error">{error}</div>}

                <form onSubmit={handleSubmit}>
                    <div className="form-row">
                        <input
                            type="text"
                            name="firstName"
                            placeholder="Prénom *"
                            value={formData.firstName}
                            onChange={handleChange}
                            required
                        />
                        <input
                            type="text"
                            name="lastName"
                            placeholder="Nom *"
                            value={formData.lastName}
                            onChange={handleChange}
                            required
                        />
                    </div>

                    <input
                        type="email"
                        name="email"
                        placeholder="Email *"
                        value={formData.email}
                        onChange={handleChange}
                        required
                    />

                    <div className="form-row">
                        <input
                            type="password"
                            name="password"
                            placeholder="Mot de passe *"
                            value={formData.password}
                            onChange={handleChange}
                            required
                        />
                        <input
                            type="password"
                            name="confirmPassword"
                            placeholder="Confirmer *"
                            value={formData.confirmPassword}
                            onChange={handleChange}
                            required
                        />
                    </div>

                    <div className="form-row">
                        <input
                            type="text"
                            name="city"
                            placeholder="Ville"
                            value={formData.city}
                            onChange={handleChange}
                        />
                        <input
                            type="text"
                            name="country"
                            placeholder="Pays"
                            value={formData.country}
                            onChange={handleChange}
                        />
                    </div>

                    <button type="submit" disabled={loading}>
                        {loading ? 'Inscription...' : 'S\'inscrire'}
                    </button>
                </form>

                <p className="login-link">
                    Déjà un compte ? <span onClick={onBackToLogin}>Se connecter</span>
                </p>
            </div>
        </div>
    );
}

export default Register;