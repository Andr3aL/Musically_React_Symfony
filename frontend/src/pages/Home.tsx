import { useState, useEffect } from 'react';
import type { FormEvent } from 'react';
import { usersService } from '../services/api';
import MusicianCard from '../components/MusicianCard';
import type { User } from '../types';

interface HomeProps {
    currentUser: User | null;
}

function Home({ currentUser }: HomeProps) {
    const [musicians, setMusicians] = useState<User[]>([]);
    const [nearbyMusicians, setNearbyMusicians] = useState<User[]>([]);
    const [searchQuery, setSearchQuery] = useState<string>('');
    const [loading, setLoading] = useState<boolean>(true);

    useEffect(() => {
        loadMusicians();
    }, []);

    const loadMusicians = async (): Promise<void> => {
        try {
            const response = await usersService.getAll();
            const allMusicians = response.data['hydra:member'] || [];
            
            // Filtrer les musiciens proches (même ville)
            const nearby = allMusicians.filter(m => m.city === currentUser?.city);
            const others = allMusicians.filter(m => m.city !== currentUser?.city);
            
            setNearbyMusicians(nearby);
            setMusicians(others);
        } catch (error) {
            console.error('Erreur:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleSearch = (e: FormEvent<HTMLFormElement>): void => {
        e.preventDefault();
        // Implémenter la recherche
        console.log('Recherche:', searchQuery);
    };

    const handleContact = (user: User): void => {
        // Rediriger vers le chat
        window.location.href = `/chat/${user.id}`;
    };

    if (loading) {
        return <div className="loading">Chargement...</div>;
    }

    return (
        <div className="home-page">
            <section className="hero">
                <h1>Rencontrez vos prochains alliés musicaux !</h1>
                <p>Trouvez des musiciens près de chez vous</p>
                
                <form onSubmit={handleSearch} className="search-form">
                    <input
                        type="text"
                        placeholder="Rechercher par nom, ville, instrument ou style musical..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                    />
                    <button type="submit">Rechercher</button>
                </form>
            </section>

            {nearbyMusicians.length > 0 && (
                <section className="section">
                    <h2>Musiciens près de chez vous ({currentUser?.city})</h2>
                    <div className="musicians-grid">
                        {nearbyMusicians.map(musician => (
                            <MusicianCard 
                                key={musician.id} 
                                user={musician} 
                                onContact={handleContact}
                            />
                        ))}
                    </div>
                </section>
            )}

            <section className="section">
                <h2>Autres musiciens</h2>
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

export default Home;
