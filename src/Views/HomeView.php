<?php

/**
 * Linna App.
 *
 * @author Sebastian Rapetti <sebastian.rapetti@alice.it>
 * @copyright (c) 2018, Sebastian Rapetti
 * @license http://opensource.org/licenses/MIT MIT License
 */
declare(strict_types=1);

namespace App\Views;

use App\Models\HomeModel;
use App\Templates\HtmlTemplate;
use Linna\Authentication\Authentication;
use Linna\Mvc\View;

/**
 * Home Page View.
 */
class HomeView extends View
{
    /**
     * Constructor.
     *
     * @param HomeModel      $model
     * @param HtmlTemplate   $htmlTemplate
     * @param Authentication $login
     */
    public function __construct(HomeModel $model, HtmlTemplate $htmlTemplate, Authentication $login)
    {
        parent::__construct($model, $htmlTemplate);

        //merge data passed from model with login information
        $this->data = array_merge($this->data, ['login' => $login->islogged(), 'userName' => $login->getLoginData()['user_name']]);
    }

    /**
     * Index.
     */
    public function index(): void
    {
        //load home html
        $this->template->loadHtml('Home');

        //set page title
        $this->template->title = 'App/Home';
    }
}
