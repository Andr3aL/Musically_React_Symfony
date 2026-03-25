<?php

namespace App\Controller\Admin;

use App\Entity\UserInstrument;
use App\Enum\Niveau;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

class UserInstrumentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserInstrument::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('user', 'Utilisateur'),
            AssociationField::new('instrument', 'Instrument'),
            BooleanField::new('isMain', 'Instrument principal'),
            ChoiceField::new('niveau', 'Niveau')
                ->setChoices([
                    'Débutant' => Niveau::DEBUTANT,
                    'Intermédiaire' => Niveau::INTERMEDIAIRE,
                    'Avancé' => Niveau::AVANCE,
                ]),
        ];
    }
}