<?php

namespace App\Command;

use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImageCleanupCommand extends Command
{
    protected static $defaultName = 'image:cleanup';
    protected static $defaultDescription = 'Try to clean up duplicate images';
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $all = $this->em->getRepository(Image::class)->findAll();
        $count = 0;
        /** @var Image $image */
        foreach ($all as $image) {
            $matches = $this->em->getRepository(Image::class)->checkExistingDup($image);
            foreach ($matches as $match) {
                $this->em->remove($match);
                ++$count;
            }
            $this->em->flush();
        }
        $io->success("Cleaned up $count images");

        return 0;
    }
}
