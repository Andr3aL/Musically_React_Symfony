import { Link } from 'react-router-dom';
import type { User } from '../types';

interface MusicianCardProps {
    user: User;
    onContact: (user: User) => void;
    nearby?: boolean;
}

function MusicianCard({ user, onContact, nearby }: MusicianCardProps) {
    // Extract main instrument from userInstruments or fallback to mainInstrument
    const mainInstrument = user.mainInstrument
        || user.userInstruments?.find(ui => ui.isMain)?.instrument?.nom_instrument
        || user.userInstruments?.[0]?.instrument?.nom_instrument
        || 'Musicien';

    // Extract main style from userStyles or fallback to mainStyle
    const mainStyle = user.mainStyle
        || user.userStyles?.find(us => us.isPrincipal)?.style?.nom_style
        || user.userStyles?.[0]?.style?.nom_style
        || 'Divers';

    return (
        <div className={`musician-card ${nearby ? 'musician-card-nearby' : ''}`}>
            <div className="musician-image">
                {user.image ? (
                    <img src={`http://localhost:8000/uploads/users/${user.image}`} alt={`${user.firstName} ${user.lastName}`} />
                ) : (
                    <div className="placeholder-user">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="48" height="48"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
                    </div>
                )}
            </div>
            <div className="musician-info">
                <h3>{user.lastName} {user.firstName}</h3>
                <p className="instrument">{mainInstrument}</p>
                <p className="style">{mainStyle}</p>
                <p className="city">{user.city}</p>
            </div>
            <div className="musician-actions">
                <button onClick={() => onContact(user)} className="btn-contact">Contacter</button>
                <Link to={`/musicien/${user.id}`} className="btn-profile">Voir profil</Link>
            </div>
        </div>
    );
}

export default MusicianCard;
