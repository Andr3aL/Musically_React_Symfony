<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\Civility;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            EmailField::new('email'),
            TextField::new('password')->onlyOnForms(),
            TextField::new('firstName', 'Prénom'),
            TextField::new('lastName', 'Nom'),
            ChoiceField::new('civility', 'Civilité')
                ->setChoices([
                    'Homme' => Civility::HOMME,
                    'Femme' => Civility::FEMME,
                ])
                ->renderAsBadges(),
            DateField::new('birthday', 'Date de naissance'),
            TextField::new('address', 'Adresse'),
            TextField::new('city', 'Ville'),
            TextField::new('country', 'Pays'),
            Field::new('imageFile', 'Photo')
                ->setFormType(VichImageType::class)
                ->onlyOnForms(),
            ImageField::new('image', 'Photo')
                ->setBasePath('/uploads/users')
                ->hideOnForm(),
            ArrayField::new('roles'),
        ];
    }
}
