<?php
/**
 * Created by PhpStorm.
 * User: thyago
 * Date: 7/10/16
 * Time: 12:23 AM
 */

namespace Tutorial\Controller;


use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController
{
    public function allUsers(Application $app) {
        $em = $app['orm.em'];
        $userRepository = $em->getRepository('Tutorial\Entity\User');
        $users = $userRepository->findAll();

        $data = [];
        foreach ($users as $user) {
            array_push($data, $user->toArray());
        }

        return new JsonResponse($data, 200);
    }
}