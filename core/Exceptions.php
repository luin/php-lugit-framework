<?php
class HttpException extends RuntimeException { }


class BadRequestException extends HttpException {

    public function __construct($message = null, $code = 400)
    {
        if (empty($message)) {
            $message = 'Bad Request';
        }
        parent::__construct($message, $code);
    }


}


class UnauthorizedException extends HttpException {

    public function __construct($message = null, $code = 401)
    {
        if (empty($message)) {
            $message = 'Unauthorized';
        }
        parent::__construct($message, $code);
    }


}


class ForbiddenException extends HttpException {

    public function __construct($message = null, $code = 403)
    {
        if (empty($message)) {
            $message = 'Forbidden';
        }
        parent::__construct($message, $code);
    }


}


class NotFoundException extends HttpException {

    public function __construct($message = null, $code = 404)
    {
        if (empty($message)) {
            $message = 'Not Found';
        }
        parent::__construct($message, $code);
    }


}


class MethodNotAllowedException extends HttpException {

    public function __construct($message = null, $code = 405)
    {
        if (empty($message)) {
            $message = 'Method Not Allowed';
        }
        parent::__construct($message, $code);
    }


}


class InternalErrorException extends HttpException {

    public function __construct($message = null, $code = 500)
    {
        if (empty($message)) {
            $message = 'Internal Server Error';
        }
        parent::__construct($message, $code);
    }


}

class LugitException extends RuntimeException {

    protected $_attributes = array();


    protected $_messageTemplate = '';
    public function __construct($message, $code = 500)
    {
        if (is_array($message)) {
            $this->_attributes = $message;
            $message = vsprintf($this->_messageTemplate, $message);
        }
        parent::__construct($message, $code);
    }


    public function getAttributes()
    {
        return $this->_attributes;
    }


}


class MissingControllerException extends LugitException {
    protected $_messageTemplate = 'Controller class %s could not be found.';

    public function __construct($message, $code = 404)
    {
        parent::__construct($message, $code);
    }


}


class ApiException extends LugitException {
    protected $_messageTemplate = 'Curl error: %s.';

    public function __construct($message, $code = 500)
    {
        parent::__construct($message, $code);
    }


}

class TemplateException extends LugitException {
    protected $_messageTemplate = 'Block %s is not closed';

    public function __construct($message, $code = 500)
    {
        parent::__construct($message, $code);
    }


}