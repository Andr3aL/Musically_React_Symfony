import { useState, useEffect, useRef } from 'react';
import { usersService, stylesService, instrumentsService, profileService } from '../services/api';
import type { User, Style, Instrument } from '../types';

interface ProfilProps {
    currentUser: User | null;
    onUserUpdate: (user: User) => void;
}

interface SelectedStyle {
    styleId: number;
    nomStyle: string;
    isPrincipal: boolean;
}

interface SelectedInstrument {
    instrumentId: number;
    nomInstrument: string;
    isMain: boolean;
    niveau: string;
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
    const [allStyles, setAllStyles] = useState<Style[]>([]);
    const [allInstruments, setAllInstruments] = useState<Instrument[]>([]);
    const [selectedStyles, setSelectedStyles] = useState<SelectedStyle[]>([]);
    const [selectedInstruments, setSelectedInstruments] = useState<SelectedInstrument[]>([]);
    const [loading, setLoading] = useState(false);
    const [success, setSuccess] = useState('');
    const [error, setError] = useState('');
    const [uploading, setUploading] = useState(false);
    const [avatarPreview, setAvatarPreview] = useState<string | null>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);

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
        loadData();
    }, [currentUser]);

    const loadData = async () => {
        try {
            const [stylesRes, instrumentsRes, prefsRes] = await Promise.all([
                stylesService.getAll(),
                instrumentsService.getAll(),
                profileService.getPreferences(),
            ]);
            setAllStyles(stylesRes.data['hydra:member'] || stylesRes.data || []);
            setAllInstruments(instrumentsRes.data['hydra:member'] || instrumentsRes.data || []);
            setSelectedStyles(prefsRes.data.styles || []);
            setSelectedInstruments(prefsRes.data.instruments || []);
        } catch (err) {
            console.error('Erreur chargement:', err);
        }
    };

    const handlePhotoSelect = async (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;

        // Client-side validation
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            setError('Format non autorise. Utilisez JPEG, PNG, WebP ou GIF.');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            setError('Fichier trop volumineux (max 5 Mo).');
            return;
        }

        // Preview
        const reader = new FileReader();
        reader.onloadend = () => setAvatarPreview(reader.result as string);
        reader.readAsDataURL(file);

        // Upload
        setUploading(true);
        setError('');
        setSuccess('');
        try {
            const response = await profileService.uploadPhoto(file);
            const user = await usersService.getCurrentUser();
            if (user) onUserUpdate(user);
            setSuccess('Photo mise a jour !');
            setAvatarPreview(null);
        } catch (err: any) {
            setError(err.response?.data?.error || 'Erreur lors de l\'upload');
            setAvatarPreview(null);
        } finally {
            setUploading(false);
            if (fileInputRef.current) fileInputRef.current.value = '';
        }
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    // --- Styles ---
    const addStyle = (styleId: number) => {
        if (!styleId || selectedStyles.some(s => s.styleId === styleId)) return;
        const style = allStyles.find(s => s.id === styleId);
        if (!style) return;
        setSelectedStyles([...selectedStyles, {
            styleId: style.id,
            nomStyle: style.nom_style,
            isPrincipal: selectedStyles.length === 0,
        }]);
    };

    const removeStyle = (styleId: number) => {
        const remaining = selectedStyles.filter(s => s.styleId !== styleId);
        if (remaining.length > 0 && !remaining.some(s => s.isPrincipal)) {
            remaining[0].isPrincipal = true;
        }
        setSelectedStyles(remaining);
    };

    const setPrincipalStyle = (styleId: number) => {
        setSelectedStyles(selectedStyles.map(s => ({
            ...s,
            isPrincipal: s.styleId === styleId,
        })));
    };

    // --- Instruments ---
    const addInstrument = (instrumentId: number) => {
        if (!instrumentId || selectedInstruments.some(i => i.instrumentId === instrumentId)) return;
        const instrument = allInstruments.find(i => i.id === instrumentId);
        if (!instrument) return;
        setSelectedInstruments([...selectedInstruments, {
            instrumentId: instrument.id,
            nomInstrument: instrument.nom_instrument,
            isMain: selectedInstruments.length === 0,
            niveau: 'debutant',
        }]);
    };

    const removeInstrument = (instrumentId: number) => {
        const remaining = selectedInstruments.filter(i => i.instrumentId !== instrumentId);
        if (remaining.length > 0 && !remaining.some(i => i.isMain)) {
            remaining[0].isMain = true;
        }
        setSelectedInstruments(remaining);
    };

    const setMainInstrument = (instrumentId: number) => {
        setSelectedInstruments(selectedInstruments.map(i => ({
            ...i,
            isMain: i.instrumentId === instrumentId,
        })));
    };

    const setNiveau = (instrumentId: number, niveau: string) => {
        setSelectedInstruments(selectedInstruments.map(i =>
            i.instrumentId === instrumentId ? { ...i, niveau } : i
        ));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError('');
        setSuccess('');

        try {
            if (currentUser?.id) {
                await usersService.update(currentUser.id, formData);
                await profileService.savePreferences({
                    styles: selectedStyles.map(s => ({
                        styleId: s.styleId,
                        isPrincipal: s.isPrincipal,
                    })),
                    instruments: selectedInstruments.map(i => ({
                        instrumentId: i.instrumentId,
                        isMain: i.isMain,
                        niveau: i.niveau,
                    })),
                });

                const user = await usersService.getCurrentUser();
                if (user) onUserUpdate(user);
                setSuccess('Profil mis a jour avec succes !');
            }
        } catch (err) {
            setError('Erreur lors de la mise a jour du profil');
        } finally {
            setLoading(false);
        }
    };

    const availableStyles = allStyles.filter(s => !selectedStyles.some(sel => sel.styleId === s.id));
    const availableInstruments = allInstruments.filter(i => !selectedInstruments.some(sel => sel.instrumentId === i.id));

    return (
        <div className="profil-page">
            <div className="profil-container">
                <div className="profil-header">
                    <div className="profil-avatar">
                        {avatarPreview ? (
                            <img src={avatarPreview} alt="Preview" />
                        ) : currentUser?.image ? (
                            <img
                                src={`http://localhost:8000/uploads/users/${currentUser.image}`}
                                alt={currentUser.firstName}
                            />
                        ) : (
                            <div className="avatar-placeholder">
                                {currentUser?.firstName?.charAt(0)}{currentUser?.lastName?.charAt(0)}
                            </div>
                        )}
                        <input
                            type="file"
                            ref={fileInputRef}
                            onChange={handlePhotoSelect}
                            accept="image/jpeg,image/png,image/webp,image/gif"
                            style={{ display: 'none' }}
                        />
                        <button
                            type="button"
                            className="btn-change-photo"
                            onClick={() => fileInputRef.current?.click()}
                            disabled={uploading}
                        >
                            {uploading ? 'Envoi en cours...' : 'Changer la photo'}
                        </button>
                        <span className="photo-hint">JPEG, PNG, WebP ou GIF - 5 Mo max</span>
                    </div>
                    <div className="profil-title">
                        <h1>Mon Profil</h1>
                        <p>Gerez vos informations personnelles</p>
                    </div>
                </div>

                {success && <div className="success-message">{success}</div>}
                {error && <div className="error-message">{error}</div>}

                <form onSubmit={handleSubmit} className="profil-form">
                    <div className="form-section">
                        <h2>Informations personnelles</h2>
                        <div className="form-row">
                            <div className="form-group">
                                <label>Prenom</label>
                                <input type="text" name="firstName" value={formData.firstName} onChange={handleChange} required />
                            </div>
                            <div className="form-group">
                                <label>Nom</label>
                                <input type="text" name="lastName" value={formData.lastName} onChange={handleChange} required />
                            </div>
                        </div>
                        <div className="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value={formData.email} disabled />
                            <small>L'email ne peut pas etre modifie</small>
                        </div>
                    </div>

                    <div className="form-section">
                        <h2>Localisation</h2>
                        <div className="form-group">
                            <label>Adresse</label>
                            <input type="text" name="address" value={formData.address} onChange={handleChange} placeholder="Votre adresse" />
                        </div>
                        <div className="form-row">
                            <div className="form-group">
                                <label>Ville</label>
                                <input type="text" name="city" value={formData.city} onChange={handleChange} placeholder="Votre ville" />
                            </div>
                            <div className="form-group">
                                <label>Pays</label>
                                <input type="text" name="country" value={formData.country} onChange={handleChange} placeholder="Votre pays" />
                            </div>
                        </div>
                    </div>

                    <div className="form-section">
                        <h2>Mes styles musicaux</h2>
                        <p className="section-hint">Ajoutez vos styles et choisissez votre style principal.</p>

                        {selectedStyles.length > 0 && (
                            <div className="pref-list">
                                {selectedStyles.map(s => (
                                    <div key={s.styleId} className={`pref-item ${s.isPrincipal ? 'pref-principal' : ''}`}>
                                        <span className="pref-name">{s.nomStyle}</span>
                                        {s.isPrincipal ? (
                                            <span className="pref-badge">Principal</span>
                                        ) : (
                                            <button type="button" className="pref-btn-set" onClick={() => setPrincipalStyle(s.styleId)}>
                                                Definir principal
                                            </button>
                                        )}
                                        <button type="button" className="pref-btn-remove" onClick={() => removeStyle(s.styleId)}>X</button>
                                    </div>
                                ))}
                            </div>
                        )}

                        <div className="pref-add">
                            <select onChange={(e) => { addStyle(Number(e.target.value)); e.target.value = ''; }}>
                                <option value="">+ Ajouter un style...</option>
                                {availableStyles.map(s => (
                                    <option key={s.id} value={s.id}>{s.nom_style}</option>
                                ))}
                            </select>
                        </div>
                    </div>

                    <div className="form-section">
                        <h2>Mes instruments</h2>
                        <p className="section-hint">Ajoutez vos instruments, definissez le niveau et choisissez votre instrument principal.</p>

                        {selectedInstruments.length > 0 && (
                            <div className="pref-list">
                                {selectedInstruments.map(i => (
                                    <div key={i.instrumentId} className={`pref-item pref-item-instrument ${i.isMain ? 'pref-principal' : ''}`}>
                                        <div className="pref-item-top">
                                            <span className="pref-name">{i.nomInstrument}</span>
                                            {i.isMain ? (
                                                <span className="pref-badge">Principal</span>
                                            ) : (
                                                <button type="button" className="pref-btn-set" onClick={() => setMainInstrument(i.instrumentId)}>
                                                    Definir principal
                                                </button>
                                            )}
                                            <button type="button" className="pref-btn-remove" onClick={() => removeInstrument(i.instrumentId)}>X</button>
                                        </div>
                                        <div className="pref-item-bottom">
                                            <label className="niveau-label">Niveau :</label>
                                            <div className="niveau-options">
                                                {[
                                                    { val: 'debutant', label: 'Debutant' },
                                                    { val: 'intermediaire', label: 'Intermediaire' },
                                                    { val: 'avance', label: 'Avance' },
                                                ].map(n => (
                                                    <button
                                                        key={n.val}
                                                        type="button"
                                                        className={`niveau-btn ${i.niveau === n.val ? 'niveau-btn-active niveau-' + n.val : ''}`}
                                                        onClick={() => setNiveau(i.instrumentId, n.val)}
                                                    >
                                                        {n.label}
                                                    </button>
                                                ))}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}

                        <div className="pref-add">
                            <select onChange={(e) => { addInstrument(Number(e.target.value)); e.target.value = ''; }}>
                                <option value="">+ Ajouter un instrument...</option>
                                {availableInstruments.map(i => (
                                    <option key={i.id} value={i.id}>{i.nom_instrument}</option>
                                ))}
                            </select>
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
