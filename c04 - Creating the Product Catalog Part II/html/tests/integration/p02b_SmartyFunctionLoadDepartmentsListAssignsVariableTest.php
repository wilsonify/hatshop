<?php

// Reference Composer's autoload
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../presentation/smarty_plugins/function.load_departments_list.php';
use PHPUnit\Framework\TestCase;
use Smarty\Smarty;

class p02b_SmartyFunctionLoadDepartmentsListAssignsVariableTest  extends TestCase
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
