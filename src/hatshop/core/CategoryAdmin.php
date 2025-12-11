<?php

namespace Hatshop\Core;

/**
 * Business tier class for category administration operations.
 *
 * This class handles CRUD operations for categories
 * used by the admin interface.
 */
class CategoryAdmin
{
    /**
     * Gets all categories in a department.
     *
     * @param int $departmentId Department ID
     * @return array List of categories
     */
    public static function getDepartmentCategories(int $departmentId): array
    {
        $sql = 'SELECT * FROM catalog_get_department_categories(:department_id);';
        $params = [':department_id' => $departmentId];
        $result = DatabaseHandler::prepare($sql);
        return DatabaseHandler::getAll($result, $params) ?? [];
    }

    /**
     * Adds a new category to a department.
     *
     * @param int $departmentId Department ID
     * @param string $categoryName Category name
     * @param string $categoryDescription Category description
     */
    public static function addCategory(
        int $departmentId,
        string $categoryName,
        string $categoryDescription
    ): void {
        $sql = 'SELECT catalog_add_category(:department_id, :category_name, :category_description);';
        $params = [
            ':department_id' => $departmentId,
            ':category_name' => $categoryName,
            ':category_description' => $categoryDescription,
        ];
        $result = DatabaseHandler::prepare($sql);
        DatabaseHandler::execute($result, $params);
    }

    /**
     * Deletes a category.
     *
     * @param int $categoryId Category ID to delete
     * @return int Status code (negative if category not empty)
     */
    public static function deleteCategory(int $categoryId): int
    {
        $sql = 'SELECT catalog_delete_category(:category_id);';
        $params = [':category_id' => $categoryId];
        $result = DatabaseHandler::prepare($sql);
        return (int) DatabaseHandler::getOne($result, $params);
    }

    /**
     * Updates a category.
     *
     * @param int $categoryId Category ID
     * @param string $categoryName New category name
     * @param string $categoryDescription New category description
     */
    public static function updateCategory(
        int $categoryId,
        string $categoryName,
        string $categoryDescription
    ): void {
        $sql = 'SELECT catalog_update_category(:category_id, :category_name, :category_description);';
        $params = [
            ':category_id' => $categoryId,
            ':category_name' => $categoryName,
            ':category_description' => $categoryDescription,
        ];
        $result = DatabaseHandler::prepare($sql);
        DatabaseHandler::execute($result, $params);
    }

    /**
     * Gets all categories.
     *
     * @return array List of all categories
     */
    public static function getCategories(): array
    {
        $sql = 'SELECT * FROM catalog_get_categories();';
        $result = DatabaseHandler::prepare($sql);
        return DatabaseHandler::getAll($result) ?? [];
    }
}
