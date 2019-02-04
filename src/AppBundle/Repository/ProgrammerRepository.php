<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Programmer;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class ProgrammerRepository extends EntityRepository
{
    public function findAllQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('p');
    }

    /**
     * @param User $user
     * @return Programmer[]
     */
    public function findAllForUser(User $user)
    {
        return $this->findBy(array('user' => $user));
    }

    /**
     * @param $nickname
     * @return Programmer
     */
    public function findOneByNickname($nickname)
    {
        return $this->findOneBy(array('nickname' => $nickname));
    }
}
