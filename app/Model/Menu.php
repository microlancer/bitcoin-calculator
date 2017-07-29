<?php

namespace App\Model;

use App\Util\Session;

class Menu
{
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function getLinks()
    {
        $links = [
            [
                'route' => '',
                'label' => 'Home',
            ]
        ];
        
        $links[] = [
            'route' => 'markets',
            'label' => 'Markets',
        ];

        $links[] = [
            'route' => 'biz/list',
            'label' => 'Businesses',
        ];

        $links[] = [
            'route' => 'jobs/list',
            'label' => 'Jobs',
        ];
        
        $links[] = [
            'route' => 'forums/list',
            'label' => 'Forums',
        ];

        if (!$this->session->get('loggedIn')) {
            $links[] = [
                'route' => 'user/login',
                'label' => 'Login',
            ];

            $links[] = [
                'route' => 'user/signup',
                'label' => 'Signup',
            ];
        } else {
            $links[] = [
                'route' => 'user/account',
                'label' => 'My account',
            ];
            
            $links[] = [
                'route' => 'user/logout',
                'label' => 'Logout',
            ];
        }

//        $links[] = [
//            'route' => 'help/faq',
//            'label' => 'Help',
//        ];
        
        return $links;
    }
}
