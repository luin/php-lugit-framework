<?php
class AdaptController extends Controller
{
	public function AdaptAction()
	{
		echo $this->request->filter('xss')->oo;
	}
}