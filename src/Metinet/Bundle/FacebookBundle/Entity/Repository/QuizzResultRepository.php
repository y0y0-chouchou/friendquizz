<?php

namespace Metinet\Bundle\FacebookBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * QuizzResultRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class QuizzResultRepository extends EntityRepository
{

	/**
     * Fonction qui regarde si l'user a déjà joué au quizz ou non
     * @param QUIZZ $quizz Le quizz que l'on veut jouer
     * @param USER $user    L'user qui veut jouer au quizz
     * @return BOOL TRUE or FALSE
     */
    public function hasPlayedThisQuizz($quizz, $user){
	    $paramArray = array("quizz" => $quizz,
	                "user"  => $user);
	    $result = $this->_em->createQuery("SELECT COUNT(quizzResult.id)
	                    FROM MetinetFacebookBundle:QuizzResult quizzResult
	                    WHERE quizzResult.quizz = :quizz
	                    AND quizzResult.user = :user")
	        ->setParameters($paramArray)
	        ->getSingleScalarResult();
	    if($result > 0){
	        return TRUE;
	    }
	    return FALSE;
    }

    /**
     * Fonction pour l'update de quizz result
     * @param QUIZZ $quizz
     * @param USER $user
     * @return QUIZZRESULT $result
     */
    public function updateResultQuizz($quizz, $user){

	    $paramArray = array("quizz" => $quizz,
	                		"user"  => $user);
	    $result = $this->_em->createQuery("SELECT quizzResult
	                    FROM MetinetFacebookBundle:QuizzResult quizzResult
	                    WHERE quizzResult.quizz = :quizz
	                    AND quizzResult.user = :user")
	        ->setParameters($paramArray)
	        ->setMaxResults(1);
	    
	    try {
		    $quizzRes = $result->getSingleResult();
		} catch (\Doctrine\Orm\NoResultException $e) {
		    $quizzRes = null;
		}
		return $quizzRes;
	}

}
