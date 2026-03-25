<?php

namespace App\Controller\Admin;

use App\Entity\Style;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;    

class StyleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Style::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nom_style', 'Nom du style'),
            TextareaField::new('description'),
            
            // Option 1: Utiliser VichImageType directement
            Field::new('imageFile', 'Image')
                ->setFormType(VichImageType::class)
                ->onlyOnForms(),
            
            // Pour afficher l'image dans la liste
            ImageField::new('image', 'Image')
                ->setBasePath('/uploads/styles')
                ->hideOnForm(),
        ];
    }
}
