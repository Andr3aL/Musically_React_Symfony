import { Link } from 'react-router-dom';

function Footer() {
    return (
        <footer className="footer">
            <p>© 2025 Music'Ally. All rights reserved.</p>
            <div className="footer-links">
                <Link to="/">Accueil</Link>
                <Link to="/musiciens">Musiciens</Link>
                <Link to="/groupes">Groupes</Link>
                <Link to="/a-propos">A propos</Link>
            </div>
        </footer>
    );
}

export default Footer;
