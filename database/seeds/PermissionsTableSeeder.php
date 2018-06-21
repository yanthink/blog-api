<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permissions')->insert([
            [
                'name' => 'permission.index',
                'display_name' => '权限列表',
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),
            ],
            [
                'name' => 'permission.store',
                'display_name' => '添加权限',
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),
            ],
            [
                'name' => 'permission.update',
                'display_name' => '更新权限',
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),],
            [
                'name' => 'role.index',
                'display_name' => '角色列表',
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),],
            [
                'name' => 'role.store',
                'display_name' => '添加角色',
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),],
            [
                'name' => 'role.update',
                'display_name' => '更新角色',
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),],
            [
                'name' => 'role.assignPermissions',
                'display_name' => '分配权限',
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),],
            [
                'name' => 'user.index',
                'display_name' => '用户列表',
                'created_at' => Carbon\Carbon::now(),

                'updated_at' => Carbon\Carbon::now(),],
            [
                'name' => 'user.store',
                'display_name' => '添加用户',
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),],
            [
                'name' => 'user.update',
                'display_name' => '更新用户',
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),],
            [
                'name' => 'user.assignRoles',
                'display_name' => '分配角色',
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),
            ],
            [
                'name' => 'article.index',
                'display_name' => '文章列表',
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),
            ],
            [
                'name' => 'article.store',
                'display_name' => '发布文章',
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),],
            [
                'name' => 'article.update',
                'display_name' => '修改文章',
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),
            ],
            [
                'name' => 'article.show',
                'display_name' => '查看文章',
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),
            ],
            [
                'name' => 'article.destroy',
                'display_name' => '删除文章',
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),
            ],
            [
                'name' => 'attachment.upload',
                'display_name' => '上传附件',
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),
            ],
        ]);

        Role::find(1)->attachPermissions(Permission::pluck('id'));
        User::find(1)->attachRoles(Role::pluck('id'));
    }
}
