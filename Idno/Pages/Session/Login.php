<?php

    /**
     * Defines built-in log in functionality
     */

    namespace Idno\Pages\Session {

        /**
         * Default class to serve the homepage
         */
        class Login extends \Idno\Common\Page
        {

            function getContent()
            {

                // If we're somehow here but logged in, move to the front page
                if (\Idno\Core\site()->session()->isLoggedOn()) {
                    $this->forward();
                }

                $fwd      = $this->getInput('fwd'); // Forward to a new page?
                if ($fwd == \Idno\Core\site()->config()->url . 'session/login') {
                    $fwd = '';
                }
                $t        = \Idno\Core\site()->template();
                $t->body  = $t->__(['fwd' => $fwd])->draw('session/login');
                $t->title = 'Sign in';
                $t->drawPage();
            }

            function postContent()
            {
                $fwd = $this->getInput('fwd'); // Forward to a new page?
                if (empty($fwd)) {
                    $fwd = \Idno\Core\site()->config()->url;
                }

                if ($user = \Idno\Entities\User::getByHandle($this->getInput('email'))) {
                } else if ($user = \Idno\Entities\User::getByEmail($this->getInput('email'))) {
                } else {
                    \Idno\Core\site()->triggerEvent('login/failure/nouser', ['method' => 'password', 'credentials' => ['email' => $this->getInput('email')]]);
                    $this->setResponse(401);
                    //$this->gatekeeper();
                }

                if ($user instanceof \Idno\Entities\User) {
                    if ($user->checkPassword($this->getInput('password'))) {
                        \Idno\Core\site()->triggerEvent('login/success', ['user' => $user]); // Trigger an event for auditing
                        \Idno\Core\site()->session()->logUserOn($user);
                        $this->forward($fwd);
                    } else {
                        \Idno\Core\site()->session()->addMessage("Oops! It looks like your password isn't correct. Please try again.");
                        \Idno\Core\site()->triggerEvent('login/failure', ['user' => $user]);
                        $this->forward($_SERVER['HTTP_REFERER']);
                    }
                } else {
                    \Idno\Core\site()->session()->addMessage("Oops! We couldn't find your username or email address. Please check you typed it correctly and try again.");
                }
            }

        }

    }