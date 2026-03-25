<?php

namespace App\Controller\Admin;

use App\Entity\UserStyle;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class UserStyleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserStyle::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('user', 'Utilisateur'),
            AssociationField::new('style', 'Style'),
            BooleanField::new('isPrincipal', 'Style principal'),
        ];
    }
}