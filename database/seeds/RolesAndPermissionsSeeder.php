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
            'name' => 'permission.index',
            'display_name' => '权限列表',
        ]);
        Permission::create([
            'name' => 'permission.store',
            'display_name' => '添加权限',
        ]);
        Permission::create([
            'name' => 'permission.update',
            'display_name' => '更新权限',
        ]);
        Permission::create([
            'name' => 'role.index',
            'display_name' => '角色列表',
        ]);
        Permission::create([
            'name' => 'role.store',
            'display_name' => '添加角色',
        ]);
        Permission::create([
            'name' => 'role.update',
            'display_name' => '更新角色',
        ]);
        Permission::create([
            'name' => 'role.assignPermissions',
            'display_name' => '分配权限',
        ]);
        Permission::create([
            'name' => 'user.index',
            'display_name' => '用户列表',
        ]);
        Permission::create([
            'name' => 'user.store',
            'display_name' => '添加用户',
        ]);
        Permission::create([
            'name' => 'user.update',
            'display_name' => '更新用户',
        ]);
        Permission::create([
            'name' => 'user.assignRoles',
            'display_name' => '分配角色',
        ]);
        Permission::create([
            'name' => 'article.index',
            'display_name' => '文章列表',
        ]);
        Permission::create([
            'name' => 'article.store',
            'display_name' => '发布文章',
        ]);
        Permission::create([
            'name' => 'article.update',
            'display_name' => '修改文章',
        ]);
        Permission::create([
            'name' => 'article.show',
            'display_name' => '查看文章',
        ]);
        Permission::create([
            'name' => 'article.destroy',
            'display_name' => '删除文章',
        ]);
        Permission::create([
            'name' => 'attachment.upload',
            'display_name' => '上传附件',
        ]);

        // create roles and assign created permissions
        $role = Role::create([
            'name' => 'Founder',
            'display_name' => '创始人',
        ]);
        $role->givePermissionTo(Permission::all());

        // assign role
        $user = User::find(1);
        $user->assignRole($role);
    }
}