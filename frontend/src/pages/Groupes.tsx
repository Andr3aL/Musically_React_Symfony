import { useState, useEffect } from 'react';
import type { FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { bandsService } from '../services/api';
import BandCard from '../components/BandCard';
import type { Band, User } from '../types';

interface GroupesProps {
    currentUser: User | null;
}

function Groupes({ currentUser }: GroupesProps) {
    const navigate = useNavigate();
    const [myBands, setMyBands] = useState<Band[]>([]);
    const [otherBands, setOtherBands] = useState<Band[]>([]);
    const [loading, setLoading] = useState<boolean>(true);
    const [searchQuery, setSearchQuery] = useState<string>('');
    const [searchResults, setSearchResults] = useState<Band[] | null>(null);
    const [searching, setSearching] = useState<boolean>(false);
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [newBandName, setNewBandName] = useState('');
    const [newBandDesc, setNewBandDesc] = useState('');
    const [creating, setCreating] = useState(false);

    useEffect(() => {
        loadBands();
    }, []);

    const loadBands = async (): Promise<void> => {
        try {
            const response = await bandsService.getAll();
            const allBands: Band[] = response.data['hydra:member'] || [];

            // Separate my bands from others
            const mine: Band[] = [];
            const others: Band[] = [];

            allBands.forEach(band => {
                const isMember = band.members?.some(m => m.user?.id == currentUser?.id);
                if (isMember) {
                    mine.push(band);
                } else {
                    others.push(band);
                }
            });

            setMyBands(mine);
            setOtherBands(others);
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
            const response = await bandsService.search(query);
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

    const handleCreate = async (e: FormEvent<HTMLFormElement>): Promise<void> => {
        e.preventDefault();
        if (!newBandName.trim()) return;

        setCreating(true);
        try {
            const response = await bandsService.create({
                nameBand: newBandName.trim(),
                description: newBandDesc.trim() || undefined,
            });
            setShowCreateModal(false);
            setNewBandName('');
            setNewBandDesc('');
            navigate(`/groupe/${response.data.id}`);
        } catch (error) {
            console.error('Erreur creation:', error);
        } finally {
            setCreating(false);
        }
    };

    if (loading) {
        return <div className="loading">Chargement...</div>;
    }

    return (
        <div className="groupes-page">
            <section className="hero">
                <h1>Groupes de musiciens</h1>
                <p>Decouvrez les groupes ou creez le votre</p>

                <form onSubmit={handleSearch} className="search-form">
                    <input
                        type="text"
                        placeholder="Rechercher un groupe par nom ou par membre..."
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
                        <div className="bands-grid">
                            {searchResults.map(band => (
                                <BandCard key={band.id} band={band} />
                            ))}
                        </div>
                    ) : (
                        <p className="no-results">Aucun groupe trouve pour cette recherche.</p>
                    )}
                </section>
            ) : (
                <>
                    {myBands.length > 0 && (
                        <section className="section section-my-bands">
                            <h2>Mes groupes</h2>
                            <div className="bands-grid">
                                {myBands.map(band => (
                                    <BandCard key={band.id} band={band} />
                                ))}
                            </div>
                        </section>
                    )}

                    <section className="section">
                        <div className="section-header">
                            <h2>Tous les groupes</h2>
                            <button className="btn-create" onClick={() => setShowCreateModal(true)}>
                                + Creer un groupe
                            </button>
                        </div>
                        <div className="bands-grid">
                            {otherBands.map(band => (
                                <BandCard key={band.id} band={band} />
                            ))}
                        </div>
                    </section>
                </>
            )}

            {showCreateModal && (
                <div className="modal-overlay" onClick={() => setShowCreateModal(false)}>
                    <div className="modal-content" onClick={e => e.stopPropagation()}>
                        <h2>Creer un groupe</h2>
                        <form onSubmit={handleCreate}>
                            <div className="form-group">
                                <label htmlFor="createBandName">Nom du groupe *</label>
                                <input
                                    type="text"
                                    id="createBandName"
                                    value={newBandName}
                                    onChange={e => setNewBandName(e.target.value)}
                                    placeholder="Ex: Les Rockeurs du Dimanche"
                                    required
                                    autoFocus
                                />
                            </div>
                            <div className="form-group">
                                <label htmlFor="createBandDesc">Description</label>
                                <textarea
                                    id="createBandDesc"
                                    value={newBandDesc}
                                    onChange={e => setNewBandDesc(e.target.value)}
                                    placeholder="Decrivez votre groupe..."
                                    rows={3}
                                />
                            </div>
                            <div className="modal-actions">
                                <button type="button" className="btn-cancel" onClick={() => setShowCreateModal(false)}>
                                    Annuler
                                </button>
                                <button type="submit" className="btn-submit" disabled={creating || !newBandName.trim()}>
                                    {creating ? 'Creation...' : 'Creer le groupe'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}

export default Groupes;
