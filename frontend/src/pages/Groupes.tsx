import { useState, useEffect } from 'react';
import { bandsService } from '../services/api';
import BandCard from '../components/BandCard';
import type { Band } from '../types';

function Groupes() {
    const [bands, setBands] = useState<Band[]>([]);
    const [loading, setLoading] = useState<boolean>(true);

    useEffect(() => {
        loadBands();
    }, []);

    const loadBands = async (): Promise<void> => {
        try {
            const response = await bandsService.getAll();
            setBands(response.data['hydra:member'] || []);
        } catch (error) {
            console.error('Erreur:', error);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return <div className="loading">Chargement...</div>;
    }

    return (
        <div className="groupes-page">
            <section className="section">
                <div className="section-header">
                    <h2>Groupes de musiciens</h2>
                    <button className="btn-create">+ Créer un groupe</button>
                </div>
                <div className="bands-grid">
                    {bands.map(band => (
                        <BandCard key={band.id} band={band} />
                    ))}
                </div>
            </section>
        </div>
    );
}

export default Groupes;
