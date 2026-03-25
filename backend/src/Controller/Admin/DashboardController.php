<?php

namespace App\Controller\Admin;

use App\Entity\Band;
use App\Entity\BandMember;
use App\Entity\Instrument;
use App\Entity\Message;
use App\Entity\Style;
use App\Entity\User;
use App\Entity\UserInstrument;
use App\Entity\UserStyle;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Musically Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        
        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkToCrud('Users', 'fa fa-users', User::class);
        yield MenuItem::linkToCrud('Messages', 'fa fa-envelope', Message::class);
        
        yield MenuItem::section('Musique');
        yield MenuItem::linkToCrud('Styles', 'fa fa-music', Style::class);
        yield MenuItem::linkToCrud('Instruments', 'fa fa-guitar', Instrument::class);
        
        yield MenuItem::section('Groupes');
        yield MenuItem::linkToCrud('Bands', 'fa fa-users-rectangle', Band::class);
        yield MenuItem::linkToCrud('Band Members', 'fa fa-user-plus', BandMember::class);
        
        yield MenuItem::section('Relations');
        yield MenuItem::linkToCrud('User Styles', 'fa fa-link', UserStyle::class);
        yield MenuItem::linkToCrud('User Instruments', 'fa fa-link', UserInstrument::class);
    }
}

