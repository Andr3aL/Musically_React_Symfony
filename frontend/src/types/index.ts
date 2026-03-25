// Types pour les entités de l'API
export interface User {
    id: number;
    email: string;
    firstName: string;
    lastName: string;
    image?: string;
    city?: string;
    address?: string;
    country?: string;
    birthday?: string;
    civility?: string;
    roles: string[];
    mainInstrument?: string;
    mainStyle?: string;
    userStyles?: UserStyle[];
    userInstruments?: UserInstrument[];
}

export interface Band {
    id: number;
    nameBand: string;
    dateCreation: string;
    photoBand?: string;
    membersCount?: number;
    members?: BandMember[];
    needsSetup?: boolean;
    description?: string;
    style?: Style;
}

export interface Style {
    id: number;
    nom_style: string;
    description?: string;
    image?: string;
}

export interface Instrument {
    id: number;
    nom_instrument: string;
    description?: string;
    image_instrument?: string;
}

export interface Message {
    id: number;
    content: string;
    readStatus: boolean;
    createdAt: string;
    sender: User;
    receiver: User;
}

export interface UserStyle {
    id: number;
    user?: User;
    style: Style;
    isPrincipal: boolean;
}

export interface UserInstrument {
    id: number;
    user?: User;
    instrument: Instrument;
    isMain: boolean;
    niveau: 'DEBUTANT' | 'INTERMEDIAIRE' | 'AVANCE';
}

export interface BandMember {
    id: number;
    user: User;
    band?: Band;
    isAdmin: boolean;
    joinedAt: string;
    description?: string;
}

// Types pour les réponses API (Hydra/JSON-LD)
export interface HydraCollection<T> {
    '@context': string;
    '@id': string;
    '@type': string;
    'hydra:totalItems': number;
    'hydra:member': T[];
}

export interface LoginResponse {
    token: string;
}

export interface JWTPayload {
    username: string;
    roles: string[];
    exp: number;
    iat: number;
}

// Type pour les erreurs API
export interface ApiError {
    error: string;
    message?: string;
}