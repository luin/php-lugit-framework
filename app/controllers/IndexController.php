<?php
class IndexController extends Controller
{
    public function indexAction()
    {
        $this->setVar('title', 'cc');
        $this->render('adapt/adapt');
    }
}