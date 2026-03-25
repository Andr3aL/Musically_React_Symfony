import { Link } from 'react-router-dom';
import type { Band } from '../types';

interface BandCardProps {
    band: Band;
}

function BandCard({ band }: BandCardProps) {
    const imageUrl = band.photoBand 
        ? `http://localhost:8000/uploads/bands/${band.photoBand}` 
        : '/default-band.png';

    const formatDate = (dateString: string): string => {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR');
    };

    return (
        <div className="band-card">
            <div className="band-image">
                <img src={imageUrl} alt={band.nameBand} />
            </div>
            <div className="band-info">
                <h3>{band.nameBand}</h3>
                <p className="members">
                    <span className="icon">👥</span> {band.membersCount || 1} membre{(band.membersCount || 1) > 1 ? 's' : ''}
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
