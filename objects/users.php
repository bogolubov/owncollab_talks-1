<?php

namespace OCA\Owncollab_Talks\Objects;

class Users
{
    public function __construct()
    {

    }


    //$groups, $users
/*    public function subscribersGenerator($json, $callback)
    {
        $data = ['groups' => [], 'users' => []];
        try {
            $data = json_decode($json, true);
        } catch ( \Exception $error) {}

        $data = array_map($callback, $data);

        $data['groups'] = array_unique($data['groups']);
        $data['users'] = array_unique($data['users']);
        return json_encode($data);
    }

    public function subscribersFromJSON()
    {
        return 'create';
    }


    public function subscribersAddUser()
    {
        return 'create';
    }


    public function subscribersGroupUser()
    {
        return 'create';
    }*/
}