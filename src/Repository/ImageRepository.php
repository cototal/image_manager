<?php

namespace App\Repository;

use App\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 * @method Image[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Image::class);
    }

    public function checkFileDup(UploadedFile $file)
    {
        $nameParts = explode(".", $file->getClientOriginalName());
        $size = $file->getSize();

        return $this->createQueryBuilder("image")
            ->andWhere("image.imageName LIKE :nameStart")
            ->setParameter("nameStart", $nameParts[0] . "%")
            ->andWhere("image.size = :imageSize")
            ->setParameter("imageSize", $size)
            ->getQuery()
            ->execute();
    }

    public function checkExistingDup(Image $image)
    {
        $imageNameParts = explode("-", $image->getImageName());
        array_pop($imageNameParts);
        $imageStart = implode("-", $imageNameParts);

        return $this->createQueryBuilder("image")
            ->andWhere("image.imageName LIKE :nameStart")
            ->setParameter("nameStart", $imageStart . "%")
            ->andWhere("image.size = :imageSize")
            ->setParameter("imageSize", $image->getSize())
            ->andWhere("image.id != :currentId")
            ->setParameter("currentId", $image->getId())
            ->getQuery()
            ->execute();
    }
}
