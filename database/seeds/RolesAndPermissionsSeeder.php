<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // create permissions
        Permission::create([
            'name' => 'permissions.index',
            'display_name' => '权限列表',
        ]);
        Permission::create([
            'name' => 'permissions.store',
            'display_name' => '新建权限',
        ]);
        Permission::create([
            'name' => 'permissions.update',
            'display_name' => '更新权限',
        ]);
        Permission::create([
            'name' => 'roles.index',
            'display_name' => '角色列表',
        ]);
        Permission::create([
            'name' => 'roles.store',
            'display_name' => '新建角色',
        ]);
        Permission::create([
            'name' => 'roles.update',
            'display_name' => '更新角色',
        ]);
        Permission::create([
            'name' => 'roles.assignPermissions',
            'display_name' => '分配权限',
        ]);
        Permission::create([
            'name' => 'users.index',
            'display_name' => '用户列表',
        ]);
        Permission::create([
            'name' => 'users.assignRoles',
            'display_name' => '分配角色',
        ]);
        Permission::create([
            'name' => 'users.assignPermissions',
            'display_name' => '分配权限',
        ]);
        Permission::create([
            'name' => 'tags.index',
            'display_name' => '标签列表',
        ]);
        Permission::create([
            'name' => 'tags.store',
            'display_name' => '新建标签',
        ]);
        Permission::create([
            'name' => 'tags.update',
            'display_name' => '	更新标签',
        ]);
        Permission::create([
            'name' => 'articles.store',
            'display_name' => '发布文章',
        ]);
        Permission::create([
            'name' => 'articles.update',
            'display_name' => '修改文章',
        ]);

        // create roles and assign created permissions
        $role = Role::create([
            'name' => 'Founder',
            'display_name' => '超超超级管理员',
        ]);
        $role->givePermissionTo(Permission::all());

        // assign role
        $user = User::find(1);
        $user->assignRole($role);
    }
}