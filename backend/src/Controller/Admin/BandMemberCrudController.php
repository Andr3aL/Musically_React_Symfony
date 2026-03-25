<?php

namespace App\Controller\Admin;

use App\Entity\BandMember;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class BandMemberCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BandMember::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('user', 'Utilisateur'),
            AssociationField::new('band', 'Groupe'),
            BooleanField::new('isAdmin', 'Administrateur'),
            DateTimeField::new('joinedAt', 'Date d\'adhésion'),
            TextareaField::new('description', 'Description'),
        ];
    }
}