<?php

namespace App\Controller\Admin;

use App\Entity\Band;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Vich\UploaderBundle\Form\Type\VichImageType;

class BandCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Band::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nameBand', 'Nom du groupe'),
            DateTimeField::new('dateCreation', 'Date de création'),
            Field::new('imageFile', 'Photo')
                ->setFormType(VichImageType::class)
                ->onlyOnForms(),
            ImageField::new('photoBand', 'Photo')
                ->setBasePath('/uploads/bands')
                ->hideOnForm(),
        ];
    }
}