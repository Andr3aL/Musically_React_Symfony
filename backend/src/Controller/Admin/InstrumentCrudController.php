<?php

namespace App\Controller\Admin;

use App\Entity\Instrument;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

class InstrumentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Instrument::class;
    }

   public function configureFields(string $pageName): iterable
   {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nom_instrument', 'Nom de l\'instrument'),
            TextareaField::new('description'),
            
            Field::new('imageFile', 'Image')
                ->setFormType(VichImageType::class)
                ->onlyOnForms(),
            
            ImageField::new('image_instrument', 'Image')
                ->setBasePath('/uploads/instruments')
                ->hideOnForm(),
        ];
    }
}
