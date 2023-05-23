<?php

namespace App\Controller;

use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FilesController extends AbstractController
{
    #[Route('/files', name: 'app_files')]
    public function index(Request $request): Response
    {
        if($request->isMethod('post')){

            $uploadedFile= $request->files->get('file');
            $destination=$this->getParameter('kernel.project_dir').'/public/uploads';

            //unique name
            //Urlizer remplace les espaces par des traits d'union
            $originalFilename=pathinfo($uploadedFile->getClientOriginalName(),PATHINFO_FILENAME);
            $newFilename= Urlizer::urlize( $originalFilename).'_'.uniqid('', true).'-'.$uploadedFile->guessExtension();

            //upload
            $uploadedFile->move($destination, $newFilename);
        }

        return $this->render('files/index.html.twig');
    }
}
