<?php

namespace Presentation\Controllers;

use Presentation\MVC\ActionResult;
use Presentation\MVC\Controller;

class Home extends Controller
{

    public function __construct(
        private \Application\TitleQuery $titleQuery
    ) {
    }

    public function GET_Index(): ActionResult
    {
        return $this->view('home', [
            'title' => $this->titleQuery->execute(),
        ]);
    }
}
