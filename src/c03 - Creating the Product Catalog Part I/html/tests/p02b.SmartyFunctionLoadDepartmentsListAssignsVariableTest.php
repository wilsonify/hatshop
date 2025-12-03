<?php

// Reference Composer's autoload
require_once '/var/www/html/vendor/autoload.php'; // NOSONAR - Legacy PHP application without PSR-4 autoloading
require_once __DIR__ . '/../presentation/smarty_plugins/02.function.load_departments_list.php'; // NOSONAR
use PHPUnit\Framework\TestCase;
use Smarty\Smarty;

class SmartyFunctionLoadDepartmentsListAssignsVariableTest  extends TestCase
{
    public function testSmartyFunctionLoadDepartmentsListAssignsVariable()
    {
        $smarty = $this->createMock(Smarty::class);
        $smarty->expects($this->once())
               ->method('assign')
               ->with(
                   $this->equalTo('departments_list'),
                   $this->isInstanceOf(DepartmentsList::class)
               );

        $params = ['assign' => 'departments_list'];
        smarty_function_load_departments_list($params, $smarty);
    }
}
