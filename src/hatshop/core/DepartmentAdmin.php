<?php

namespace Hatshop\Core;

/**
 * Business tier class for department administration operations.
 *
 * This class handles CRUD operations for departments
 * used by the admin interface.
 */
class DepartmentAdmin
{
    /**
     * Retrieves all departments with their descriptions.
     *
     * @return array List of departments with descriptions
     */
    public static function getDepartmentsWithDescriptions(): array
    {
        $sql = 'SELECT * FROM catalog_get_departments();';
        $result = DatabaseHandler::prepare($sql);
        return DatabaseHandler::getAll($result) ?? [];
    }

    /**
     * Updates department details.
     *
     * @param int $departmentId Department ID
     * @param string $departmentName New department name
     * @param string $departmentDescription New department description
     */
    public static function updateDepartment(
        int $departmentId,
        string $departmentName,
        string $departmentDescription
    ): void {
        $sql = 'SELECT catalog_update_department(:department_id, :department_name,
                                                 :department_description);';
        $params = [
            ':department_id' => $departmentId,
            ':department_name' => $departmentName,
            ':department_description' => $departmentDescription,
        ];
        $result = DatabaseHandler::prepare($sql);
        DatabaseHandler::execute($result, $params);
    }

    /**
     * Deletes a department.
     *
     * @param int $departmentId Department ID to delete
     * @return int Status code (negative if department not empty)
     */
    public static function deleteDepartment(int $departmentId): int
    {
        $sql = 'SELECT catalog_delete_department(:department_id);';
        $params = [':department_id' => $departmentId];
        $result = DatabaseHandler::prepare($sql);
        return (int) DatabaseHandler::getOne($result, $params);
    }

    /**
     * Adds a new department.
     *
     * @param string $departmentName Department name
     * @param string $departmentDescription Department description
     */
    public static function addDepartment(string $departmentName, string $departmentDescription): void
    {
        $sql = 'SELECT catalog_add_department(:department_name, :department_description);';
        $params = [
            ':department_name' => $departmentName,
            ':department_description' => $departmentDescription,
        ];
        $result = DatabaseHandler::prepare($sql);
        DatabaseHandler::execute($result, $params);
    }
}
