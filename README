# 开始 #
Lugit提供简单优雅的方法帮助你实现PHP开发，需要PHP5.0以上的版本支持。

# URL映射 #
Lugit使用PATHINFO模式，如：

http://localhost/index.php/controllername/actionname/parameter0/parameter1/parameter2/

建议使用rewrite将上述URL写成

http://localhost/controllername/actionname/parameter0/parameter1/parameter2/

对于Apache服务器可以在app文件夹下建立.htaccess文件，内容如下：

    RewriteEngine on
    RewriteCond $1 !^(index\.php|favicon\.ico|robots\.txt)
    RewriteRule ^(.*)$ index.php/$1 [L]

更多内容请参见Apache服务器文档。

## Controller、Action与Parameter ##
Lugit将URL映射为Controller、Action和Parameter。

如URL: http://localhost/people/tom/book/comment/

对应的Controller为people，Action为tom，Parameter[0]为book，Parameter[1]为comment

如果缺少Action则默认为index, 缺少Controller同理

如URL: http://localhost/people/

对应的Controller为people，Action为index

如URL: http://localhost/

对应的Controller为index，Action为index

# 第一个示例: Tom的书评 #
要处理URL: 

http://localhost/people/tom/book/comment/

需要在app目录下的controllers文件夹下建立PeopleController.php文件，内容如下：

    <?php
    class PeopleController extends Controller
    {
        public function tomAction()
        {
            //$this->parameters[0] == 'book'
            $this->setVar('p0', $this->parameters[0]);
            //或 $this->vars->p0 =$this->parameters[0];

            //$this->parameters[1] == 'comment'
            $this->setVar('p1', $this->parameters[1]);
        }
    }

在views下建立people文件夹，在里面建立tom.phtml文件，内容如下：

    <html>
        <head><title>Hello tom!</title></head>
        <body>
            <p>p0 is: <?php echo $this->vars->p0; ?></p>
            <p>p1 is: <?php echo $this->vars->p1; ?></p>
        </body>
    </html>

## 问题: 其他人的书评 ##
设想网站的用户会有很多，所以会有很多如下的网址：

http://localhost/people/tom/book/comment/

http://localhost/people/luin/book/comment/

http://localhost/people/chen/book/comment/

...

我们可以在PeopleController.php里塞满以人名命名的方法，但在Lugit里有更好的解决方案：

    <?php
    class PeopleController extends Controller
    {
        public function adaptAction($people)
        {
            $this->setVar('people', $people);

            //$this->parameters[0] == 'book'
            $this->setVar('p0', $this->parameters[0]);

            //$this->parameters[1] == 'comment'
            $this->setVar('p1', $this->parameters[1]);
        }
    }

对应地，在views/people文件夹下建立adapt.phtml，内容为：

    <html>
        <head><title>Hello <?php echo $this->vars->people; ?>!</title></head>
        <body>
            <p>p0 is: <?php echo $this->vars->p0; ?></p>
            <p>p1 is: <?php echo $this->vars->p1; ?></p>
        </body>
    </html>

## 关于adapt ##
当Controller或Action不能确定时可以使用adapt。

比如要处理URL: 

http://localhost/tom/1/

http://localhost/peter/8/

http://localhost/peter/17/

我们只要在controllers文件夹下建立AdaptController.php文件: 

    <?php
    class AdaptController extends Controller
    {
        public function adaptAction($num)
        {
            $this->setVar('number', $num);
            $this->setVar('people', $this->controllerName);
        }
    }

在views文件夹下建立adapt/adapt.phtml进行处理即可。

# 处理POST & GET #
在Lugit中处理POST和GET的方式是一样的，如http://localhost/sample/?people=tom：

    <?php 
    class SampleController extends Controller
    {
        public function indexAction()
        {
            $this->request->people == 'tom'; //true
        }
    }

## 过滤输入 ##
Lugit提供了优雅的方法过滤输入:

    <?php 
    class SampleController extends Controller
    {
        public function indexAction()
        {
            $var_people = $this->request->filter('trim', 'Class::staticMethod')->people;
        }
    }

相当于:

    <?php 
    class SampleController extends Controller
    {
        public function indexAction()
        {
            $var_people = $this->request->people;
            $var_people = trim($var_people);
            $var_people = Class::staticMethod($var_people);
        }
    }
