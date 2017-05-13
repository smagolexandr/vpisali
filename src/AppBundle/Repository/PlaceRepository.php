<?php

namespace AppBundle\Repository;

/**
 * PlaceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PlaceRepository extends \Doctrine\ORM\EntityRepository
{
    public function selectPlacesByCity($city){
        return $this->createQueryBuilder('p')
                ->addSelect('p')
                ->join('p.city', 'c')
                ->addSelect('c')
                ->where('c.name = :city')
                ->setParameter('city', $city)
                ->getQuery()
                ->getResult();
    }
}
