import { useState, useEffect } from 'react';
import { usersService, stylesService, instrumentsService } from '../services/api';
import type { User, Style, Instrument } from '../types';

interface ProfilProps {
    currentUser: User | null;
    onUserUpdate: (user: User) => void;
}

function Profil({ currentUser, onUserUpdate }: ProfilProps) {
    const [formData, setFormData] = useState({
        firstName: '',
        lastName: '',
        email: '',
        city: '',
        country: '',
        address: '',
    });
    const [styles, setStyles] = useState<Style[]>([]);
    const [instruments, setInstruments] = useState<Instrument[]>([]);
    const [selectedStyle, setSelectedStyle] = useState<number | null>(null);
    const [selectedInstrument, setSelectedInstrument] = useState<number | null>(null);
    const [loading, setLoading] = useState(false);
    const [success, setSuccess] = useState('');
    const [error, setError] = useState('');

    useEffect(() => {
        if (currentUser) {
            setFormData({
                firstName: currentUser.firstName || '',
                lastName: currentUser.lastName || '',
                email: currentUser.email || '',
                city: currentUser.city || '',
                country: currentUser.country || '',
                address: currentUser.address || '',
            });
        }
        loadStylesAndInstruments();
    }, [currentUser]);

    const loadStylesAndInstruments = async () => {
        try {
            const [stylesRes, instrumentsRes] = await Promise.all([
                stylesService.getAll(),
                instrumentsService.getAll(),
            ]);
            setStyles(stylesRes.data['hydra:member'] || []);
            setInstruments(instrumentsRes.data['hydra:member'] || []);
        } catch (err) {
            console.error('Erreur chargement:', err);
        }
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value,
        });
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError('');
        setSuccess('');

        try {
            if (currentUser?.id) {
                const response = await usersService.update(currentUser.id, formData);
                onUserUpdate(response.data);
                setSuccess('Profil mis à jour avec succès !');
            }
        } catch (err) {
            setError('Erreur lors de la mise à jour du profil');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="profil-page">
            <div className="profil-container">
                <div className="profil-header">
                    <div className="profil-avatar">
                        {currentUser?.image ? (
                            <img 
                                src={`http://localhost:8000/uploads/users/${currentUser.image}`} 
                                alt={currentUser.firstName} 
                            />
                        ) : (
                            <div className="avatar-placeholder">
                                {currentUser?.firstName?.charAt(0)}{currentUser?.lastName?.charAt(0)}
                            </div>
                        )}
                        <button className="btn-change-photo">📷 Changer la photo</button>
                    </div>
                    <div className="profil-title">
                        <h1>Mon Profil</h1>
                        <p>Gérez vos informations personnelles</p>
                    </div>
                </div>

                {success && <div className="success-message">{success}</div>}
                {error && <div className="error-message">{error}</div>}

                <form onSubmit={handleSubmit} className="profil-form">
                    <div className="form-section">
                        <h2>Informations personnelles</h2>
                        
                        <div className="form-row">
                            <div className="form-group">
                                <label>Prénom</label>
                                <input
                                    type="text"
                                    name="firstName"
                                    value={formData.firstName}
                                    onChange={handleChange}
                                    required
                                />
                            </div>
                            <div className="form-group">
                                <label>Nom</label>
                                <input
                                    type="text"
                                    name="lastName"
                                    value={formData.lastName}
                                    onChange={handleChange}
                                    required
                                />
                            </div>
                        </div>

                        <div className="form-group">
                            <label>Email</label>
                            <input
                                type="email"
                                name="email"
                                value={formData.email}
                                onChange={handleChange}
                                required
                                disabled
                            />
                            <small>L'email ne peut pas être modifié</small>
                        </div>
                    </div>

                    <div className="form-section">
                        <h2>Localisation</h2>
                        
                        <div className="form-group">
                            <label>Adresse</label>
                            <input
                                type="text"
                                name="address"
                                value={formData.address}
                                onChange={handleChange}
                                placeholder="Votre adresse"
                            />
                        </div>

                        <div className="form-row">
                            <div className="form-group">
                                <label>Ville</label>
                                <input
                                    type="text"
                                    name="city"
                                    value={formData.city}
                                    onChange={handleChange}
                                    placeholder="Votre ville"
                                />
                            </div>
                            <div className="form-group">
                                <label>Pays</label>
                                <input
                                    type="text"
                                    name="country"
                                    value={formData.country}
                                    onChange={handleChange}
                                    placeholder="Votre pays"
                                />
                            </div>
                        </div>
                    </div>

                    <div className="form-section">
                        <h2>Préférences musicales</h2>
                        
                        <div className="form-row">
                            <div className="form-group">
                                <label>Style principal</label>
                                <select
                                    value={selectedStyle || ''}
                                    onChange={(e) => setSelectedStyle(Number(e.target.value) || null)}
                                >
                                    <option value="">Sélectionnez un style</option>
                                    {styles.map((style) => (
                                        <option key={style.id} value={style.id}>
                                            {style.nom_style}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div className="form-group">
                                <label>Instrument principal</label>
                                <select
                                    value={selectedInstrument || ''}
                                    onChange={(e) => setSelectedInstrument(Number(e.target.value) || null)}
                                >
                                    <option value="">Sélectionnez un instrument</option>
                                    {instruments.map((instrument) => (
                                        <option key={instrument.id} value={instrument.id}>
                                            {instrument.nom_instrument}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div className="form-actions">
                        <button type="submit" className="btn-save" disabled={loading}>
                            {loading ? 'Enregistrement...' : 'Enregistrer les modifications'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}

export default Profil;