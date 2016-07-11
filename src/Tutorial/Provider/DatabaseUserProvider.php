<?php

namespace Tutorial\Provider;

use Doctrine\ORM\EntityRepository;
use Silex\Application;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Tutorial\Entity\User;

class DatabaseUserProvider implements UserProviderInterface
{
    /**
     * @var Application
     */
    private $app;

    public function __construct($app) {
        $this->app = $app;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        $em = $this->app['orm.em'];
        $userRepository = $em->getRepository('Tutorial\Entity\User');
        $users = $userRepository->findAll();
        $user = $users[0];

        return $user;
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws \Symfony\Component\Security\Core\Exception\UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        return new User();
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class instanceof User;
    }
}