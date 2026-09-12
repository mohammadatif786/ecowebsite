<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Kreait\Laravel\Firebase\Facades\Firebase;

class Policy
{
    use HandlesAuthorization;

    protected $db;

    protected $user;

    protected $userRole;

    protected $permissions;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->db = Firebase::firestore()->database();
        $this->user = $this->db->collection('adminUsers')->document(session('loggedUser.uid'));
        $this->userRole = $this->db->collection('roles')->document($this->user->snapshot()->data()['role_id']);
        $this->permissions = $this->userRole->snapshot()->data()['permissions']?? [];
    }

    public function read()
    {
        return $this->userHasPermission($this->module, 'read');
    }

    public function write()
    {
        return $this->userHasPermission($this->module, 'write');
    }

    public function create()
    {
        return $this->userHasPermission($this->module, 'create');
    }

    public function delete()
    {
        return $this->userHasPermission($this->module, 'delete');
    }

    public function any(array $actions)
    {
        return $this->userHasAnyPermission($this->module, $actions);
    }

    public function all(array $actions)
    {
        return $this->userHasAllPermissions($this->module, $actions);
    }

    public function userHasPermission($module, $action)
    {
        if (isset($this->permissions[$module]))
            return in_array($action, $this->permissions[$module]);
        else
            return false;
    }

    public function userHasAnyPermission($module, array $actions)
    {
        if (isset($this->permissions[$module])) {
            $hasPermission = false;
            foreach ($actions as $action) {
                if (in_array($action, $this->permissions[$module]))
                    $hasPermission = true;
            }

            return $hasPermission;
        }

        return false;
    }

    public function userHasAllPermissions($module, array $actions)
    {
        if (isset($this->permissions[$module])) {
            $hasPermission = true;
            foreach ($actions as $action) {
                if (!in_array($action, $this->permissions[$module]))
                    $hasPermission = false;
            }

            return $hasPermission;
        }

        return false;
    }
}
