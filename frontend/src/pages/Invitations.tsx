import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { invitationsService, bandsService } from '../services/api';

interface Invitation {
    id: number;
    sender: {
        id: number;
        firstName: string;
        lastName: string;
        image?: string;
    };
    createdAt: string;
}

interface BandMember {
    id: number;
    firstName: string;
    lastName: string;
    image?: string;
    isAdmin: boolean;
}

interface PendingBand {
    id: number;
    nameBand: string;
    members: BandMember[];
    dateCreation: string;
}

function Invitations() {
    const navigate = useNavigate();
    const [invitations, setInvitations] = useState<Invitation[]>([]);
    const [pendingBands, setPendingBands] = useState<PendingBand[]>([]);
    const [loading, setLoading] = useState(true);
    const [responding, setResponding] = useState<number | null>(null);
    
    // Band setup state
    const [setupBandId, setSetupBandId] = useState<number | null>(null);
    const [bandName, setBandName] = useState('');
    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        loadData();
    }, []);

    const loadData = async () => {
        try {
            // Load invitations
            const invitationsRes = await invitationsService.getPending();
            setInvitations(invitationsRes.data);
        } catch (err) {
            console.error('Erreur chargement invitations:', err);
        }
        
        try {
            // Load pending bands separately
            const bandsRes = await bandsService.getPendingSetup();
            setPendingBands(bandsRes.data);
        } catch (err) {
            console.error('Erreur chargement groupes:', err);
        }
        
        setLoading(false);
    };

    const handleRespond = async (invitationId: number, accept: boolean) => {
        setResponding(invitationId);
        try {
            await invitationsService.respond(invitationId, accept);
            setInvitations(prev => prev.filter(inv => inv.id !== invitationId));
            
            if (accept) {
                alert('Invitation acceptée ! Le groupe a été créé. L\'admin du groupe peut maintenant le configurer.');
            }
        } catch (err: any) {
            console.error('Erreur réponse invitation:', err);
            alert(err.response?.data?.error || 'Erreur lors de la réponse');
        } finally {
            setResponding(null);
        }
    };

    const handleSetupBand = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!setupBandId || !bandName.trim()) return;
        
        setSubmitting(true);
        try {
            await bandsService.setupBand(setupBandId, { nameBand: bandName.trim() });
            setPendingBands(prev => prev.filter(b => b.id !== setupBandId));
            setSetupBandId(null);
            setBandName('');
            // Redirect to the new band page
            navigate(`/groupe/${setupBandId}`);
        } catch (err: any) {
            console.error('Erreur configuration groupe:', err);
            alert(err.response?.data?.error || 'Erreur lors de la configuration');
        } finally {
            setSubmitting(false);
        }
    };

    const openSetupModal = (band: PendingBand) => {
        setSetupBandId(band.id);
        setBandName('');
    };

    const formatDate = (dateString: string): string => {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    if (loading) {
        return <div className="loading">Chargement...</div>;
    }

    const hasNotifications = invitations.length > 0 || pendingBands.length > 0;

    return (
        <div className="invitations-page">
            <div className="invitations-container">
                <h1>🔔 Notifications</h1>
                
                {!hasNotifications ? (
                    <div className="no-invitations">
                        <p>Aucune notification</p>
                        <Link to="/musiciens" className="btn-browse">Parcourir les musiciens</Link>
                    </div>
                ) : (
                    <>
                        {/* Bands pending setup - shown first as they require action */}
                        {pendingBands.length > 0 && (
                            <div className="notification-section">
                                <h2>🎸 Groupes à configurer</h2>
                                <p className="section-description">
                                    Votre invitation a été acceptée ! Configurez votre nouveau groupe.
                                </p>
                                <div className="invitations-list">
                                    {pendingBands.map(band => {
                                        const otherMembers = band.members.filter(m => !m.isAdmin);
                                        
                                        return (
                                            <div key={band.id} className="invitation-card band-setup-card">
                                                <div className="invitation-sender">
                                                    <div className="band-members-preview">
                                                        {band.members.map(member => {
                                                            const memberImage = member.image
                                                                ? `http://localhost:8000/uploads/users/${member.image}`
                                                                : '/default-avatar.png';
                                                            return (
                                                                <img 
                                                                    key={member.id} 
                                                                    src={memberImage} 
                                                                    alt={`${member.firstName} ${member.lastName}`}
                                                                    title={`${member.firstName} ${member.lastName}${member.isAdmin ? ' (Admin)' : ''}`}
                                                                    className="member-preview-img"
                                                                />
                                                            );
                                                        })}
                                                    </div>
                                                    <div className="sender-info">
                                                        <h3>Nouveau groupe avec {otherMembers.map(m => m.firstName).join(', ')}</h3>
                                                        <p className="invitation-text">
                                                            Créé le {formatDate(band.dateCreation)}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div className="invitation-actions">
                                                    <button
                                                        className="btn-setup"
                                                        onClick={() => openSetupModal(band)}
                                                    >
                                                        ⚙️ Configurer le groupe
                                                    </button>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        )}

                        {/* Received invitations */}
                        {invitations.length > 0 && (
                            <div className="notification-section">
                                <h2>📩 Invitations reçues</h2>
                                <div className="invitations-list">
                                    {invitations.map(invitation => {
                                        const senderImage = invitation.sender.image
                                            ? `http://localhost:8000/uploads/users/${invitation.sender.image}`
                                            : '/default-avatar.png';
                                        
                                        return (
                                            <div key={invitation.id} className="invitation-card">
                                                <div className="invitation-sender">
                                                    <img src={senderImage} alt={`${invitation.sender.firstName} ${invitation.sender.lastName}`} />
                                                    <div className="sender-info">
                                                        <h3>{invitation.sender.firstName} {invitation.sender.lastName}</h3>
                                                        <p className="invitation-text">
                                                            souhaite créer un groupe avec vous
                                                        </p>
                                                        <p className="invitation-date">
                                                            Reçue le {formatDate(invitation.createdAt)}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div className="invitation-actions">
                                                    <Link to={`/musicien/${invitation.sender.id}`} className="btn-view-sender">
                                                        Voir le profil
                                                    </Link>
                                                    <button
                                                        className="btn-accept"
                                                        onClick={() => handleRespond(invitation.id, true)}
                                                        disabled={responding === invitation.id}
                                                    >
                                                        {responding === invitation.id ? '⏳' : '✅ Accepter'}
                                                    </button>
                                                    <button
                                                        className="btn-reject"
                                                        onClick={() => handleRespond(invitation.id, false)}
                                                        disabled={responding === invitation.id}
                                                    >
                                                        {responding === invitation.id ? '⏳' : '❌ Refuser'}
                                                    </button>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        )}
                    </>
                )}
            </div>

            {/* Band Setup Modal */}
            {setupBandId && (
                <div className="modal-overlay" onClick={() => setSetupBandId(null)}>
                    <div className="modal-content" onClick={e => e.stopPropagation()}>
                        <h2>⚙️ Configurer votre groupe</h2>
                        <form onSubmit={handleSetupBand}>
                            <div className="form-group">
                                <label htmlFor="bandName">Nom du groupe *</label>
                                <input
                                    type="text"
                                    id="bandName"
                                    value={bandName}
                                    onChange={e => setBandName(e.target.value)}
                                    placeholder="Ex: Les Rockeurs du Dimanche"
                                    required
                                    autoFocus
                                />
                            </div>
                            <div className="modal-actions">
                                <button type="button" className="btn-cancel" onClick={() => setSetupBandId(null)}>
                                    Annuler
                                </button>
                                <button type="submit" className="btn-submit" disabled={submitting || !bandName.trim()}>
                                    {submitting ? '⏳ Création...' : '✅ Créer le groupe'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}

export default Invitations;
