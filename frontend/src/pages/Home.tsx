import { useState, useEffect } from 'react';
import type { FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { usersService } from '../services/api';
import MusicianCard from '../components/MusicianCard';
import type { User } from '../types';

interface HomeProps {
    currentUser: User | null;
}

const INITIAL_LIMIT = 10;

function Home({ currentUser }: HomeProps) {
    const navigate = useNavigate();
    const [musicians, setMusicians] = useState<User[]>([]);
    const [nearbyMusicians, setNearbyMusicians] = useState<User[]>([]);
    const [searchQuery, setSearchQuery] = useState<string>('');
    const [loading, setLoading] = useState<boolean>(true);
    const [searchResults, setSearchResults] = useState<User[] | null>(null);
    const [searching, setSearching] = useState<boolean>(false);
    const [showAll, setShowAll] = useState<boolean>(false);

    useEffect(() => {
        loadMusicians();
    }, []);

    const loadMusicians = async (): Promise<void> => {
        try {
            const response = await usersService.getAll();
            const allMusicians = (response.data['hydra:member'] || [])
                .filter((m: User) => m.id !== currentUser?.id);

            const nearby = allMusicians.filter((m: User) => m.city === currentUser?.city);
            const others = allMusicians.filter((m: User) => m.city !== currentUser?.city);

            setNearbyMusicians(nearby);
            setMusicians(others);
        } catch (error) {
            console.error('Erreur:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleSearch = async (e: FormEvent<HTMLFormElement>): Promise<void> => {
        e.preventDefault();
        const query = searchQuery.trim();
        if (!query) {
            setSearchResults(null);
            return;
        }
        setSearching(true);
        try {
            const response = await usersService.search(query);
            setSearchResults(response.data.filter((m: User) => m.id !== currentUser?.id));
        } catch (error) {
            console.error('Erreur recherche:', error);
        } finally {
            setSearching(false);
        }
    };

    const clearSearch = (): void => {
        setSearchQuery('');
        setSearchResults(null);
    };

    const handleContact = (user: User): void => {
        navigate(`/chat/${user.id}`);
    };

    if (loading) {
        return <div className="loading">Chargement...</div>;
    }

    const displayedOthers = showAll ? musicians : musicians.slice(0, INITIAL_LIMIT);
    const hasMore = musicians.length > INITIAL_LIMIT;

    return (
        <div className="home-page">
            <section className="hero">
                <h1>Rencontrez vos prochains allies musicaux !</h1>
                <p>Trouvez des musiciens pres de chez vous</p>

                <form onSubmit={handleSearch} className="search-form">
                    <input
                        type="text"
                        placeholder="Rechercher par nom, ville, instrument ou style musical..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                    />
                    <button type="submit" disabled={searching}>
                        {searching ? 'Recherche...' : 'Rechercher'}
                    </button>
                </form>
            </section>

            {searchResults !== null ? (
                <section className="section">
                    <div className="search-results-header">
                        <h2>Resultats pour "{searchQuery}" ({searchResults.length})</h2>
                        <button onClick={clearSearch} className="btn-clear-search">Effacer la recherche</button>
                    </div>
                    {searchResults.length > 0 ? (
                        <div className="musicians-grid">
                            {searchResults.map(musician => (
                                <MusicianCard
                                    key={musician.id}
                                    user={musician}
                                    onContact={handleContact}
                                />
                            ))}
                        </div>
                    ) : (
                        <p className="no-results">Aucun musicien trouve pour cette recherche.</p>
                    )}
                </section>
            ) : (
                <>
                    {nearbyMusicians.length > 0 && (
                        <section className="section section-nearby">
                            <h2>Musiciens pres de chez vous ({currentUser?.city})</h2>
                            <div className="musicians-grid">
                                {nearbyMusicians.map(musician => (
                                    <MusicianCard
                                        key={musician.id}
                                        user={musician}
                                        onContact={handleContact}
                                        nearby
                                    />
                                ))}
                            </div>
                        </section>
                    )}

                    <section className="section">
                        <h2>Autres musiciens</h2>
                        <div className="musicians-grid">
                            {displayedOthers.map(musician => (
                                <MusicianCard
                                    key={musician.id}
                                    user={musician}
                                    onContact={handleContact}
                                />
                            ))}
                        </div>
                        {hasMore && !showAll && (
                            <div className="show-more-container">
                                <button className="btn-show-more" onClick={() => setShowAll(true)}>
                                    Voir plus de musiciens ({musicians.length - INITIAL_LIMIT} restants)
                                </button>
                            </div>
                        )}
                    </section>
                </>
            )}
        </div>
    );
}

export default Home;
