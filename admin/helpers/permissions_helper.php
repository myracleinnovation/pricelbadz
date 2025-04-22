<?php
/**
 * Permissions Helper Functions
 *
 * This file contains helper functions for checking and managing user permissions.
 */

/**
 * Check if the current user has permission to perform an action on a module
 *
 * @param string $module The module name (e.g., 'analytics', 'customers_order')
 * @param string $action The action to check (e.g., 'create', 'read', 'update', 'delete')
 * @return bool True if the user has permission, false otherwise
 */
function hasPermission($module, $action)
{
    // Admin users have full access to everything
    if (isset($_SESSION['access_type']) && $_SESSION['access_type'] === 'Admin') {
        return true;
    }

    // If user has no permissions set, deny access
    if (!isset($_SESSION['permissions']) || empty($_SESSION['permissions'])) {
        return false;
    }

    // Check if the user has the specific permission
    $permissions = json_decode($_SESSION['permissions'], true);

    if (isset($permissions[$module][$action]) && $permissions[$module][$action] == 1) {
        return true;
    }

    return false;
}

/**
 * Load user permissions from the database
 *
 * @param object $conn Database connection
 * @param int $user_id User ID
 * @return bool True if permissions were loaded successfully, false otherwise
 */
function loadUserPermissions($conn, $user_id)
{
    $query = 'SELECT permissions FROM tusers WHERE id = ?';
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $_SESSION['permissions'] = $row['permissions'];
        return true;
    }

    return false;
}

/**
 * Check if the current user has access to a specific page
 *
 * @param string $page The page name (e.g., 'analytics.php', 'customers_order.php')
 * @return bool True if the user has access, false otherwise
 */
function hasPageAccess($page)
{
    // Admin users have access to all pages
    if (isset($_SESSION['access_type']) && $_SESSION['access_type'] === 'Admin') {
        return true;
    }

    // Map pages to modules
    $pageToModule = [
        'analytics.php' => 'analytics',
        'customers_order.php' => 'customers_order',
        'delivery_rider.php' => 'delivery_rider',
        'user_account.php' => 'user_account',
        'profile.php' => 'profile',
        'account_settings.php' => 'account_settings',
    ];

    // If the page is not in our map, deny access
    if (!isset($pageToModule[$page])) {
        return false;
    }

    // Check if the user has at least read permission for the module
    return hasPermission($pageToModule[$page], 'read');
}

/**
 * Redirect user based on their role and permissions
 *
 * @param string $currentPage The current page name
 * @return void
 */
function redirectBasedOnPermissions($currentPage)
{
    // If user is not logged in, redirect to login page
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    // Admin users can access all pages
    if (isset($_SESSION['access_type']) && $_SESSION['access_type'] === 'Admin') {
        return;
    }

    // Check if user has access to the current page
    if (!hasPageAccess($currentPage)) {
        // Redirect based on user role
        if (isset($_SESSION['access_type'])) {
            switch ($_SESSION['access_type']) {
                case 'Rider':
                    header('Location: analytics.php');
                    break;
                case 'Customer':
                    header('Location: home.php');
                    break;
                default:
                    header('Location: analytics.php');
            }
        } else {
            header('Location: analytics.php');
        }
        exit();
    }
}

/**
 * Get list of accessible modules for the current user
 *
 * @return array List of modules the user can access
 */
function getAccessibleModules()
{
    $accessibleModules = [];

    // Admin users have access to all modules
    if (isset($_SESSION['access_type']) && $_SESSION['access_type'] === 'Admin') {
        return [
            'analytics' => 'Analytics',
            'customers_order' => 'Customers Order',
            'delivery_rider' => 'Delivery Riders',
            'user_account' => 'User Accounts',
            'account_settings' => 'Account Settings',
        ];
    }

    // If user has no permissions set, return empty array
    if (!isset($_SESSION['permissions']) || empty($_SESSION['permissions'])) {
        return $accessibleModules;
    }

    // Check which modules the user has at least read permission for
    $permissions = json_decode($_SESSION['permissions'], true);
    $allModules = [
        'analytics' => 'Analytics',
        'customers_order' => 'Customers Order',
        'delivery_rider' => 'Delivery Riders',
        'user_account' => 'User Accounts',
        'account_settings' => 'Account Settings',
    ];

    foreach ($allModules as $module => $name) {
        if (isset($permissions[$module]['read']) && $permissions[$module]['read'] == 1) {
            $accessibleModules[$module] = $name;
        }
    }

    return $accessibleModules;
}
?>
