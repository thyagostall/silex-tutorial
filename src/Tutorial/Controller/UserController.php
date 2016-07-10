<?php
/**
 * Created by PhpStorm.
 * User: thyago
 * Date: 7/10/16
 * Time: 12:23 AM
 */

namespace Tutorial\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;

class UserController
{
    public function allUsers() {
        $data = array("method" => "/");
        return new JsonResponse($data, 200);
    }
}