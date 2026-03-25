import { Link } from 'react-router-dom';
import type { User } from '../types';

interface MusicianCardProps {
    user: User;
    onContact: (user: User) => void;
}

function MusicianCard({ user, onContact }: MusicianCardProps) {
    const imageUrl = user.image 
        ? `http://localhost:8000/uploads/users/${user.image}` 
        : '/default-avatar.png';

    return (
        <div className="musician-card">
            <div className="musician-image">
                <img src={imageUrl} alt={`${user.firstName} ${user.lastName}`} />
            </div>
            <div className="musician-info">
                <h3>{user.lastName} {user.firstName}</h3>
                <p className="instrument">{user.mainInstrument || 'Musicien'}</p>
                <p className="style">{user.mainStyle || 'Divers'}</p>
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
