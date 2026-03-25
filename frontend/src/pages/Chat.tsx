import { useState, useEffect, useRef } from 'react';
import { useParams, Link } from 'react-router-dom';
import { usersService } from '../services/api';
import api from '../services/api';
import type { User, Message } from '../types';

interface ChatProps {
    currentUser: User | null;
}

function Chat({ currentUser }: ChatProps) {
    const { userId } = useParams<{ userId: string }>();
    const [otherUser, setOtherUser] = useState<User | null>(null);
    const [messages, setMessages] = useState<Message[]>([]);
    const [newMessage, setNewMessage] = useState('');
    const [loading, setLoading] = useState(true);
    const messagesEndRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (userId) {
            loadChatData(parseInt(userId));
        }
    }, [userId]);

    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    const loadChatData = async (otherUserId: number) => {
        try {
            const [userRes, messagesRes] = await Promise.all([
                usersService.getOne(otherUserId),
                api.get(`/chat/${otherUserId}`),
            ]);
            setOtherUser(userRes.data);
            setMessages(messagesRes.data);
        } catch (error) {
            console.error('Erreur chargement chat:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleSendMessage = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!newMessage.trim() || !userId) return;

        try {
            const response = await api.post('/chat/send', {
                receiverId: parseInt(userId),
                content: newMessage,
            });
            setMessages([...messages, response.data]);
            setNewMessage('');
        } catch (error) {
            console.error('Erreur envoi message:', error);
        }
    };

    if (loading) {
        return <div className="loading">Chargement...</div>;
    }

    if (!otherUser) {
        return (
            <div className="error-page">
                <h1>😕 Utilisateur non trouvé</h1>
                <Link to="/musiciens">Retour aux musiciens</Link>
            </div>
        );
    }

    const otherUserImage = otherUser.image
        ? `http://localhost:8000/uploads/users/${otherUser.image}`
        : '/default-avatar.png';

    return (
        <div className="chat-page">
            <div className="chat-container">
                <div className="chat-sidebar">
                    <div className="chat-user-info">
                        <h3>Conversation avec {otherUser.firstName} {otherUser.lastName}</h3>
                        <img src={otherUserImage} alt={otherUser.firstName} className="chat-avatar" />
                        <p>{otherUser.firstName} {otherUser.lastName}</p>
                        <span className="chat-user-instrument">{otherUser.mainInstrument || 'Musicien'}</span>
                        <span className="chat-user-style">{otherUser.mainStyle || ''}</span>
                        <Link to={`/musicien/${otherUser.id}`} className="btn-view-profile">
                            👤 Voir profil
                        </Link>
                    </div>
                    <Link to="/" className="btn-back">← Retour</Link>
                </div>

                <div className="chat-main">
                    <div className="chat-header">
                        <h2>Messages</h2>
                    </div>

                    <div className="chat-messages">
                        {messages.length === 0 ? (
                            <div className="no-messages">
                                <p>💬 Aucun message. Commencez la conversation !</p>
                            </div>
                        ) : (
                            messages.map((message) => (
                                <div
                                    key={message.id}
                                    className={`message ${message.sender.id === currentUser?.id ? 'sent' : 'received'}`}
                                >
                                    <div className="message-content">{message.content}</div>
                                    <div className="message-time">
                                        {new Date(message.createdAt).toLocaleTimeString('fr-FR', {
                                            hour: '2-digit',
                                            minute: '2-digit',
                                        })}
                                    </div>
                                </div>
                            ))
                        )}
                        <div ref={messagesEndRef} />
                    </div>

                    <form onSubmit={handleSendMessage} className="chat-input-form">
                        <input
                            type="text"
                            value={newMessage}
                            onChange={(e) => setNewMessage(e.target.value)}
                            placeholder="Écrivez votre message..."
                            className="chat-input"
                        />
                        <button type="submit" className="btn-send">
                            ✈️ Envoyer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    );
}

export default Chat;