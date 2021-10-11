<?php

namespace App\Controller\Api;

use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/image")
 */
class ImageController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/multi-upload", name="api_image_multi_upload", methods={"POST"})
     */
    public function multiUpload(Request $request): Response
    {
        $images = [];
        /** @var UploadedFile $file */
        foreach ($request->files->get("image") as $file) {
            $matches = $this->em->getRepository(Image::class)->checkFileDup($file);
            if (!empty($matches)) {
                continue;
            }
            $image = (new Image)->setImageFile($file);
            $images[] = $image;
            $this->em->persist($image);
        }
        $this->em->flush();

        return $this->json($images, 201);
    }
}