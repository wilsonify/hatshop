<?php

namespace Hatshop\Core;

/**
 * Business tier class for reading product catalog information.
 *
 * This class handles department and category retrieval functionality.
 * For product operations, use CatalogProducts.
 * For search operations, use CatalogSearch.
 * For admin operations, use CatalogAdmin.
 */
class Catalog
{
    /**
     * Retrieve all departments.
     *
     * @return array List of departments
     */
    public static function getDepartments(): array
    {
        if (!FeatureFlags::isEnabled(FeatureFlags::FEATURE_DEPARTMENTS)) {
            return [];
        }

        $sql = 'SELECT * FROM catalog_get_departments_list();';
        $result = DatabaseHandler::prepare($sql);
        return DatabaseHandler::getAll($result) ?? [];
    }

    /**
     * Retrieve complete details for a department.
     *
     * @param int $departmentId The department ID
     * @return array|null Department details or null if not found
     */
    public static function getDepartmentDetails(int $departmentId): ?array
    {
        if (!FeatureFlags::isEnabled(FeatureFlags::FEATURE_DEPARTMENTS)) {
            return null;
        }

        $sql = 'SELECT * FROM catalog_get_department_details(:department_id);';
        $params = [':department_id' => $departmentId];
        $result = DatabaseHandler::prepare($sql);
        $row = DatabaseHandler::getRow($result, $params);
        return $row !== false ? $row : null;
    }

    /**
     * Retrieve list of categories in a department.
     *
     * @param int $departmentId The department ID
     * @return array List of categories
     */
    public static function getCategoriesInDepartment(int $departmentId): array
    {
        if (!FeatureFlags::isEnabled(FeatureFlags::FEATURE_CATEGORIES)) {
            return [];
        }

        $sql = 'SELECT * FROM catalog_get_categories_list(:department_id);';
        $params = [':department_id' => $departmentId];
        $result = DatabaseHandler::prepare($sql);
        return DatabaseHandler::getAll($result, $params) ?? [];
    }

    /**
     * Retrieve complete details for a category.
     *
     * @param int $categoryId The category ID
     * @return array|null Category details or null if not found
     */
    public static function getCategoryDetails(int $categoryId): ?array
    {
        if (!FeatureFlags::isEnabled(FeatureFlags::FEATURE_CATEGORIES)) {
            return null;
        }

        $sql = 'SELECT * FROM catalog_get_category_details(:category_id);';
        $params = [':category_id' => $categoryId];
        $result = DatabaseHandler::prepare($sql);
        $row = DatabaseHandler::getRow($result, $params);
        return $row !== false ? $row : null;
    }
}

