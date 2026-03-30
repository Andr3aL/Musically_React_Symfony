import { Link } from 'react-router-dom';
import type { Band } from '../types';

interface BandCardProps {
    band: Band;
}

function BandCard({ band }: BandCardProps) {
    const formatDate = (dateString: string): string => {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR');
    };

    return (
        <div className="band-card">
            <div className="band-image">
                {band.photoBand ? (
                    <img src={`http://localhost:8000/uploads/bands/${band.photoBand}`} alt={band.nameBand} />
                ) : (
                    <div className="placeholder-band">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="48" height="48"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                    </div>
                )}
            </div>
            <div className="band-info">
                <h3>{band.nameBand}</h3>
                <p className="members">
                    <span className="icon">👥</span> {band.members?.length || band.membersCount || 1} membre{(band.members?.length || band.membersCount || 1) > 1 ? 's' : ''}
                </p>
                <p className="date">
                    <span className="icon">📅</span> Créé le {formatDate(band.dateCreation)}
                </p>
            </div>
            <Link to={`/groupe/${band.id}`} className="btn-view">Voir le groupe</Link>
        </div>
    );
}

export default BandCard;
