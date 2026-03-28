import { Link } from 'react-router-dom';

function APropos() {
    return (
        <div className="apropos-page">
            <section className="hero">
                <h1>Connecter les musiciens, créer des harmonies</h1>
                <p>Bâtir l'avenir musical de demain</p>
            </section>

            <section className="apropos-section">
                <h2>À propos de notre plateforme</h2>
                <p className="apropos-intro">
                    Notre mission : réunir les passionnés de musique et faciliter la création de groupes musicaux exceptionnels.
                </p>
                <p className="apropos-text">
                    Music'Ally est née de l'envie de connecter les musiciens entre eux. Que vous soyez débutant ou professionnel,
                    notre plateforme vous aide à trouver vos partenaires musicaux idéaux pour créer, collaborer et partager
                    votre passion.
                </p>
            </section>

            <section className="apropos-section">
                <h2>Comment ça marche ?</h2>
                <div className="apropos-steps">
                    <div className="step-card">
                        <div className="step-icon">1</div>
                        <h3>Créez votre profil</h3>
                        <p>Inscrivez-vous et renseignez votre instrument, votre style musical et votre localisation.</p>
                    </div>
                    <div className="step-card">
                        <div className="step-icon">2</div>
                        <h3>Découvrez des musiciens</h3>
                        <p>Parcourez les profils de musiciens près de chez vous et filtrez par instrument ou style.</p>
                    </div>
                    <div className="step-card">
                        <div className="step-icon">3</div>
                        <h3>Connectez-vous</h3>
                        <p>Utilisez la messagerie intégrée pour contacter des musiciens et organiser des sessions.</p>
                    </div>
                </div>
            </section>

            <section className="apropos-section">
                <h2>Nos valeurs</h2>
                <div className="apropos-values">
                    <div className="value-card">
                        <div className="value-icon">🎵</div>
                        <h3>Passion</h3>
                        <p>La musique est au coeur de tout ce que nous faisons. Elle unit les gens au-delà des frontières.</p>
                    </div>
                    <div className="value-card">
                        <div className="value-icon">🤝</div>
                        <h3>Communauté</h3>
                        <p>Un espace bienveillant où chaque musicien trouve sa place, quel que soit son niveau.</p>
                    </div>
                    <div className="value-card">
                        <div className="value-icon">🔒</div>
                        <h3>Sécurité</h3>
                        <p>Vos données personnelles sont protégées. Votre vie privée est notre priorité.</p>
                    </div>
                    <div className="value-card">
                        <div className="value-icon">✨</div>
                        <h3>Simplicité</h3>
                        <p>Une interface intuitive pour vous concentrer sur l'essentiel : la musique.</p>
                    </div>
                </div>
            </section>

            <section className="apropos-section">
                <h2>Instruments supportés</h2>
                <div className="apropos-tags">
                    {['Guitare', 'Batterie', 'Basse', 'Clavier', 'Chant', 'Violon', 'Saxophone', 'Trompette', 'Flûte', 'Harpe', 'Accordéon', 'Ukulélé', 'Djembé', 'Synthétiseur', 'et bien d\'autres...'].map(tag => (
                        <span key={tag} className="tag">{tag}</span>
                    ))}
                </div>
            </section>

            <section className="apropos-section">
                <h2>Styles musicaux</h2>
                <div className="apropos-tags">
                    {['Rock', 'Jazz', 'Metal', 'Hip-hop', 'Pop', 'Blues', 'Reggae', 'Funk', 'Soul', 'Classique', 'Électronique', 'Country', 'Afrobeat', 'Bossa Nova', 'et plus encore...'].map(tag => (
                        <span key={tag} className="tag tag-style">{tag}</span>
                    ))}
                </div>
            </section>

            <section className="apropos-cta">
                <h2>Prêt à rejoindre la communauté ?</h2>
                <div className="cta-buttons">
                    <Link to="/" className="cta-btn cta-primary">Découvrir les musiciens</Link>
                    <Link to="/groupes" className="cta-btn cta-secondary">Voir les groupes</Link>
                </div>
            </section>
        </div>
    );
}

export default APropos;
