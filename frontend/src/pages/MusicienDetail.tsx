import { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { usersService, invitationsService } from '../services/api';
import type { User } from '../types';

interface MusicienDetailProps {
    currentUser: User | null;
}

function MusicienDetail({ currentUser }: MusicienDetailProps) {
    const { id } = useParams<{ id: string }>();
    const [musician, setMusician] = useState<User | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [shareBand, setShareBand] = useState(false);
    const [hasPendingInvitation, setHasPendingInvitation] = useState(false);
    const [invitationSent, setInvitationSent] = useState(false);
    const [sendingInvitation, setSendingInvitation] = useState(false);

    useEffect(() => {
        if (id) {
            loadMusician(parseInt(id));
        }
    }, [id]);

    useEffect(() => {
        if (musician && currentUser && musician.id !== currentUser.id) {
            checkShareBand(musician.id);
        }
    }, [musician, currentUser]);

    const loadMusician = async (musicianId: number) => {
        try {
            const response = await usersService.getOne(musicianId);
            setMusician(response.data);
        } catch (err) {
            console.error('Erreur chargement musicien:', err);
            setError('Musicien non trouvé');
        } finally {
            setLoading(false);
        }
    };

    const checkShareBand = async (userId: number) => {
        try {
            const response = await invitationsService.checkShareBand(userId);
            setShareBand(response.data.shareBand);
            setHasPendingInvitation(response.data.hasPendingInvitation);
        } catch (err) {
            console.error('Erreur vérification groupe:', err);
        }
    };

    const handleSendInvitation = async () => {
        if (!musician) return;
        
        setSendingInvitation(true);
        try {
            await invitationsService.sendInvitation(musician.id);
            setInvitationSent(true);
            setHasPendingInvitation(true);
        } catch (err: any) {
            console.error('Erreur envoi invitation:', err);
            alert(err.response?.data?.error || 'Erreur lors de l\'envoi de l\'invitation');
        } finally {
            setSendingInvitation(false);
        }
    };

    if (loading) {
        return <div className="loading">Chargement...</div>;
    }

    if (error || !musician) {
        return (
            <div className="error-page">
                <h1>😕 {error || 'Musicien non trouvé'}</h1>
                <Link to="/musiciens" className="btn-back">Retour aux musiciens</Link>
            </div>
        );
    }

    const imageUrl = musician.image 
        ? `http://localhost:8000/uploads/users/${musician.image}` 
        : '/default-avatar.png';

    const isOwnProfile = currentUser && currentUser.id === musician.id;
    const canInvite = !isOwnProfile && !shareBand && !hasPendingInvitation;

    return (
        <div className="musician-detail-page">
            <div className="musician-detail-container">
                <div className="musician-detail-header">
                    <div className="musician-detail-avatar">
                        <img src={imageUrl} alt={`${musician.firstName} ${musician.lastName}`} />
                    </div>
                    <div className="musician-detail-info">
                        <h1>{musician.firstName} {musician.lastName}</h1>
                        <p className="musician-location">📍 {musician.city}, {musician.country}</p>
                        <div className="musician-tags">
                            {musician.mainInstrument && (
                                <span className="tag instrument">🎸 {musician.mainInstrument}</span>
                            )}
                            {musician.mainStyle && (
                                <span className="tag style">🎵 {musician.mainStyle}</span>
                            )}
                        </div>
                        {!isOwnProfile && (
                            <div className="musician-actions">
                                <Link to={`/chat/${musician.id}`} className="btn-contact">
                                    💬 Contacter
                                </Link>
                                {canInvite && (
                                    <button 
                                        className="btn-invite"
                                        onClick={handleSendInvitation}
                                        disabled={sendingInvitation}
                                    >
                                        {sendingInvitation ? '⏳ Envoi...' : '🎸 Inviter à créer un groupe'}
                                    </button>
                                )}
                                {hasPendingInvitation && !invitationSent && (
                                    <span className="invitation-pending">📩 Invitation en attente</span>
                                )}
                                {invitationSent && (
                                    <span className="invitation-sent">✅ Invitation envoyée !</span>
                                )}
                                {shareBand && (
                                    <span className="already-in-band">🎵 Vous êtes déjà dans un groupe ensemble</span>
                                )}
                            </div>
                        )}
                    </div>
                </div>

                <div className="musician-detail-sections">
                    <section className="detail-section">
                        <h2>À propos</h2>
                        <div className="info-grid">
                            <div className="info-item">
                                <span className="info-label">Email</span>
                                <span className="info-value">{musician.email}</span>
                            </div>
                            {musician.city && (
                                <div className="info-item">
                                    <span className="info-label">Ville</span>
                                    <span className="info-value">{musician.city}</span>
                                </div>
                            )}
                            {musician.country && (
                                <div className="info-item">
                                    <span className="info-label">Pays</span>
                                    <span className="info-value">{musician.country}</span>
                                </div>
                            )}
                        </div>
                    </section>

                    <section className="detail-section">
                        <h2>Styles musicaux</h2>
                        {musician.userStyles && musician.userStyles.length > 0 ? (
                            <div className="tags-list">
                                {musician.userStyles.map((us) => (
                                    <span key={us.id} className={`tag ${us.isPrincipal ? 'principal' : ''}`}>
                                        {us.style.nom_style}
                                        {us.isPrincipal && <span className="badge-principal">Principal</span>}
                                    </span>
                                ))}
                            </div>
                        ) : (
                            <p className="no-data">Aucun style renseigne</p>
                        )}
                    </section>

                    <section className="detail-section">
                        <h2>Instruments</h2>
                        {musician.userInstruments && musician.userInstruments.length > 0 ? (
                            <div className="instruments-list">
                                {musician.userInstruments.map((ui) => (
                                    <div key={ui.id} className={`instrument-item ${ui.isMain ? 'instrument-main' : ''}`}>
                                        <div className="instrument-name-wrap">
                                            <span className="instrument-name">{ui.instrument.nom_instrument}</span>
                                            {ui.isMain && <span className="badge-principal">Principal</span>}
                                        </div>
                                        <span className={`niveau niveau-${ui.niveau.toLowerCase()}`}>
                                            {ui.niveau}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="no-data">Aucun instrument renseigne</p>
                        )}
                    </section>
                </div>

                <div className="musician-detail-footer">
                    <Link to="/musiciens" className="btn-back">← Retour aux musiciens</Link>
                </div>
            </div>
        </div>
    );
}

export default MusicienDetail;