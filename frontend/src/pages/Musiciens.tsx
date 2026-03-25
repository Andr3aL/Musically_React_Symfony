import { useState, useEffect } from 'react';
import { usersService } from '../services/api';
import MusicianCard from '../components/MusicianCard';
import type { User } from '../types';

function Musiciens() {
    const [musicians, setMusicians] = useState<User[]>([]);
    const [loading, setLoading] = useState<boolean>(true);

    useEffect(() => {
        loadMusicians();
    }, []);

    const loadMusicians = async (): Promise<void> => {
        try {
            const response = await usersService.getAll();
            setMusicians(response.data['hydra:member'] || []);
        } catch (error) {
            console.error('Erreur:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleContact = (user: User): void => {
        window.location.href = `/chat/${user.id}`;
    };

    if (loading) {
        return <div className="loading">Chargement...</div>;
    }

    return (
        <div className="musiciens-page">
            <section className="hero">
                <h1>Parcourez les musiciens de notre réseau !</h1>
            </section>

            <section className="section">
                <h2>Nos musiciens</h2>
                <div className="musicians-grid">
                    {musicians.map(musician => (
                        <MusicianCard 
                            key={musician.id} 
                            user={musician} 
                            onContact={handleContact}
                        />
                    ))}
                </div>
            </section>
        </div>
    );
}

export default Musiciens;
