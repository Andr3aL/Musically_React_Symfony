<?php

namespace App\DataFixtures;

use App\Entity\Band;
use App\Entity\BandMember;
use App\Entity\Instrument;
use App\Entity\Message;
use App\Entity\Style;
use App\Entity\User;
use App\Entity\UserInstrument;
use App\Entity\UserStyle;
use App\Enum\Civility;
use App\Enum\Niveau;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void
    {
        $uploadDir = __DIR__ . '/../../public/uploads';
        $styles = $this->createStyles($manager, $uploadDir);
        $instruments = $this->createInstruments($manager, $uploadDir);
        $users = $this->createUsers($manager, $uploadDir, $styles, $instruments);
        $this->createBands($manager, $uploadDir, $users, $styles);
        $this->createMessages($manager, $users);
        $manager->flush();
    }

    private function dl(string $url, string $path): ?string
    {
        try {
            $dir = dirname($path);
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $c = @file_get_contents($url, false, stream_context_create(['http'=>['timeout'=>15]]));
            if ($c && strlen($c) > 100) { file_put_contents($path, $c); return basename($path); }
        } catch (\Throwable $e) {}
        return null;
    }

    private function createStyles(ObjectManager $m, string $d): array
    {
        $data = [
            'Blues'=>'Genre musical afro-americain','Reggae'=>'Musique jamaicaine rythmee',
            'Country'=>'Musique traditionnelle americaine','Funk'=>'Genre rythmique melant soul et jazz',
            'Soul'=>'Musique combinant gospel et R&B','R&B'=>'Rhythm and blues contemporain',
            'Electronique'=>'Musique electronique','Classique'=>'Musique savante occidentale',
            'Punk'=>'Genre brut et energique','Grunge'=>'Rock alternatif de Seattle',
            'Disco'=>'Musique de danse des annees 70','Ska'=>'Genre jamaicain precurseur du reggae',
            'Gospel'=>'Musique chretienne afro-americaine','Afrobeat'=>'Fusion ouest-africaine jazz funk',
            'Bossa Nova'=>'Genre bresilien samba et jazz','Hard Rock'=>'Rock agressif et amplifie',
        ];
        $seeds = ['blues-music','reggae-jamaica','country-guitar','funk-groove','soul-singer','rnb-concert','electronic-dj','classical-orchestra','punk-rock','grunge-seattle','disco-ball','ska-band','gospel-choir','afrobeat-drums','bossa-nova','hard-rock'];
        $styles = []; $i = 0;
        foreach ($data as $nom => $desc) {
            $s = new Style(); $s->setNomStyle($nom); $s->setDescription($desc);
            $fn = 'fixture_style_'.strtolower(preg_replace('/[^a-z0-9]/i','_',$nom)).'.jpg';
            $img = $this->dl('https://picsum.photos/seed/'.$seeds[$i].'/400/400', $d.'/styles/'.$fn);
            if ($img) $s->setImage($img);
            $m->persist($s); $styles[$nom] = $s; $i++;
        }
        return $styles;
    }

    private function createInstruments(ObjectManager $m, string $d): array
    {
        $data = [
            'Violon'=>'Instrument a cordes frottees','Saxophone'=>'Instrument a vent a anche simple',
            'Trompette'=>'Cuivre a pistons','Flute traversiere'=>'Instrument a vent bois',
            'Contrebasse'=>'Plus grand instrument a cordes frottees','Harpe'=>'Cordes pincees triangulaire',
            'Accordeon'=>'Instrument a soufflet','Banjo'=>'Cordes folk et country',
            'Harmonica'=>'Petit instrument a anches libres','Violoncelle'=>'Cordes frottees tenor',
            'Tuba'=>'Plus grave des cuivres','Clarinette'=>'Bois a anche simple',
            'Ukulele'=>'Petites cordes pincees hawaien','Djembe'=>'Percussion ouest-africaine',
            'Synthetiseur'=>'Instrument electronique','Mandoline'=>'Cordes pincees famille du luth',
        ];
        $seeds = ['violin','saxophone','trumpet','flute','double-bass','harp','accordion','banjo','harmonica','cello','tuba','clarinet','ukulele','djembe','synthesizer','mandolin'];
        $instr = []; $i = 0;
        foreach ($data as $nom => $desc) {
            $inst = new Instrument(); $inst->setNomInstrument($nom); $inst->setDescription($desc);
            $fn = 'fixture_instr_'.strtolower(preg_replace('/[^a-z0-9]/i','_',$nom)).'.jpg';
            $img = $this->dl('https://picsum.photos/seed/'.$seeds[$i].'/400/400', $d.'/instruments/'.$fn);
            if ($img) $inst->setImageInstrument($img);
            $m->persist($inst); $instr[$nom] = $inst; $i++;
        }
        return $instr;
    }

    private function createUsers(ObjectManager $m, string $d, array $styles, array $instruments): array
    {
        $rows = [
            ['sophie.martin@email.fr','Sophie','MARTIN','Paris','France','f','1995-03-12','15 rue de Rivoli','Violon','avance','Classique','women/1'],
            ['alex.dupont@email.fr','Alexandre','DUPONT','Lyon','France','h','1990-07-22','8 place Bellecour','Synthetiseur','intermediaire','Punk','men/1'],
            ['emilie.bernard@email.fr','Emilie','BERNARD','Marseille','France','f','1993-11-05','42 rue Paradis','Saxophone','avance','Blues','women/2'],
            ['lucas.thomas@email.fr','Lucas','THOMAS','Toulouse','France','h','1992-01-18','5 allee Jean Jaures','Djembe','avance','Funk','men/2'],
            ['camille.petit@email.fr','Camille','PETIT','Bordeaux','France','f','1997-06-30','12 cours Intendance','Harpe','avance','Classique','women/3'],
            ['julien.moreau@email.fr','Julien','MOREAU','Nantes','France','h','1988-09-14','3 quai de la Fosse','Contrebasse','intermediaire','Reggae','men/3'],
            ['maria.garcia@email.fr','Maria','GARCIA','Montpellier','France','f','1994-04-08','20 rue Foch','Flute traversiere','avance','Classique','women/4'],
            ['antoine.roux@email.fr','Antoine','ROUX','Strasbourg','France','h','1991-12-25','7 place Kleber','Trompette','avance','Blues','men/4'],
            ['lea.fournier@email.fr','Lea','FOURNIER','Lille','France','f','1996-02-17','11 rue de Bethune','Harpe','intermediaire','Electronique','women/5'],
            ['maxime.girard@email.fr','Maxime','GIRARD','Rennes','France','h','1989-08-03','6 rue Le Bastard','Synthetiseur','avance','Electronique','men/5'],
            ['chloe.bonnet@email.fr','Chloe','BONNET','Nice','France','f','1998-05-21','14 promenade des Anglais','Violoncelle','avance','Classique','women/6'],
            ['hugo.lambert@email.fr','Hugo','LAMBERT','Grenoble','France','h','1987-10-09','22 boulevard Gambetta','Harmonica','avance','Blues','men/6'],
            ['manon.fontaine@email.fr','Manon','FONTAINE','Dijon','France','f','1993-07-14','9 rue de la Liberte','Accordeon','intermediaire','Country','women/7'],
            ['theo.mercier@email.fr','Theo','MERCIER','Tours','France','h','1995-03-28','18 rue Nationale','Djembe','avance','Afrobeat','men/7'],
            ['juliette.leroy@email.fr','Juliette','LEROY','Angers','France','f','1999-01-06','4 boulevard Foch','Ukulele','debutant','Bossa Nova','women/8'],
            ['nathan.dubois@email.fr','Nathan','DUBOIS','Clermont-Ferrand','France','h','1991-11-30','2 place de Jaude','Banjo','intermediaire','Country','men/8'],
            ['ines.morel@email.fr','Ines','MOREL','Aix-en-Provence','France','f','1994-09-22','31 cours Mirabeau','Clarinette','avance','Blues','women/9'],
            ['raphael.simon@email.fr','Raphael','SIMON','Metz','France','h','1986-04-15','10 place Saint-Louis','Mandoline','intermediaire','Country','men/9'],
            ['clara.muller@email.fr','Clara','MULLER','Besancon','France','f','1997-08-11','5 Grande Rue','Tuba','debutant','Classique','women/10'],
            ['bastien.lefebvre@email.fr','Bastien','LEFEBVRE','Pau','France','h','1990-06-19','16 bd des Pyrenees','Contrebasse','avance','Blues','men/10'],
            ['carlos.martinez@email.fr','Carlos','MARTINEZ','Perpignan','France','h','1985-02-28','8 quai Vauban','Ukulele','avance','Bossa Nova','men/11'],
            ['anais.faure@email.fr','Anais','FAURE','Avignon','France','f','1996-12-03','25 rue de la Republique','Violon','intermediaire','Classique','women/11'],
            ['damien.henry@email.fr','Damien','HENRY','Rouen','France','h','1992-05-07','13 rue du Gros-Horloge','Synthetiseur','avance','Electronique','men/12'],
            ['margot.duval@email.fr','Margot','DUVAL','La Rochelle','France','f','1993-10-16','7 rue du Palais','Saxophone','intermediaire','Soul','women/12'],
            ['romain.chevalier@email.fr','Romain','CHEVALIER','Caen','France','h','1994-01-24','19 rue Saint-Pierre','Trompette','avance','Funk','men/13'],
        ];

        $users = [];
        foreach ($rows as $r) {
            $u = new User();
            $u->setEmail($r[0]); $u->setFirstName($r[1]); $u->setLastName($r[2]);
            $u->setCity($r[3]); $u->setCountry($r[4]);
            $u->setCivility($r[5]==='f' ? Civility::FEMME : Civility::HOMME);
            $u->setBirthday(new \DateTime($r[6])); $u->setAddress($r[7]);
            $u->setRoles(['ROLE_USER']);
            $u->setPassword($this->passwordHasher->hashPassword($u, 'blablabla'));

            $slug = strtolower(preg_replace('/[^a-z0-9]/i','_',$r[1].'_'.$r[2]));
            $img = $this->dl('https://randomuser.me/api/portraits/'.$r[11].'.jpg', $d.'/users/fixture_'.$slug.'.jpg');
            if ($img) $u->setImage($img);
            $m->persist($u);

            if (isset($instruments[$r[8]])) {
                $ui = new UserInstrument(); $ui->setUser($u); $ui->setInstrument($instruments[$r[8]]);
                $ui->setIsMain(true); $ui->setNiveau(Niveau::from($r[9])); $m->persist($ui);
            }
            if (isset($styles[$r[10]])) {
                $us = new UserStyle(); $us->setUser($u); $us->setStyle($styles[$r[10]]);
                $us->setIsPrincipal(true); $m->persist($us);
            }
            $users[$r[1].' '.$r[2]] = $u;
        }
        return $users;
    }

    private function createBands(ObjectManager $m, string $d, array $users, array $styles): array
    {
        $bandsData = [
            ['Les Cordes Sensibles','Duo musique de chambre violon et violoncelle.','Classique','chamber-music',[['Sophie MARTIN',true,'Violoniste'],['Chloe BONNET',false,'Violoncelliste']]],
            ['Groove Machine','Trio funk explosif.','Funk','funk-concert',[['Lucas THOMAS',true,'Percussionniste'],['Romain CHEVALIER',false,'Trompettiste'],['Julien MOREAU',false,'Contrebassiste']]],
            ['Jazz Horizon','Trio jazz contemporain.','Blues','jazz-stage',[['Emilie BERNARD',true,'Saxophoniste'],['Antoine ROUX',false,'Trompettiste'],['Bastien LEFEBVRE',false,'Contrebassiste']]],
            ['Electro Pulse','Collectif electronique.','Electronique','electronic-stage',[['Maxime GIRARD',true,'Synthetiseur'],['Damien HENRY',true,'Sound design'],['Lea FOURNIER',false,'Harpe electrique'],['Camille PETIT',false,'Harpe classique']]],
            ['Terra Nova','Ensemble world music.','Afrobeat','world-music',[['Carlos MARTINEZ',true,'Ukulele et direction'],['Hugo LAMBERT',false,'Harmonica'],['Margot DUVAL',false,'Saxophone'],['Theo MERCIER',false,'Djembe'],['Manon FONTAINE',false,'Accordeon']]],
            ['Rebel Sound','Duo punk-rock.','Punk','punk-concert',[['Alexandre DUPONT',true,'Synthe et chant'],['Nathan DUBOIS',false,'Banjo electrique']]],
        ];
        $bands = [];
        foreach ($bandsData as [$name,$desc,$style,$seed,$members]) {
            $b = new Band(); $b->setNameBand($name); $b->setDescription($desc);
            $b->setDateCreation(new \DateTime('-'.rand(30,365).' days')); $b->setNeedsSetup(false);
            if (isset($styles[$style])) $b->setStyle($styles[$style]);
            $slug = strtolower(preg_replace('/[^a-z0-9]/i','_',$name));
            $img = $this->dl('https://picsum.photos/seed/'.$seed.'/600/400', $d.'/bands/fixture_band_'.$slug.'.jpg');
            if ($img) $b->setPhotoBand($img);
            $m->persist($b);
            foreach ($members as [$uname,$admin,$mdesc]) {
                if (isset($users[$uname])) {
                    $mb = new BandMember(); $mb->setUser($users[$uname]); $mb->setBand($b);
                    $mb->setIsAdmin($admin); $mb->setJoinedAt(new \DateTime('-'.rand(1,29).' days'));
                    $mb->setDescription($mdesc); $m->persist($mb);
                }
            }
            $bands[$name] = $b;
        }
        return $bands;
    }

    private function createMessages(ObjectManager $m, array $users): void
    {
        $convs = [
            [['Sophie MARTIN','Chloe BONNET'],[[0,'Salut Chloe ! J ai trouve une superbe partition de Dvorak !',72],[1,'Carrement ! On se retrouve quand pour repeter ?',71],[0,'Samedi matin au conservatoire, salle 12.',70],[1,'Parfait ! J amene mon violoncelle.',69],[0,'Super, a samedi !',68]]],
            [['Lucas THOMAS','Romain CHEVALIER'],[[0,'Romain, j ai compose un nouveau groove hier soir !',48],[1,'Envoie un extrait ! Chaud pour bosser dessus.',47],[0,'Entre funk et afrobeat, tempo 110.',46],[1,'Genial, j adore melanger les styles.',45]]],
            [['Emilie BERNARD','Antoine ROUX'],[[0,'Tu connais le club de jazz Le Petit Duc a Aix ?',96],[1,'Oui ! Postulons avec Jazz Horizon.',95],[0,'Demo envoyee, ils rappellent la semaine prochaine.',94],[1,'Preparons 2-3 nouveaux morceaux.',93],[0,'Un standard de Coltrane et un original ?',92],[1,'Ca marche, a mardi !',91]]],
            [['Maxime GIRARD','Damien HENRY'],[[0,'Moog Sub 37 d occasion a un prix dingue !',36],[1,'Exactement ce qu il nous faut ! Combien ?',35],[0,'800 euros, quasi neuf.',34],[1,'Fonce ! On partage les frais.',33]]],
            [['Carlos MARTINEZ','Hugo LAMBERT'],[[0,'Hugo, j ai rencontre Theo, il joue du djembe comme un dieu.',120],[1,'Super ! On avait besoin d un percussionniste.',119],[0,'Afrobeat, mandingue, un peu de jazz.',118],[1,'On organise une jam session ?',117],[0,'Margot et Manon dispos vendredi soir.',116],[1,'Vendredi c est bon, j ai hate !',115]]],
            [['Juliette LEROY','Ines MOREL'],[[0,'Salut Ines ! Tu donnes des cours de clarinette ?',24],[1,'Oui, cours pour debutants a Aix.',23],[0,'Je suis a Angers... Visio possible ?',22],[1,'Bien sur ! Seance d essai gratuite.',21],[0,'Merci ! Je te montre le ukulele en echange.',20]]],
            [['Manon FONTAINE','Raphael SIMON'],[[0,'Tu joues de la mandoline ? Projet folk ?',60],[1,'Oui, 10 ans de mandoline ! C est quoi ?',59],[0,'Duo accordeon-mandoline pour bars et festivals.',58],[1,'J adore ! On essaie en visio ?',57]]],
            [['Anais FAURE','Maria GARCIA'],[[0,'Maria, flute a Montpellier ? Je suis a Avignon !',15],[1,'Oui ! Tu cherches des musiciens ?',14],[0,'Duo flute-violon pour les eglises.',13],[1,'Bonne idee ! Repertoire baroque pret.',12],[0,'Week-end prochain a Montpellier ?',11]]],
        ];
        foreach ($convs as [$between,$msgs]) {
            $u1 = $users[$between[0]] ?? null; $u2 = $users[$between[1]] ?? null;
            if (!$u1 || !$u2) continue;
            foreach ($msgs as [$from,$text,$ago]) {
                $msg = new Message();
                $msg->setSender($from===0?$u1:$u2); $msg->setReceiver($from===0?$u2:$u1);
                $msg->setContent($text); $msg->setReadStatus(true);
                $msg->setCreatedAt(new \DateTime('-'.$ago.' hours'));
                $m->persist($msg);
            }
        }
    }
}
