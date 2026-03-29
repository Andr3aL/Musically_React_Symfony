import { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { bandsService, stylesService } from '../services/api';
import type { Band, User, Style } from '../types';

interface GroupeDetailProps {
    currentUser: User | null;
}

function GroupeDetail({ currentUser }: GroupeDetailProps) {
    const { id } = useParams<{ id: string }>();
    const [band, setBand] = useState<Band | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    
    // Edit modal state
    const [showEditModal, setShowEditModal] = useState(false);
    const [editName, setEditName] = useState('');
    const [editDescription, setEditDescription] = useState('');
    const [editStyleId, setEditStyleId] = useState<number | null>(null);
    const [editImage, setEditImage] = useState<File | null>(null);
    const [imagePreview, setImagePreview] = useState<string | null>(null);
    const [saving, setSaving] = useState(false);
    const [styles, setStyles] = useState<Style[]>([]);

    useEffect(() => {
        if (id) {
            loadBand(parseInt(id));
        }
        loadStyles();
    }, [id]);

    const loadStyles = async () => {
        try {
            const response = await stylesService.getAll();
            // Extract from HydraCollection
            const stylesData = response.data['hydra:member'] || response.data;
            setStyles(Array.isArray(stylesData) ? stylesData : []);
        } catch (err) {
            console.error('Erreur chargement styles:', err);
        }
    };

    const loadBand = async (bandId: number) => {
        try {
            const response = await bandsService.getOne(bandId);
            setBand(response.data);
        } catch (err) {
            console.error('Erreur chargement groupe:', err);
            setError('Groupe non trouvé');
        } finally {
            setLoading(false);
        }
    };

    const isCurrentUserAdmin = (): boolean => {
        if (!currentUser || !band?.members) return false;
        return band.members.some(m => m.user.id == currentUser.id && m.isAdmin);
    };

    const openEditModal = () => {
        if (band) {
            setEditName(band.nameBand);
            setEditDescription(band.description || '');
            setEditStyleId(band.style?.id || null);
            setEditImage(null);
            setImagePreview(band.photoBand ? `http://localhost:8000/uploads/bands/${band.photoBand}` : null);
            setShowEditModal(true);
        }
    };

    const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setEditImage(file);
            // Create preview URL
            const reader = new FileReader();
            reader.onloadend = () => {
                setImagePreview(reader.result as string);
            };
            reader.readAsDataURL(file);
        }
    };

    const handleSaveEdit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!band || !editName.trim()) return;

        setSaving(true);
        try {
            // First, upload image if changed
            if (editImage) {
                await bandsService.uploadImage(band.id, editImage);
            }

            // Then update other fields
            await bandsService.setupBand(band.id, {
                nameBand: editName.trim(),
                description: editDescription.trim() || undefined,
                styleId: editStyleId
            });

            // Reload band to get updated data
            const updatedBand = await bandsService.getOne(band.id);
            setBand(updatedBand.data);
            setShowEditModal(false);
        } catch (err: any) {
            console.error('Erreur modification groupe:', err);
            alert(err.response?.data?.error || 'Erreur lors de la modification');
        } finally {
            setSaving(false);
        }
    };

    if (loading) {
        return <div className="loading">Chargement...</div>;
    }

    if (error || !band) {
        return (
            <div className="error-page">
                <h1>😕 {error || 'Groupe non trouvé'}</h1>
                <Link to="/groupes" className="btn-back">Retour aux groupes</Link>
            </div>
        );
    }

    const formatDate = (dateString: string): string => {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    };

    const admins = band.members?.filter(m => m.isAdmin) || [];
    const regularMembers = band.members?.filter(m => !m.isAdmin) || [];

    return (
        <div className="groupe-detail-page">
            <div className="groupe-detail-container">
                <div className="groupe-detail-header">
                    <div className="groupe-detail-avatar">
                        {band.photoBand ? (
                            <img src={`http://localhost:8000/uploads/bands/${band.photoBand}`} alt={band.nameBand} />
                        ) : (
                            <div className="placeholder-band-detail">
                                <svg viewBox="0 0 24 24" fill="currentColor" width="64" height="64"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                            </div>
                        )}
                    </div>
                    <div className="groupe-detail-info">
                        <h1>{band.nameBand}</h1>
                        {band.style && (
                            <p className="groupe-style">🎵 {band.style.nom_style}</p>
                        )}
                        <p className="groupe-date">📅 Créé le {formatDate(band.dateCreation)}</p>
                        <p className="groupe-members-count">
                            👥 {(band.members?.length) || 0} membre{(band.members?.length || 0) > 1 ? 's' : ''}
                        </p>
                        {band.description && (
                            <p className="groupe-description">{band.description}</p>
                        )}
                        {isCurrentUserAdmin() && (
                            <button className="btn-edit-band" onClick={openEditModal}>
                                Modifier le groupe
                            </button>
                        )}
                    </div>
                </div>

                <div className="groupe-detail-sections">
                    {admins.length > 0 && (
                        <section className="detail-section">
                            <h2>👑 Admin{admins.length > 1 ? 's' : ''} du groupe</h2>
                            <div className="members-grid">
                                {admins.map(member => {
                                    const memberImage = member.user.image
                                        ? `http://localhost:8000/uploads/users/${member.user.image}`
                                        : null;
                                    return (
                                        <div key={member.id} className="member-card admin">
                                            {memberImage ? (
                                                <img src={memberImage} alt={`${member.user.firstName} ${member.user.lastName}`} />
                                            ) : (
                                                <div className="placeholder-member"><svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg></div>
                                            )}
                                            <div className="member-info">
                                                <h4>{member.user.firstName} {member.user.lastName}</h4>
                                                {member.user.city && (
                                                    <p className="member-location">📍 {member.user.city}</p>
                                                )}
                                                {member.description && (
                                                    <p className="member-description">{member.description}</p>
                                                )}
                                                <p className="member-joined">Membre depuis le {formatDate(member.joinedAt)}</p>
                                            </div>
                                            <div className="member-actions">
                                                {member.user.id == currentUser?.id ? (
                                                    <Link to="/profil" className="btn-view-profile">Voir mon profil</Link>
                                                ) : (
                                                    <>
                                                        <Link to={`/musicien/${member.user.id}`} className="btn-view-profile">Voir profil</Link>
                                                        <Link to={`/chat/${member.user.id}`} className="btn-contact-member">💬</Link>
                                                    </>
                                                )}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </section>
                    )}

                    <section className="detail-section">
                        <h2>🎸 Membres</h2>
                        {regularMembers.length > 0 ? (
                            <div className="members-grid">
                                {regularMembers.map(member => {
                                    const memberImage = member.user.image
                                        ? `http://localhost:8000/uploads/users/${member.user.image}`
                                        : null;
                                    return (
                                        <div key={member.id} className="member-card">
                                            {memberImage ? (
                                                <img src={memberImage} alt={`${member.user.firstName} ${member.user.lastName}`} />
                                            ) : (
                                                <div className="placeholder-member"><svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg></div>
                                            )}
                                            <div className="member-info">
                                                <h4>{member.user.firstName} {member.user.lastName}</h4>
                                                {member.user.city && (
                                                    <p className="member-location">📍 {member.user.city}</p>
                                                )}
                                                {member.description && (
                                                    <p className="member-description">{member.description}</p>
                                                )}
                                                <p className="member-joined">Membre depuis le {formatDate(member.joinedAt)}</p>
                                            </div>
                                            <div className="member-actions">
                                                {member.user.id == currentUser?.id ? (
                                                    <Link to="/profil" className="btn-view-profile">Voir mon profil</Link>
                                                ) : (
                                                    <>
                                                        <Link to={`/musicien/${member.user.id}`} className="btn-view-profile">Voir profil</Link>
                                                        <Link to={`/chat/${member.user.id}`} className="btn-contact-member">💬</Link>
                                                    </>
                                                )}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        ) : (
                            <p className="no-data">Aucun autre membre pour le moment</p>
                        )}
                    </section>
                </div>

                <div className="groupe-detail-footer">
                    <Link to="/groupes" className="btn-back">← Retour aux groupes</Link>
                </div>
            </div>

            {/* Edit Band Modal */}
            {showEditModal && (
                <div className="modal-overlay" onClick={() => setShowEditModal(false)}>
                    <div className="modal-content modal-large" onClick={e => e.stopPropagation()}>
                        <h2>✏️ Modifier le groupe</h2>
                        <form onSubmit={handleSaveEdit}>
                            <div className="form-row">
                                <div className="form-group image-upload-group">
                                    <label>Photo du groupe</label>
                                    <div className="image-upload-container">
                                        {imagePreview ? (
                                            <img src={imagePreview} alt="Preview" className="image-preview" />
                                        ) : (
                                            <div className="image-placeholder">📷</div>
                                        )}
                                        <input
                                            type="file"
                                            accept="image/*"
                                            onChange={handleImageChange}
                                            id="bandImage"
                                            className="hidden-input"
                                        />
                                        <label htmlFor="bandImage" className="btn-upload">
                                            📁 Choisir une image
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div className="form-group">
                                <label htmlFor="bandName">Nom du groupe *</label>
                                <input
                                    type="text"
                                    id="bandName"
                                    value={editName}
                                    onChange={e => setEditName(e.target.value)}
                                    placeholder="Nom du groupe"
                                    required
                                />
                            </div>
                            
                            <div className="form-group">
                                <label htmlFor="bandDescription">Description</label>
                                <textarea
                                    id="bandDescription"
                                    value={editDescription}
                                    onChange={e => setEditDescription(e.target.value)}
                                    placeholder="Décrivez votre groupe..."
                                    rows={4}
                                />
                            </div>
                            
                            <div className="form-group">
                                <label htmlFor="bandStyle">Style musical</label>
                                <select
                                    id="bandStyle"
                                    value={editStyleId || ''}
                                    onChange={e => setEditStyleId(e.target.value ? parseInt(e.target.value) : null)}
                                >
                                    <option value="">-- Choisir un style --</option>
                                    {styles.map(style => (
                                        <option key={style.id} value={style.id}>
                                            {style.nom_style}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            
                            <div className="modal-actions">
                                <button type="button" className="btn-cancel" onClick={() => setShowEditModal(false)}>
                                    Annuler
                                </button>
                                <button type="submit" className="btn-submit" disabled={saving || !editName.trim()}>
                                    {saving ? '⏳ Enregistrement...' : '✅ Enregistrer'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}

export default GroupeDetail;
