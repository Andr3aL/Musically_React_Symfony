import { useState, useEffect } from 'react';
import type { FormEvent } from 'react';
import { usersService } from '../services/api';
import MusicianCard from '../components/MusicianCard';
import type { User } from '../types';

function Musiciens() {
    const [musicians, setMusicians] = useState<User[]>([]);
    const [loading, setLoading] = useState<boolean>(true);
    const [searchQuery, setSearchQuery] = useState<string>('');
    const [searchResults, setSearchResults] = useState<User[] | null>(null);
    const [searching, setSearching] = useState<boolean>(false);

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
            setSearchResults(response.data);
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
        window.location.href = `/chat/${user.id}`;
    };

    if (loading) {
        return <div className="loading">Chargement...</div>;
    }

    const displayedMusicians = searchResults !== null ? searchResults : musicians;

    return (
        <div className="musiciens-page">
            <section className="hero">
                <h1>Parcourez les musiciens de notre réseau !</h1>
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

            <section className="section">
                {searchResults !== null ? (
                    <div className="search-results-header">
                        <h2>Résultats pour "{searchQuery}" ({searchResults.length})</h2>
                        <button onClick={clearSearch} className="btn-clear-search">Effacer la recherche</button>
                    </div>
                ) : (
                    <h2>Nos musiciens</h2>
                )}
                {displayedMusicians.length > 0 ? (
                    <div className="musicians-grid">
                        {displayedMusicians.map(musician => (
                            <MusicianCard
                                key={musician.id}
                                user={musician}
                                onContact={handleContact}
                            />
                        ))}
                    </div>
                ) : (
                    <p className="no-results">Aucun musicien trouvé pour cette recherche.</p>
                )}
            </section>
        </div>
    );
}

export default Musiciens;
