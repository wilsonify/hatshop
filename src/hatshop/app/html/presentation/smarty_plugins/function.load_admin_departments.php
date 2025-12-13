<?php

use Hatshop\Core\DepartmentAdmin;

/**
 * Smarty plugin function for admin departments management.
 */
function smarty_function_load_admin_departments($params, $smarty)
{
    $admin_departments = new AdminDepartments();
    $admin_departments->init();
    $smarty->assign($params['assign'], $admin_departments);
}

/**
 * Class that supports departments admin functionality.
 */
class AdminDepartments
{
    public $mDepartmentsCount = 0;
    public $mDepartments = [];
    public $mErrorMessage = '';
    public $mEditItem;
    public $mAdminDepartmentsTarget = 'admin.php?Page=Departments';

    private $mAction = '';
    private $mActionedDepartmentId;

    public function __construct()
    {
        foreach ($_POST as $key => $value) {
            if (substr($key, 0, 6) === 'submit') {
                $lastUnderscore = strrpos($key, '_');
                $this->mAction = substr($key, strlen('submit_'),
                                        $lastUnderscore - strlen('submit_'));
                $this->mActionedDepartmentId = (int) substr($key, $lastUnderscore + 1);
                break;
            }
        }
    }

    public function init()
    {
        // Adding a new department
        if ($this->mAction === 'add_dep') {
            $departmentName = $_POST['department_name'] ?? '';
            $departmentDescription = $_POST['department_description'] ?? '';

            if (empty($departmentName)) {
                $this->mErrorMessage = 'Department name required';
            }

            if (empty($this->mErrorMessage)) {
                DepartmentAdmin::addDepartment($departmentName, $departmentDescription);
            }
        }

        // Editing an existing department
        if ($this->mAction === 'edit_dep') {
            $this->mEditItem = $this->mActionedDepartmentId;
        }

        // Updating a department
        if ($this->mAction === 'update_dep') {
            $departmentName = $_POST['name'] ?? '';
            $departmentDescription = $_POST['description'] ?? '';

            if (empty($departmentName)) {
                $this->mErrorMessage = 'Department name required';
            }

            if (empty($this->mErrorMessage)) {
                DepartmentAdmin::updateDepartment($this->mActionedDepartmentId,
                                          $departmentName, $departmentDescription);
            }
        }

        // Deleting a department
        if ($this->mAction === 'delete_dep') {
            $status = DepartmentAdmin::deleteDepartment($this->mActionedDepartmentId);

            if ($status < 0) {
                $this->mErrorMessage = 'Department not empty';
            }
        }

        // Editing department's categories
        if ($this->mAction === 'edit_categ') {
            header('Location: admin.php?Page=Categories&DepartmentID=' .
                   $this->mActionedDepartmentId);
            exit;
        }

        // Load the list of departments
        $this->mDepartments = DepartmentAdmin::getDepartmentsWithDescriptions();
        $this->mDepartmentsCount = count($this->mDepartments);
    }
}
