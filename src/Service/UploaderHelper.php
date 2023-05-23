<?php

namespace App\Service;

use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploaderHelper
{
  //declarer uploads dans config/services.yaml  services:bind:  $uploadsPath: '%kernel.project_dir%/public/uploads'
    public function __construct(private readonly string $uploadsPath)
    {
    }

    public function uploadFile(UploadedFile $uploadedFile):string{

    $destination = $this->uploadsPath;

    //unique name
    //Urlizer remplace les espaces par des traits d'union
    $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
    $newFilename = Urlizer::urlize($originalFilename) . '_' . uniqid('', true) . '-' . $uploadedFile->guessExtension();

    //upload
    $uploadedFile->move($destination, $newFilename);

    return $newFilename;
}
}